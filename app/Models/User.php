<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     title="User",
 *     description="사용자 정보",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="email", type="string", format="email", example="kim@plandy.kr"),
 *     @OA\Property(property="name", type="string", example="김철수"),
 *     @OA\Property(property="timezone", type="string", example="Asia/Seoul"),
 *     @OA\Property(
 *         property="preferences",
 *         type="object",
 *         @OA\Property(property="theme", type="string", example="light"),
 *         @OA\Property(property="notifications", type="boolean", example=true),
 *         @OA\Property(property="work_hours", type="array", @OA\Items(type="string"), example={"09:00", "18:00"}),
 *         @OA\Property(property="break_duration", type="integer", example=15),
 *         @OA\Property(property="language", type="string", example="ko")
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01T00:00:00Z")
 * )
 */
class User extends Model
{
    use HasFactory, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'email',
        'password_hash',
        'name',
        'timezone',
        'preferences',
    ];

    protected $casts = [
        'preferences' => 'array',
    ];

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // 관계 정의
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function scheduleBlocks()
    {
        return $this->hasMany(ScheduleBlock::class);
    }

    public function habitLogs()
    {
        return $this->hasMany(HabitLog::class);
    }

    public function balanceScores()
    {
        return $this->hasMany(BalanceScore::class);
    }

    public function chatRooms()
    {
        return $this->hasMany(ChatRoom::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}