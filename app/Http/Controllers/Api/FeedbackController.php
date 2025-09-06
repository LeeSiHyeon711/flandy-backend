<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Models\ScheduleBlock;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FeedbackController extends Controller
{
    /**
     * 피드백 목록 조회
     */
    public function index(Request $request): JsonResponse
    {
        $feedbacks = Feedback::where('user_id', $request->user()->id)
            ->with(['scheduleBlock', 'task'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $feedbacks
        ]);
    }

    /**
     * 피드백 생성
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'block_id' => 'required|exists:schedule_blocks,id',
            'completed' => 'required|boolean',
            'actual_minutes' => 'required|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $scheduleBlock = ScheduleBlock::findOrFail($validated['block_id']);
        $this->authorize('view', $scheduleBlock);

        $validated['task_id'] = $scheduleBlock->task_id;
        $validated['user_id'] = $request->user()->id;

        $feedback = Feedback::create($validated);

        return response()->json([
            'success' => true,
            'data' => $feedback
        ], 201);
    }

    /**
     * 피드백 수정
     */
    public function update(Request $request, Feedback $feedback): JsonResponse
    {
        $this->authorize('update', $feedback);

        $validated = $request->validate([
            'completed' => 'sometimes|boolean',
            'actual_minutes' => 'sometimes|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $feedback->update($validated);

        return response()->json([
            'success' => true,
            'data' => $feedback
        ]);
    }

    /**
     * 피드백 삭제
     */
    public function destroy(Feedback $feedback): JsonResponse
    {
        $this->authorize('delete', $feedback);

        $feedback->delete();

        return response()->json([
            'success' => true,
            'message' => 'Feedback deleted successfully'
        ]);
    }
}
