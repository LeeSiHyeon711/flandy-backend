<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ScheduleBlock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class ScheduleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/schedule",
     *     summary="일정 블록 목록 조회",
     *     tags={"일정 관리"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="일정 목록 조회 성공",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ScheduleBlock"))
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $scheduleBlocks = ScheduleBlock::where('user_id', $request->user()->id)
            ->with(['task', 'feedbacks'])
            ->orderBy('starts_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $scheduleBlocks
        ]);
    }

    /**
     * 특정 날짜의 일정 조회
     */
    public function getByDate(Request $request, string $date): JsonResponse
    {
        $scheduleBlocks = ScheduleBlock::where('user_id', $request->user()->id)
            ->whereDate('starts_at', $date)
            ->with(['task', 'feedbacks'])
            ->orderBy('starts_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $scheduleBlocks
        ]);
    }

    /**
     * 일정 블록 생성
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'task_id' => 'nullable|exists:tasks,id',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
            'is_locked' => 'boolean',
            'source' => 'in:user,ai,system',
            'state' => 'in:scheduled,in_progress,completed,cancelled',
        ]);

        $validated['user_id'] = $request->user()->id;

        $scheduleBlock = ScheduleBlock::create($validated);

        return response()->json([
            'success' => true,
            'data' => $scheduleBlock
        ], 201);
    }

    /**
     * 일정 블록 수정
     */
    public function update(Request $request, ScheduleBlock $scheduleBlock): JsonResponse
    {
        $this->authorize('update', $scheduleBlock);

        $validated = $request->validate([
            'starts_at' => 'sometimes|date',
            'ends_at' => 'sometimes|date|after:starts_at',
            'is_locked' => 'boolean',
            'state' => 'sometimes|in:scheduled,in_progress,completed,cancelled',
        ]);

        $scheduleBlock->update($validated);

        return response()->json([
            'success' => true,
            'data' => $scheduleBlock
        ]);
    }

    /**
     * 일정 블록 삭제
     */
    public function destroy(ScheduleBlock $scheduleBlock): JsonResponse
    {
        $this->authorize('delete', $scheduleBlock);

        $scheduleBlock->delete();

        return response()->json([
            'success' => true,
            'message' => 'Schedule block deleted successfully'
        ]);
    }
}
