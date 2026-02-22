<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $teams = $request->user()->teams()->with(['owner', 'members.user'])->get();

        return response()->json([
            'success' => true,
            'data' => $teams
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $validated['owner_id'] = $request->user()->id;

        $team = Team::create($validated);

        // 생성자를 admin으로 멤버 추가
        $team->members()->create([
            'user_id' => $request->user()->id,
            'role' => 'admin',
        ]);

        $team->load(['owner', 'members.user']);

        return response()->json([
            'success' => true,
            'data' => $team
        ], 201);
    }

    public function show(Team $team): JsonResponse
    {
        $this->authorize('view', $team);

        $team->load(['owner', 'members.user', 'sprints', 'activeSprint']);

        return response()->json([
            'success' => true,
            'data' => $team
        ]);
    }

    public function update(Request $request, Team $team): JsonResponse
    {
        $this->authorize('update', $team);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
        ]);

        $team->update($validated);

        return response()->json([
            'success' => true,
            'data' => $team
        ]);
    }

    public function destroy(Team $team): JsonResponse
    {
        $this->authorize('delete', $team);

        $team->delete();

        return response()->json([
            'success' => true,
            'message' => 'Team deleted successfully'
        ]);
    }

    public function join(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'invite_code' => 'required|string|size:8',
        ]);

        $team = Team::where('invite_code', $validated['invite_code'])->first();

        if (!$team) {
            return response()->json([
                'success' => false,
                'message' => '유효하지 않은 초대 코드입니다.'
            ], 404);
        }

        $exists = $team->members()->where('user_id', $request->user()->id)->exists();
        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => '이미 팀에 소속되어 있습니다.'
            ], 409);
        }

        $team->members()->create([
            'user_id' => $request->user()->id,
            'role' => 'member',
        ]);

        $team->load(['owner', 'members.user']);

        return response()->json([
            'success' => true,
            'data' => $team,
            'message' => '팀에 참여했습니다.'
        ]);
    }

    public function leave(Request $request, Team $team): JsonResponse
    {
        if ($request->user()->id === $team->owner_id) {
            return response()->json([
                'success' => false,
                'message' => '팀 소유자는 팀을 탈퇴할 수 없습니다. 팀을 삭제하세요.'
            ], 403);
        }

        $team->members()->where('user_id', $request->user()->id)->delete();

        return response()->json([
            'success' => true,
            'message' => '팀에서 탈퇴했습니다.'
        ]);
    }

    public function updateMemberRole(Request $request, Team $team, TeamMember $member): JsonResponse
    {
        $this->authorize('manageMember', $team);

        $validated = $request->validate([
            'role' => 'required|in:admin,member',
        ]);

        if ($member->user_id === $team->owner_id) {
            return response()->json([
                'success' => false,
                'message' => '팀 소유자의 역할은 변경할 수 없습니다.'
            ], 403);
        }

        $member->update($validated);

        return response()->json([
            'success' => true,
            'data' => $member->load('user')
        ]);
    }

    public function removeMember(Request $request, Team $team, TeamMember $member): JsonResponse
    {
        $this->authorize('manageMember', $team);

        if ($member->user_id === $team->owner_id) {
            return response()->json([
                'success' => false,
                'message' => '팀 소유자는 제거할 수 없습니다.'
            ], 403);
        }

        $member->delete();

        return response()->json([
            'success' => true,
            'message' => '멤버가 제거되었습니다.'
        ]);
    }
}
