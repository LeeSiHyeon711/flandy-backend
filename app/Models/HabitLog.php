<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HabitLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'habit_type',
        'logged_at',
        'amount',
        'note',
    ];

    protected $casts = [
        'logged_at' => 'datetime',
        'amount' => 'decimal:2',
    ];

    // 관계 정의
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
