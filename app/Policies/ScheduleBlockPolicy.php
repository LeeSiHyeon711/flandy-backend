<?php

namespace App\Policies;

use App\Models\ScheduleBlock;
use App\Models\User;

class ScheduleBlockPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, ScheduleBlock $scheduleBlock): bool
    {
        return $user->id === $scheduleBlock->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ScheduleBlock $scheduleBlock): bool
    {
        return $user->id === $scheduleBlock->user_id;
    }

    public function delete(User $user, ScheduleBlock $scheduleBlock): bool
    {
        return $user->id === $scheduleBlock->user_id;
    }

    public function restore(User $user, ScheduleBlock $scheduleBlock): bool
    {
        return false;
    }

    public function forceDelete(User $user, ScheduleBlock $scheduleBlock): bool
    {
        return false;
    }
}
