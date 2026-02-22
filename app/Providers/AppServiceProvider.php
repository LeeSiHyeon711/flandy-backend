<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\ChatRoom;
use App\Models\Task;
use App\Models\ScheduleBlock;
use App\Models\Feedback;
use App\Models\Team;
use App\Models\Sprint;
use App\Policies\ChatRoomPolicy;
use App\Policies\TaskPolicy;
use App\Policies\ScheduleBlockPolicy;
use App\Policies\FeedbackPolicy;
use App\Policies\TeamPolicy;
use App\Policies\SprintPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(ChatRoom::class, ChatRoomPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);
        Gate::policy(ScheduleBlock::class, ScheduleBlockPolicy::class);
        Gate::policy(Feedback::class, FeedbackPolicy::class);
        Gate::policy(Team::class, TeamPolicy::class);
        Gate::policy(Sprint::class, SprintPolicy::class);
    }
}
