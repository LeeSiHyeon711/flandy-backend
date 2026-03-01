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
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use OpenApi\Annotations as OA;

class AiController extends Controller
{
    use AuthorizesRequests;
    
    private $aiServerUrl;

    public function __construct()
    {
        $this->aiServerUrl = config('ai.server_url', 'http://localhost:8001');
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
     *             required={"message"},
     *             @OA\Property(property="user_id", type="integer", example=1, description="사용자 ID (선택사항, 없으면 인증된 사용자 ID 사용)"),
     *             @OA\Property(property="session_id", type="string", example="session_123", description="세션 ID (선택사항, 없으면 자동 생성)"),
     *             @OA\Property(property="message", type="string", example="오늘 할 일을 추천해줘")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="AI 채팅 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="AI 응답이 성공적으로 생성되었습니다."),
     *             @OA\Property(property="ai_response", type="string", example="오늘은 중요한 태스크 3개를 우선적으로 처리하시는 것을 추천합니다..."),
     *             @OA\Property(property="session_id", type="string", example="session_123"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     */
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|integer',
            'session_id' => 'nullable|string',
            'message' => 'required|string',
            'team_id' => 'nullable|integer',
            'sprint_id' => 'nullable|integer',
        ]);

        // 항상 인증된 사용자 ID 사용 (다른 유저 데이터 접근 방지)
        $userId = $request->user()->id;

        // session_id를 기반으로 채팅방 찾기 또는 생성
        $sessionId = $validated['session_id'] ?? 'default_' . $userId;

        $chatRoom = ChatRoom::where('user_id', $userId)
            ->where('title', 'LIKE', '%' . $sessionId . '%')
            ->first();

        if (!$chatRoom) {
            $chatRoom = ChatRoom::create([
                'user_id' => $userId,
                'title' => 'AI 채팅 - ' . $sessionId . ' - ' . now()->format('Y-m-d H:i'),
            ]);
        }

        // 사용자 메시지 저장
        $userMessage = ChatMessage::create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => $userId,
            'sended_type' => 'user',
            'content' => $validated['message'],
        ]);

        try {
            // AI 서버로 스트림 요청 전송
            $response = Http::timeout(120)->withOptions([
                'stream' => true,
                'headers' => [
                    'Accept' => 'text/event-stream',
                    'Cache-Control' => 'no-cache',
                ]
            ])->post($this->aiServerUrl . '/chat', [
                'user_id' => $userId,
                'message' => $validated['message'],
                'chat_room_id' => $chatRoom->id,
                'team_id' => $validated['team_id'] ?? null,
                'sprint_id' => $validated['sprint_id'] ?? null,
                'context' => $this->getUserContext($request->user()->id),
            ]);

            if ($response->successful()) {
                return response()->stream(function () use ($response, $chatRoom, $userMessage, $sessionId, $request) {
                    // 출력 버퍼링 완전 비활성화 (SSE 즉시 전달)
                    @ini_set('zlib.output_compression', 'Off');
                    @ini_set('output_buffering', 'Off');
                    @ini_set('implicit_flush', 1);
                    while (ob_get_level()) {
                        ob_end_flush();
                    }

                    $fullResponse = '';
                    $metadata = null;
                    $toolInvocations = [];
                    $buffer = '';

                    $body = $response->getBody();
                    while (!$body->eof()) {
                        $chunk = $body->read(1024);
                        if ($chunk === '') {
                            break;
                        }

                        // 프론트엔드로 즉시 전달
                        echo $chunk;
                        flush();

                        // 버퍼에 누적하여 완전한 SSE 이벤트 단위로 파싱
                        $buffer .= $chunk;

                        // 빈 줄(\n\n)로 구분된 완전한 이벤트 블록만 처리
                        while (($eventEnd = strpos($buffer, "\n\n")) !== false) {
                            $eventBlock = substr($buffer, 0, $eventEnd);
                            $buffer = substr($buffer, $eventEnd + 2);

                            // 이벤트 블록에서 data: 라인 추출
                            $lines = explode("\n", $eventBlock);
                            foreach ($lines as $line) {
                                $line = trim($line);
                                if (strpos($line, 'data: ') === 0 || strpos($line, 'data:') === 0) {
                                    $data = strpos($line, 'data: ') === 0 ? substr($line, 6) : substr($line, 5);
                                    if ($data === '[DONE]') {
                                        break 3;
                                    }

                                    $decoded = json_decode($data, true);
                                    if ($decoded) {
                                        if (isset($decoded['ai_response']) && !empty($decoded['ai_response'])) {
                                            $fullResponse = $decoded['ai_response'];
                                        } elseif (isset($decoded['content']) && !empty($decoded['content'])) {
                                            $fullResponse = $decoded['content'];
                                        }
                                        if (isset($decoded['metadata'])) {
                                            $metadata = $decoded['metadata'];
                                        }
                                        if (isset($decoded['tool_invocations'])) {
                                            $toolInvocations = $decoded['tool_invocations'];
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // AI 응답 메시지 저장
                    $aiMessage = ChatMessage::create([
                        'chat_room_id' => $chatRoom->id,
                        'user_id' => $request->user()->id,
                        'sended_type' => 'ai',
                        'content' => $fullResponse ?: '응답을 생성할 수 없습니다.',
                        'metadata' => $metadata,
                    ]);

                    // 도구 호출 기록 저장
                    foreach ($toolInvocations as $toolInvocation) {
                        ToolInvocation::create([
                            'message_id' => $aiMessage->id,
                            'chat_room_id' => $chatRoom->id,
                            'user_id' => $request->user()->id,
                            'tool_name' => $toolInvocation['tool_name'] ?? 'unknown',
                            'args' => $toolInvocation['args'] ?? [],
                            'result' => $toolInvocation['result'] ?? '',
                            'status' => $toolInvocation['status'] ?? 'OK',
                        ]);
                    }

                    error_log("AI 응답 저장 완료: " . mb_substr($fullResponse, 0, 100));

                }, 200, [
                    'Content-Type' => 'text/event-stream',
                    'Cache-Control' => 'no-cache',
                    'Connection' => 'keep-alive',
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Headers' => 'Cache-Control',
                ]);
            } else {
                throw new \Exception('AI server error: ' . $response->body());
            }
        } catch (\Exception $e) {
            // 에러 로깅
            error_log("AI 서버 연결 오류: " . $e->getMessage());
            
            // AI 서버 연결 실패 시 대체 응답
            $fallbackResponse = $this->generateFallbackResponse($validated['message']);
            
            return response()->stream(function () use ($fallbackResponse, $chatRoom, $userMessage, $sessionId, $request, $e) {
                // 대체 응답을 단어별로 스트림 전달
                $words = explode(' ', $fallbackResponse);
                foreach ($words as $word) {
                    echo "data: " . json_encode([
                        'type' => 'content',
                        'content' => $word . ' '
                    ]) . "\n\n";
                    
                    if (ob_get_level()) {
                        ob_flush();
                    }
                    flush();
                    
                    usleep(50000); // 50ms 지연으로 타이핑 효과
                }
                
                // AI 응답 메시지 저장
                $aiMessage = ChatMessage::create([
                    'chat_room_id' => $chatRoom->id,
                    'user_id' => $request->user()->id,
                    'sended_type' => 'ai',
                    'content' => $fallbackResponse,
                    'metadata' => ['fallback' => true, 'error' => $e->getMessage()],
                ]);
                
                // 완료 신호
                echo "data: " . json_encode([
                    'type' => 'done',
                    'ai_message' => $aiMessage,
                    'fallback' => true
                ]) . "\n\n";
                
                if (ob_get_level()) {
                    ob_flush();
                }
                flush();
                
            }, 200, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Headers' => 'Cache-Control',
            ]);
        }
    }

    /**
     * AI 스트림 채팅 (실시간 응답)
     */
    public function chatStream(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'nullable|integer',
            'session_id' => 'nullable|string',
            'message' => 'required|string',
        ]);

        // user_id가 없으면 인증된 사용자 ID 사용
        $userId = $validated['user_id'] ?? $request->user()->id;
        
        // session_id를 기반으로 채팅방 찾기 또는 생성
        $sessionId = $validated['session_id'] ?? 'default_' . $userId;
        
        $chatRoom = ChatRoom::where('user_id', $userId)
            ->where('title', 'LIKE', '%' . $sessionId . '%')
            ->first();
            
        if (!$chatRoom) {
            $chatRoom = ChatRoom::create([
                'user_id' => $userId,
                'title' => 'AI 채팅 - ' . $sessionId . ' - ' . now()->format('Y-m-d H:i'),
            ]);
        }

        // 사용자 메시지 저장
        $userMessage = ChatMessage::create([
            'chat_room_id' => $chatRoom->id,
            'user_id' => $request->user()->id,
            'sended_type' => 'user',
            'content' => $validated['message'],
        ]);

        // SSE 헤더 설정
        $response = response()->stream(function () use ($validated, $chatRoom, $userMessage, $sessionId) {
            try {
                // AI 서버로 스트림 요청 전송
                $aiResponse = Http::timeout(30)->withOptions([
                    'stream' => true,
                    'headers' => [
                        'Accept' => 'text/event-stream',
                        'Cache-Control' => 'no-cache',
                    ]
                ])->post($this->aiServerUrl . '/chat', [
                    'user_id' => $userMessage->user_id,
                    'message' => $validated['message'],
                    'chat_room_id' => $chatRoom->id,
                    'context' => $this->getUserContext($userMessage->user_id),
                ]);

                if ($aiResponse->successful()) {
                    $fullResponse = '';
                    $metadata = null;
                    $toolInvocations = [];
                    $buffer = '';

                    $body = $aiResponse->getBody();
                    while (!$body->eof()) {
                        $chunk = $body->read(8192);
                        if ($chunk === '') {
                            break;
                        }

                        // 프론트엔드로 즉시 전달
                        echo $chunk;
                        flush();

                        // 버퍼에 누적하여 완전한 SSE 이벤트 단위로 파싱
                        $buffer .= $chunk;

                        while (($eventEnd = strpos($buffer, "\n\n")) !== false) {
                            $eventBlock = substr($buffer, 0, $eventEnd);
                            $buffer = substr($buffer, $eventEnd + 2);

                            $lines = explode("\n", $eventBlock);
                            foreach ($lines as $line) {
                                $line = trim($line);
                                if (strpos($line, 'data: ') === 0 || strpos($line, 'data:') === 0) {
                                    $data = strpos($line, 'data: ') === 0 ? substr($line, 6) : substr($line, 5);
                                    if ($data === '[DONE]') {
                                        break 3;
                                    }

                                    $decoded = json_decode($data, true);
                                    if ($decoded) {
                                        if (isset($decoded['ai_response']) && !empty($decoded['ai_response'])) {
                                            $fullResponse = $decoded['ai_response'];
                                        } elseif (isset($decoded['content']) && !empty($decoded['content'])) {
                                            $fullResponse = $decoded['content'];
                                        }
                                        if (isset($decoded['metadata'])) {
                                            $metadata = $decoded['metadata'];
                                        }
                                        if (isset($decoded['tool_invocations'])) {
                                            $toolInvocations = $decoded['tool_invocations'];
                                        }
                                    }
                                }
                            }
                        }
                    }

                    // AI 응답 메시지 저장
                    $aiMessage = ChatMessage::create([
                        'chat_room_id' => $chatRoom->id,
                        'user_id' => $userMessage->user_id,
                        'sended_type' => 'ai',
                        'content' => $fullResponse ?: '응답을 생성할 수 없습니다.',
                        'metadata' => $metadata,
                    ]);

                    // 도구 호출 기록 저장
                    foreach ($toolInvocations as $toolInvocation) {
                        ToolInvocation::create([
                            'message_id' => $aiMessage->id,
                            'chat_room_id' => $chatRoom->id,
                            'user_id' => $userMessage->user_id,
                            'tool_name' => $toolInvocation['tool_name'] ?? 'unknown',
                            'args' => $toolInvocation['args'] ?? [],
                            'result' => $toolInvocation['result'] ?? '',
                            'status' => $toolInvocation['status'] ?? 'OK',
                        ]);
                    }

                    // 완료 신호 전송
                    echo "data: " . json_encode([
                        'type' => 'complete',
                        'message_id' => $aiMessage->id,
                        'session_id' => $sessionId
                    ]) . "\n\n";
                    flush();

                } else {
                    // AI 서버 오류 시 대체 응답
                    $fallbackResponse = $this->generateFallbackResponse($validated['message']);
                    
                    echo "data: " . json_encode([
                        'type' => 'content',
                        'content' => $fallbackResponse,
                        'session_id' => $sessionId
                    ]) . "\n\n";
                    flush();

                    $aiMessage = ChatMessage::create([
                        'chat_room_id' => $chatRoom->id,
                        'user_id' => $userMessage->user_id,
                        'sended_type' => 'ai',
                        'content' => $fallbackResponse,
                        'metadata' => ['fallback' => true],
                    ]);

                    echo "data: " . json_encode([
                        'type' => 'complete',
                        'message_id' => $aiMessage->id,
                        'session_id' => $sessionId
                    ]) . "\n\n";
                    flush();
                }

            } catch (\Exception $e) {
                // 에러 발생 시
                $errorMessage = '죄송합니다. AI 서버와의 통신 중 오류가 발생했습니다.';
                echo "data: " . json_encode([
                    'type' => 'error',
                    'content' => $errorMessage,
                    'session_id' => $sessionId
                ]) . "\n\n";
                flush();
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Headers' => 'Cache-Control',
        ]);

        return $response;
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
     * 일정 최적화 요청
     *
     * @OA\Post(
     *     path="/api/ai/optimize-schedule",
     *     summary="AI 일정 최적화",
     *     tags={"AI 연동"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"date"},
     *             @OA\Property(property="date", type="string", format="date", example="2026-02-21")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="최적화 제안 반환",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="reasoning", type="string"),
     *             @OA\Property(property="changes", type="array", @OA\Items(type="object"))
     *         )
     *     )
     * )
     */
    public function optimizeSchedule(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date',
        ]);

        try {
            // 해당 날짜의 일정 조회 (cancelled 제외, is_locked=false만 이동 대상이지만 전체 전달하여 AI가 참고)
            $scheduleBlocks = ScheduleBlock::where('user_id', $request->user()->id)
                ->whereDate('starts_at', $validated['date'])
                ->where('state', '!=', 'cancelled')
                ->with(['task'])
                ->orderBy('starts_at')
                ->get();

            if ($scheduleBlocks->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'reasoning' => '해당 날짜에 일정이 없습니다.',
                    'changes' => [],
                ]);
            }

            // AI 서비스로 전달할 일정 데이터 구성
            $schedulesForAi = $scheduleBlocks->map(function ($block) {
                return [
                    'schedule_id' => $block->id,
                    'task_title' => $block->title ?: ($block->task ? $block->task->title : '(제목 없음)'),
                    'starts_at' => $block->starts_at->toIso8601String(),
                    'ends_at' => $block->ends_at->toIso8601String(),
                    'is_locked' => (bool) $block->is_locked,
                ];
            })->toArray();

            // AI 서비스로 최적화 요청
            $response = Http::timeout(60)->post($this->aiServerUrl . '/optimize-schedule', [
                'user_id' => $request->user()->id,
                'date' => $validated['date'],
                'schedules' => $schedulesForAi,
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json([
                    'success' => $data['success'] ?? true,
                    'reasoning' => $data['reasoning'] ?? '',
                    'changes' => $data['changes'] ?? [],
                ]);
            } else {
                throw new \Exception('AI server error: ' . $response->body());
            }
        } catch (\Exception $e) {
            error_log("일정 최적화 오류: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '일정 최적화에 실패했습니다: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AI 서버 연결 실패 시 대체 응답 생성
     */
    private function generateFallbackResponse(string $userMessage): string
    {
        $message = strtolower($userMessage);
        
        // 키워드 기반 간단한 응답 생성
        if (str_contains($message, '할 일') || str_contains($message, '일정') || str_contains($message, '추천')) {
            return "안녕하세요! 현재 AI 서버가 실행되지 않아 기본 응답을 드리고 있습니다.

일정 관리 기능을 사용하시려면:
1. 할 일 목록을 확인해보세요
2. 새로운 일정을 추가해보세요

AI 서버가 실행되면 더 정교한 일정 추천과 분석을 제공할 수 있습니다.";
        }

        if (str_contains($message, '안녕') || str_contains($message, 'hello') || str_contains($message, 'hi')) {
            return "안녕하세요! Plandy AI 어시스턴트입니다.

현재 AI 서버가 실행되지 않아 기본 응답을 드리고 있습니다.
일정 관리, 할 일 추천 등의 기능을 사용하실 수 있습니다.

AI 서버가 실행되면 더 정교하고 개인화된 도움을 드릴 수 있습니다!";
        }

        // 기본 응답
        return "안녕하세요! 현재 AI 서버가 실행되지 않아 기본 응답을 드리고 있습니다.

Plandy에서 제공하는 기능:
• 일정 관리 및 할 일 추천
• 개인화된 일정 조정

AI 서버가 실행되면 더 정교한 분석과 추천을 제공할 수 있습니다.";
    }

    /**
     * 사용자 컨텍스트 정보 수집
     */
    private function getUserContext(int $userId): array
    {
        $user = \App\Models\User::find($userId);

        // Basic context
        $context = [
            'user_preferences' => $user->preferences,
            'timezone' => $user->timezone,
            'recent_tasks' => Task::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->toArray(),
            'recent_schedule' => ScheduleBlock::where('user_id', $userId)
                ->where('starts_at', '>=', now()->subDays(7))
                ->get()
                ->toArray(),
        ];

        // Team & Sprint context
        $teams = $user->teams()->with(['activeSprint.tasks.assignee'])->get();
        $teamContext = [];
        foreach ($teams as $team) {
            $teamData = [
                'id' => $team->id,
                'name' => $team->name,
            ];
            $activeSprint = $team->activeSprint;
            if ($activeSprint) {
                $tasks = $activeSprint->tasks;
                $totalPoints = $tasks->sum('story_points');
                $completedPoints = $tasks->where('status', 'completed')->sum('story_points');
                $teamData['active_sprint'] = [
                    'id' => $activeSprint->id,
                    'name' => $activeSprint->name,
                    'goal' => $activeSprint->goal,
                    'start_date' => $activeSprint->start_date?->format('Y-m-d'),
                    'end_date' => $activeSprint->end_date?->format('Y-m-d'),
                    'total_points' => $totalPoints,
                    'completed_points' => $completedPoints,
                    'tasks' => $tasks->map(fn($t) => [
                        'title' => $t->title,
                        'status' => $t->status,
                        'priority' => $t->priority,
                        'story_points' => $t->story_points,
                        'assignee' => $t->assignee?->name,
                    ])->toArray(),
                ];
            }
            $teamContext[] = $teamData;
        }
        $context['teams'] = $teamContext;

        return $context;
    }
}
