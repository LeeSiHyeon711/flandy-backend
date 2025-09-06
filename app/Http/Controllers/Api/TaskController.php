<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    /**
     * 할 일 목록 조회
     */
    public function index(Request $request): JsonResponse
    {
        $tasks = Task::where('user_id', $request->user()->id)
            ->with(['scheduleBlocks', 'feedbacks'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $tasks
        ]);
    }

    /**
     * 할 일 상세 조회
     */
    public function show(Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task->load(['scheduleBlocks', 'feedbacks']);

        return response()->json([
            'success' => true,
            'data' => $task
        ]);
    }

    /**
     * 할 일 생성
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'nullable|date',
            'deadline' => 'nullable|date|after:start_time',
            'repeat_rule' => 'nullable|array',
            'on_fail' => 'nullable|string',
            'labels' => 'nullable|array',
            'meta' => 'nullable|array',
        ]);

        $validated['user_id'] = $request->user()->id;

        $task = Task::create($validated);

        return response()->json([
            'success' => true,
            'data' => $task
        ], 201);
    }

    /**
     * 할 일 수정
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'start_time' => 'nullable|date',
            'deadline' => 'nullable|date|after:start_time',
            'repeat_rule' => 'nullable|array',
            'on_fail' => 'nullable|string',
            'status' => 'sometimes|in:pending,in_progress,completed,cancelled',
            'labels' => 'nullable|array',
            'meta' => 'nullable|array',
        ]);

        $task->update($validated);

        return response()->json([
            'success' => true,
            'data' => $task
        ]);
    }

    /**
     * 할 일 삭제
     */
    public function destroy(Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully'
        ]);
    }
}
