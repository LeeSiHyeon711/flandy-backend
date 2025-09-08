<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="Task",
 *     type="object",
 *     title="Task",
 *     description="할 일 정보",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="기획서 작성"),
 *     @OA\Property(property="description", type="string", example="Q1 마케팅 기획서 작성"),
 *     @OA\Property(property="start_time", type="string", format="date-time", example="2025-01-15T09:00:00Z"),
 *     @OA\Property(property="deadline", type="string", format="date-time", example="2025-01-20T18:00:00Z"),
 *     @OA\Property(property="status", type="string", enum={"pending", "in_progress", "completed", "cancelled"}, example="in_progress"),
 *     @OA\Property(property="labels", type="array", @OA\Items(type="string"), example={"work", "urgent"}),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01T00:00:00Z")
 * )
 */
class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'start_time',
        'deadline',
        'repeat_rule',
        'on_fail',
        'status',
        'labels',
        'meta',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'deadline' => 'datetime',
        'repeat_rule' => 'array',
        'labels' => 'array',
        'meta' => 'array',
    ];

    // 관계 정의
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scheduleBlocks()
    {
        return $this->hasMany(ScheduleBlock::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }
}
