<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="ScheduleBlock",
 *     type="object",
 *     title="ScheduleBlock",
 *     description="일정 블록 정보",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="task_id", type="integer", example=1),
 *     @OA\Property(property="starts_at", type="string", format="date-time", example="2025-01-06T10:00:00Z"),
 *     @OA\Property(property="ends_at", type="string", format="date-time", example="2025-01-06T11:00:00Z"),
 *     @OA\Property(property="is_locked", type="boolean", example=false),
 *     @OA\Property(property="source", type="string", enum={"user", "ai", "system"}, example="user"),
 *     @OA\Property(property="state", type="string", enum={"scheduled", "in_progress", "completed", "cancelled"}, example="scheduled"),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01T00:00:00Z")
 * )
 */
class ScheduleBlock extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_id',
        'user_id',
        'starts_at',
        'ends_at',
        'is_locked',
        'source',
        'state',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_locked' => 'boolean',
    ];

    // 관계 정의
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'block_id');
    }
}
