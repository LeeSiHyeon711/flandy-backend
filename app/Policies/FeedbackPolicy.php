<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;

class FeedbackPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Feedback $feedback): bool
    {
        return $user->id === $feedback->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Feedback $feedback): bool
    {
        return $user->id === $feedback->user_id;
    }

    public function delete(User $user, Feedback $feedback): bool
    {
        return $user->id === $feedback->user_id;
    }

    public function restore(User $user, Feedback $feedback): bool
    {
        return false;
    }

    public function forceDelete(User $user, Feedback $feedback): bool
    {
        return false;
    }
}
