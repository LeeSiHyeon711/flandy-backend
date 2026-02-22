<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('set null')->after('user_id');
            $table->foreignId('sprint_id')->nullable()->constrained()->onDelete('set null')->after('team_id');
            $table->foreignId('assignee_id')->nullable()->constrained('users')->onDelete('set null')->after('sprint_id');
            $table->unsignedInteger('story_points')->nullable()->after('meta');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropForeign(['sprint_id']);
            $table->dropForeign(['assignee_id']);
            $table->dropColumn(['team_id', 'sprint_id', 'assignee_id', 'story_points']);
        });
    }
};
