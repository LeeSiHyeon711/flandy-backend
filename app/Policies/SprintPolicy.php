<?php

namespace App\Policies;

use App\Models\Sprint;
use App\Models\User;

class SprintPolicy
{
    public function view(User $user, Sprint $sprint): bool
    {
        return $sprint->team->members()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Sprint $sprint): bool
    {
        $team = $sprint->team;
        return $user->id === $team->owner_id
            || $team->members()->where('user_id', $user->id)->where('role', 'admin')->exists();
    }

    public function delete(User $user, Sprint $sprint): bool
    {
        $team = $sprint->team;
        return $user->id === $team->owner_id
            || $team->members()->where('user_id', $user->id)->where('role', 'admin')->exists();
    }
}
