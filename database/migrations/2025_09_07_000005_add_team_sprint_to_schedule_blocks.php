<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedule_blocks', function (Blueprint $table) {
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('set null')->after('user_id');
            $table->foreignId('sprint_id')->nullable()->constrained()->onDelete('set null')->after('team_id');
        });
    }

    public function down(): void
    {
        Schema::table('schedule_blocks', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropForeign(['sprint_id']);
            $table->dropColumn(['team_id', 'sprint_id']);
        });
    }
};
