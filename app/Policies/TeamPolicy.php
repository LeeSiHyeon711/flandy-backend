<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function view(User $user, Team $team): bool
    {
        return $team->members()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Team $team): bool
    {
        return $user->id === $team->owner_id
            || $team->members()->where('user_id', $user->id)->where('role', 'admin')->exists();
    }

    public function delete(User $user, Team $team): bool
    {
        return $user->id === $team->owner_id;
    }

    public function manageMember(User $user, Team $team): bool
    {
        return $user->id === $team->owner_id
            || $team->members()->where('user_id', $user->id)->where('role', 'admin')->exists();
    }
}
