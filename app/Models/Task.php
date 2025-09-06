<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
