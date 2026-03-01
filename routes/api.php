<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\AiController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\SprintController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// 인증 관련 라우트 (인증 불필요)
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

// 인증이 필요한 API 라우트들
Route::middleware('auth:sanctum')->group(function () {
    
    // 인증 관련 라우트 (인증 필요)
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'changePassword']);
    });
    
    // 할 일 관리 API
    Route::apiResource('tasks', TaskController::class);
    
    // 일정 관리 API
    Route::prefix('schedule')->group(function () {
        Route::get('/', [ScheduleController::class, 'index']);
        Route::get('/date/{date}', [ScheduleController::class, 'getByDate']);
        Route::post('/', [ScheduleController::class, 'store']);
        Route::put('/{scheduleBlock}', [ScheduleController::class, 'update']);
        Route::delete('/{scheduleBlock}', [ScheduleController::class, 'destroy']);
    });
    
    // 피드백 관리 API
    Route::apiResource('feedbacks', FeedbackController::class);
    
    // AI 연동 API
    Route::prefix('ai')->group(function () {
        Route::post('/chat', [AiController::class, 'chat']);
        Route::post('/chat/stream', [AiController::class, 'chatStream']);
        Route::post('/reschedule', [AiController::class, 'reschedule']);
        Route::post('/optimize-schedule', [AiController::class, 'optimizeSchedule']);
    });

    // 팀 관리 API (구체적인 라우트를 apiResource보다 먼저 등록)
    Route::post('teams/join', [TeamController::class, 'join']);
    Route::post('teams/{team}/leave', [TeamController::class, 'leave']);
    Route::apiResource('teams', TeamController::class);
    Route::put('teams/{team}/members/{member}', [TeamController::class, 'updateMemberRole']);
    Route::delete('teams/{team}/members/{member}', [TeamController::class, 'removeMember']);

    // 스프린트 관리 API
    Route::get('teams/{team}/sprints', [SprintController::class, 'index']);
    Route::post('teams/{team}/sprints', [SprintController::class, 'store']);
    Route::get('sprints/{sprint}', [SprintController::class, 'show']);
    Route::put('sprints/{sprint}', [SprintController::class, 'update']);
    Route::delete('sprints/{sprint}', [SprintController::class, 'destroy']);
    Route::post('sprints/{sprint}/activate', [SprintController::class, 'activate']);
    Route::post('sprints/{sprint}/complete', [SprintController::class, 'complete']);
    Route::get('sprints/{sprint}/dashboard', [SprintController::class, 'dashboard']);

});

// AI 서버에서 호출하는 웹훅 엔드포인트들 (인증 없음)
Route::prefix('webhook')->group(function () {
    Route::post('/ai/chat-response', function (Request $request) {
        // AI 서버에서 채팅 응답을 받는 웹훅
        // 실제 구현에서는 AI 서버의 응답을 처리
        return response()->json(['success' => true]);
    });
    
    Route::post('/ai/schedule-update', function (Request $request) {
        // AI 서버에서 일정 업데이트를 받는 웹훅
        // 실제 구현에서는 일정 변경사항을 처리
        return response()->json(['success' => true]);
    });
});

// 헬스체크 엔드포인트
Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});
