<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class TaskController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/tasks",
     *     summary="할 일 목록 조회",
     *     tags={"할 일 관리"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="할 일 목록 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Task"))
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/api/tasks/{id}",
     *     summary="할 일 상세 조회",
     *     tags={"할 일 관리"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="할 일 ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="할 일 상세 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Task")
     *         )
     *     )
     * )
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
     * @OA\Post(
     *     path="/api/tasks",
     *     summary="할 일 생성",
     *     tags={"할 일 관리"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"title"},
     *             @OA\Property(property="title", type="string", example="기획서 작성"),
     *             @OA\Property(property="description", type="string", example="Q1 마케팅 기획서 작성"),
     *             @OA\Property(property="start_time", type="string", format="date-time", example="2025-01-15T09:00:00Z"),
     *             @OA\Property(property="deadline", type="string", format="date-time", example="2025-01-20T18:00:00Z"),
     *             @OA\Property(property="labels", type="array", @OA\Items(type="string"), example={"work", "urgent"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="할 일 생성 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/Task")
     *         )
     *     )
     * )
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
