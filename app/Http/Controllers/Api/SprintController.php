<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sprint;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SprintController extends Controller
{
    public function index(Request $request, Team $team): JsonResponse
    {
        $this->authorize('view', $team);

        $sprints = $team->sprints()
            ->withCount('tasks')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $sprints
        ]);
    }

    public function store(Request $request, Team $team): JsonResponse
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'goal' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'meta' => 'nullable|array',
        ]);

        $validated['team_id'] = $team->id;

        $sprint = Sprint::create($validated);

        return response()->json([
            'success' => true,
            'data' => $sprint
        ], 201);
    }

    public function show(Sprint $sprint): JsonResponse
    {
        $this->authorize('view', $sprint);

        $sprint->load(['tasks.assignee', 'team']);

        return response()->json([
            'success' => true,
            'data' => $sprint
        ]);
    }

    public function update(Request $request, Sprint $sprint): JsonResponse
    {
        $this->authorize('update', $sprint);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'goal' => 'nullable|string',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'meta' => 'nullable|array',
        ]);

        $sprint->update($validated);

        return response()->json([
            'success' => true,
            'data' => $sprint
        ]);
    }

    public function destroy(Sprint $sprint): JsonResponse
    {
        $this->authorize('delete', $sprint);

        $sprint->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sprint deleted successfully'
        ]);
    }

    public function activate(Sprint $sprint): JsonResponse
    {
        $this->authorize('update', $sprint);

        // 같은 팀의 다른 active 스프린트를 completed로 변경
        Sprint::where('team_id', $sprint->team_id)
            ->where('status', 'active')
            ->where('id', '!=', $sprint->id)
            ->update(['status' => 'completed']);

        $sprint->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'data' => $sprint,
            'message' => '스프린트가 활성화되었습니다.'
        ]);
    }

    public function complete(Sprint $sprint): JsonResponse
    {
        $this->authorize('update', $sprint);

        $sprint->update(['status' => 'completed']);

        return response()->json([
            'success' => true,
            'data' => $sprint,
            'message' => '스프린트가 완료되었습니다.'
        ]);
    }

    public function dashboard(Sprint $sprint): JsonResponse
    {
        $this->authorize('view', $sprint);

        $tasks = $sprint->tasks()->with('assignee')->get();

        $totalPoints = $tasks->sum('story_points') ?: 0;
        $completedPoints = $tasks->where('status', 'completed')->sum('story_points') ?: 0;
        $totalTasks = $tasks->count();

        $statusCounts = [
            'pending' => $tasks->where('status', 'pending')->count(),
            'in_progress' => $tasks->where('status', 'in_progress')->count(),
            'completed' => $tasks->where('status', 'completed')->count(),
            'cancelled' => $tasks->where('status', 'cancelled')->count(),
        ];

        // 멤버별 업무량
        $memberWorkload = $tasks->groupBy('assignee_id')->map(function ($memberTasks, $assigneeId) {
            $assignee = $memberTasks->first()->assignee;
            return [
                'user_id' => $assigneeId,
                'name' => $assignee ? $assignee->name : '미지정',
                'total' => $memberTasks->count(),
                'completed' => $memberTasks->where('status', 'completed')->count(),
                'points' => $memberTasks->sum('story_points') ?: 0,
            ];
        })->values();

        // 번다운 데이터: 날짜별 남은 포인트
        $burndown = [];
        if ($sprint->start_date && $sprint->end_date) {
            $period = new \DatePeriod(
                $sprint->start_date->copy(),
                new \DateInterval('P1D'),
                $sprint->end_date->copy()->addDay()
            );

            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                $completedByDate = $tasks
                    ->where('status', 'completed')
                    ->filter(fn($t) => $t->updated_at && $t->updated_at->format('Y-m-d') <= $dateStr)
                    ->sum('story_points') ?: 0;

                $burndown[] = [
                    'date' => $dateStr,
                    'remaining' => $totalPoints - $completedByDate,
                    'ideal' => max(0, $totalPoints - ($totalPoints * (array_search($dateStr, array_column($burndown, 'date')) + 1) / max(count(iterator_to_array($period)), 1))),
                ];
            }

            // ideal line 재계산
            $days = count($burndown);
            foreach ($burndown as $i => &$point) {
                $point['ideal'] = round($totalPoints * (1 - ($i + 1) / $days), 1);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'sprint' => $sprint,
                'total_points' => $totalPoints,
                'completed_points' => $completedPoints,
                'progress' => $totalPoints > 0 ? round($completedPoints / $totalPoints * 100, 1) : 0,
                'total_tasks' => $totalTasks,
                'status_counts' => $statusCounts,
                'member_workload' => $memberWorkload,
                'burndown' => $burndown,
            ]
        ]);
    }
}
