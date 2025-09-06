<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BalanceScore;
use App\Models\HabitLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class WorkLifeController extends Controller
{
    /**
     * 워라벨 점수 목록 조회
     */
    public function getBalanceScores(Request $request): JsonResponse
    {
        $scores = BalanceScore::where('user_id', $request->user()->id)
            ->orderBy('week_start', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $scores
        ]);
    }

    /**
     * 특정 주의 워라벨 점수 조회
     */
    public function getBalanceScoreByWeek(Request $request, string $weekStart): JsonResponse
    {
        $score = BalanceScore::where('user_id', $request->user()->id)
            ->where('week_start', $weekStart)
            ->first();

        if (!$score) {
            return response()->json([
                'success' => false,
                'message' => 'Balance score not found for this week'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $score
        ]);
    }

    /**
     * 워라벨 점수 생성/업데이트
     */
    public function storeBalanceScore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'week_start' => 'required|date',
            'score' => 'required|integer|min:0|max:100',
            'metrics' => 'required|array',
        ]);

        $validated['user_id'] = $request->user()->id;

        $balanceScore = BalanceScore::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'week_start' => $validated['week_start']
            ],
            $validated
        );

        return response()->json([
            'success' => true,
            'data' => $balanceScore
        ], 201);
    }

    /**
     * 습관 로그 목록 조회
     */
    public function getHabitLogs(Request $request): JsonResponse
    {
        $habitLogs = HabitLog::where('user_id', $request->user()->id)
            ->orderBy('logged_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $habitLogs
        ]);
    }

    /**
     * 습관 로그 생성
     */
    public function storeHabitLog(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'habit_type' => 'required|in:exercise,sleep,diet,work,study,hobby,social,other',
            'logged_at' => 'required|date',
            'amount' => 'nullable|numeric',
            'note' => 'nullable|string',
        ]);

        $validated['user_id'] = $request->user()->id;

        $habitLog = HabitLog::create($validated);

        return response()->json([
            'success' => true,
            'data' => $habitLog
        ], 201);
    }

    /**
     * 현재 주의 워라벨 점수 계산 (AI 서버에서 호출)
     */
    public function calculateCurrentWeekScore(Request $request): JsonResponse
    {
        $user = $request->user();
        $weekStart = Carbon::now()->startOfWeek();

        // 이번 주의 습관 로그와 일정 데이터를 기반으로 점수 계산
        $habitLogs = HabitLog::where('user_id', $user->id)
            ->whereBetween('logged_at', [$weekStart, $weekStart->copy()->endOfWeek()])
            ->get();

        // 여기서 AI 서버로 데이터를 전송하여 점수 계산 요청
        // 실제 구현에서는 AI 서버 API를 호출

        $calculatedScore = 75; // 임시 점수
        $metrics = [
            'work_hours' => 40,
            'exercise_hours' => 5,
            'sleep_hours' => 56,
            'social_hours' => 10
        ];

        $balanceScore = BalanceScore::updateOrCreate(
            [
                'user_id' => $user->id,
                'week_start' => $weekStart->toDateString()
            ],
            [
                'score' => $calculatedScore,
                'metrics' => $metrics
            ]
        );

        return response()->json([
            'success' => true,
            'data' => $balanceScore
        ]);
    }
}
