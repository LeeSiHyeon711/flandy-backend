<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\ToolInvocation;
use App\Models\Task;
use App\Models\ScheduleBlock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use OpenApi\Annotations as OA;

class AiController extends Controller
{
    private $aiServerUrl;

    public function __construct()
    {
        $this->aiServerUrl = config('app.ai_server_url', 'http://localhost:8001');
    }

    /**
     * @OA\Post(
     *     path="/api/ai/chat",
     *     summary="AI 채팅 메시지 전송",
     *     tags={"AI 연동"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"chat_room_id","message"},
     *             @OA\Property(property="chat_room_id", type="integer", example=1),
     *             @OA\Property(property="message", type="string", example="오늘 할 일을 추천해줘")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="AI 채팅 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'chat_room_id' => 'required|exists:chat_rooms,id',
            'message' => 'required|string',
        ]);

        $chatRoom = ChatRoom::findOrFail($validated['chat_room_id']);
        $this->authorize('view', $chatRoom);

        // 사용자 메시지 저장
        $userMessage = ChatMessage::create([
            'chat_room_id' => $validated['chat_room_id'],
            'user_id' => $request->user()->id,
            'sended_type' => 'user',
            'content' => $validated['message'],
        ]);

        try {
            // AI 서버로 메시지 전송
            $response = Http::timeout(30)->post($this->aiServerUrl . '/chat', [
                'user_id' => $request->user()->id,
                'message' => $validated['message'],
                'chat_room_id' => $validated['chat_room_id'],
                'context' => $this->getUserContext($request->user()->id),
            ]);

            if ($response->successful()) {
                $aiResponse = $response->json();

                // AI 응답 메시지 저장
                $aiMessage = ChatMessage::create([
                    'chat_room_id' => $validated['chat_room_id'],
                    'user_id' => $request->user()->id,
                    'sended_type' => 'ai',
                    'content' => $aiResponse['message'],
                    'metadata' => $aiResponse['metadata'] ?? null,
                ]);

                // 도구 호출 기록 저장
                if (isset($aiResponse['tool_invocations'])) {
                    foreach ($aiResponse['tool_invocations'] as $toolInvocation) {
                        ToolInvocation::create([
                            'message_id' => $aiMessage->id,
                            'chat_room_id' => $validated['chat_room_id'],
                            'user_id' => $request->user()->id,
                            'tool_name' => $toolInvocation['tool_name'],
                            'args' => $toolInvocation['args'],
                            'result' => $toolInvocation['result'],
                            'status' => $toolInvocation['status'] ?? 'OK',
                        ]);
                    }
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'user_message' => $userMessage,
                        'ai_message' => $aiMessage,
                        'tool_invocations' => $aiResponse['tool_invocations'] ?? []
                    ]
                ]);
            } else {
                throw new \Exception('AI server error: ' . $response->body());
            }
        } catch (\Exception $e) {
            // 에러 메시지 저장
            $errorMessage = ChatMessage::create([
                'chat_room_id' => $validated['chat_room_id'],
                'user_id' => $request->user()->id,
                'sended_type' => 'system',
                'content' => '죄송합니다. AI 서버와의 통신 중 오류가 발생했습니다.',
                'metadata' => ['error' => $e->getMessage()],
            ]);

            return response()->json([
                'success' => false,
                'message' => 'AI server communication error',
                'data' => [
                    'user_message' => $userMessage,
                    'error_message' => $errorMessage
                ]
            ], 500);
        }
    }

    /**
     * 일정 재조정 요청
     */
    public function reschedule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'reason' => 'required|string',
            'preferences' => 'nullable|array',
        ]);

        try {
            // 해당 날짜의 일정 조회
            $scheduleBlocks = ScheduleBlock::where('user_id', $request->user()->id)
                ->whereDate('starts_at', $validated['date'])
                ->with(['task'])
                ->get();

            // AI 서버로 일정 재조정 요청
            $response = Http::timeout(60)->post($this->aiServerUrl . '/reschedule', [
                'user_id' => $request->user()->id,
                'date' => $validated['date'],
                'reason' => $validated['reason'],
                'current_schedule' => $scheduleBlocks,
                'preferences' => $validated['preferences'] ?? [],
                'user_context' => $this->getUserContext($request->user()->id),
            ]);

            if ($response->successful()) {
                $rescheduleData = $response->json();

                // 새로운 일정 블록 생성
                $newScheduleBlocks = [];
                foreach ($rescheduleData['new_schedule'] as $blockData) {
                    $scheduleBlock = ScheduleBlock::create([
                        'user_id' => $request->user()->id,
                        'task_id' => $blockData['task_id'] ?? null,
                        'starts_at' => $blockData['starts_at'],
                        'ends_at' => $blockData['ends_at'],
                        'source' => 'ai',
                        'state' => 'scheduled',
                    ]);
                    $newScheduleBlocks[] = $scheduleBlock;
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'original_schedule' => $scheduleBlocks,
                        'new_schedule' => $newScheduleBlocks,
                        'reasoning' => $rescheduleData['reasoning'] ?? null,
                    ]
                ]);
            } else {
                throw new \Exception('AI server error: ' . $response->body());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Reschedule failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 워라벨 점수 분석 요청
     */
    public function analyzeWorkLifeBalance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'week_start' => 'required|date',
        ]);

        try {
            // 해당 주의 데이터 수집
            $weekStart = $validated['week_start'];
            $weekEnd = \Carbon\Carbon::parse($weekStart)->addDays(6);

            $habitLogs = \App\Models\HabitLog::where('user_id', $request->user()->id)
                ->whereBetween('logged_at', [$weekStart, $weekEnd])
                ->get();

            $scheduleBlocks = ScheduleBlock::where('user_id', $request->user()->id)
                ->whereBetween('starts_at', [$weekStart, $weekEnd])
                ->with(['task'])
                ->get();

            // AI 서버로 분석 요청
            $response = Http::timeout(60)->post($this->aiServerUrl . '/analyze-worklife', [
                'user_id' => $request->user()->id,
                'week_start' => $weekStart,
                'habit_logs' => $habitLogs,
                'schedule_blocks' => $scheduleBlocks,
                'user_preferences' => $request->user()->preferences,
            ]);

            if ($response->successful()) {
                $analysis = $response->json();

                // 분석 결과를 워라벨 점수로 저장
                $balanceScore = \App\Models\BalanceScore::updateOrCreate(
                    [
                        'user_id' => $request->user()->id,
                        'week_start' => $weekStart,
                    ],
                    [
                        'score' => $analysis['score'],
                        'metrics' => $analysis['metrics'],
                    ]
                );

                return response()->json([
                    'success' => true,
                    'data' => [
                        'balance_score' => $balanceScore,
                        'analysis' => $analysis,
                        'recommendations' => $analysis['recommendations'] ?? [],
                    ]
                ]);
            } else {
                throw new \Exception('AI server error: ' . $response->body());
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Work-life balance analysis failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 사용자 컨텍스트 정보 수집
     */
    private function getUserContext(int $userId): array
    {
        $user = \App\Models\User::find($userId);
        
        return [
            'user_preferences' => $user->preferences,
            'timezone' => $user->timezone,
            'recent_tasks' => Task::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get(),
            'recent_schedule' => ScheduleBlock::where('user_id', $userId)
                ->where('starts_at', '>=', now()->subDays(7))
                ->with(['task'])
                ->get(),
            'recent_habits' => \App\Models\HabitLog::where('user_id', $userId)
                ->orderBy('logged_at', 'desc')
                ->limit(20)
                ->get(),
        ];
    }
}
