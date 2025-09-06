<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    protected $fillable = [
        'block_id',
        'task_id',
        'user_id',
        'completed',
        'actual_minutes',
        'notes',
    ];

    protected $casts = [
        'completed' => 'boolean',
    ];

    // 관계 정의
    public function scheduleBlock()
    {
        return $this->belongsTo(ScheduleBlock::class, 'block_id');
    }

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
