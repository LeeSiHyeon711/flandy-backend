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

        try {
            // AI 서버로 스트림 요청 전송
            $response = Http::timeout(30)->withOptions([
                'stream' => true,
                'headers' => [
                    'Accept' => 'text/event-stream',
                    'Cache-Control' => 'no-cache',
                ]
            ])->post($this->aiServerUrl . '/chat', [
                'user_id' => $request->user()->id,
                'message' => $validated['message'],
                'chat_room_id' => $chatRoom->id,
                'context' => $this->getUserContext($request->user()->id),
            ]);

            if ($response->successful()) {
                // 진짜 스트림만 처리
                return response()->stream(function () use ($response, $chatRoom, $userMessage, $sessionId, $request) {
                    $fullResponse = '';
                    $metadata = null;
                    $toolInvocations = [];
                    
                    // 디버깅: 요청 정보 출력
                    error_log("AI 서버 요청: " . $this->aiServerUrl . '/chat');
                    error_log("사용자 메시지: " . $userMessage->content);
                    
                    // AI 서버 스트림 데이터를 그대로 프론트엔드로 전달
                    $body = $response->getBody();
                    while (!$body->eof()) {
                        $chunk = $body->read(1024);
                        if ($chunk === '') {
                            break;
                        }
                        
                        // 디버깅: 받은 청크 출력
                        error_log("받은 청크: " . $chunk);
                        
                        // AI 서버에서 받은 청크를 그대로 프론트엔드로 전달
                        echo $chunk;
                        
                        // 즉시 flush하여 실시간 전송
                        if (ob_get_level()) {
                            ob_flush();
                        }
                        flush();
                        
                        // 백그라운드에서 파싱 (데이터베이스 저장용)
                        $lines = explode("\n", $chunk);
                        foreach ($lines as $line) {
                            $line = trim($line);
                            if (strpos($line, 'data: ') === 0) {
                                $data = substr($line, 6);
                                if ($data === '[DONE]') {
                                    break 2;
                                }
                                
                                $decoded = json_decode($data, true);
                                if ($decoded) {
                                    // 디버깅: 파싱된 데이터 출력
                                    error_log("파싱된 데이터: " . json_encode($decoded));
                                    
                                    if (isset($decoded['ai_response'])) {
                                        $fullResponse = $decoded['ai_response'];
                                    } elseif (isset($decoded['content'])) {
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
                    
                    // 디버깅: 최종 응답 출력
                    error_log("최종 응답: " . $fullResponse);
                    
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

                    // 스트림 데이터 읽기 및 프론트엔드로 전송
                    $body = $aiResponse->getBody();
                    while (!$body->eof()) {
                        $chunk = $body->read(1024);
                        if ($chunk === '') {
                            break;
                        }
                        
                        // SSE 형식으로 프론트엔드에 전송
                        $lines = explode("\n", $chunk);
                        foreach ($lines as $line) {
                            $line = trim($line);
                            if (strpos($line, 'data: ') === 0) {
                                $data = substr($line, 6);
                                if ($data === '[DONE]') {
                                    echo "data: [DONE]\n\n";
                                    break 2;
                                }
                                
                                $decoded = json_decode($data, true);
                                if ($decoded) {
                                    if (isset($decoded['content'])) {
                                        $fullResponse = $decoded['content'];
                                        echo "data: " . json_encode([
                                            'type' => 'content',
                                            'content' => $decoded['content'],
                                            'session_id' => $sessionId
                                        ]) . "\n\n";
                                        flush();
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

                    // AI 응답 메시지 저장
                    $aiMessage = ChatMessage::create([
                        'chat_room_id' => $chatRoom->id,
                        'user_id' => $userMessage->user_id,
                        'sended_type' => 'ai',
                        'content' => $fullResponse,
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
3. 워라벨 점수를 확인해보세요

AI 서버가 실행되면 더 정교한 일정 추천과 분석을 제공할 수 있습니다.";
        }
        
        if (str_contains($message, '워라벨') || str_contains($message, '균형')) {
            return "워라벨 분석 기능을 요청하셨군요! 현재 AI 서버가 실행되지 않아 기본 정보를 드립니다.

워라벨 개선을 위해:
1. 습관 로그를 꾸준히 기록하세요
2. 일정과 휴식 시간의 균형을 맞춰보세요
3. 정기적인 워라벨 점수 확인을 권장합니다

AI 서버가 실행되면 더 상세한 분석과 개인화된 추천을 제공할 수 있습니다.";
        }
        
        if (str_contains($message, '안녕') || str_contains($message, 'hello') || str_contains($message, 'hi')) {
            return "안녕하세요! Plandy AI 어시스턴트입니다. 

현재 AI 서버가 실행되지 않아 기본 응답을 드리고 있습니다. 
일정 관리, 할 일 추천, 워라벨 분석 등의 기능을 사용하실 수 있습니다.

AI 서버가 실행되면 더 정교하고 개인화된 도움을 드릴 수 있습니다!";
        }
        
        // 기본 응답
        return "안녕하세요! 현재 AI 서버가 실행되지 않아 기본 응답을 드리고 있습니다.

Plandy에서 제공하는 기능:
• 일정 관리 및 할 일 추천
• 워라벨 점수 분석
• 습관 추적 및 관리
• 개인화된 일정 조정

AI 서버가 실행되면 더 정교한 분석과 추천을 제공할 수 있습니다.";
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
