<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BalanceScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'week_start',
        'score',
        'metrics',
    ];

    protected $casts = [
        'week_start' => 'date',
        'metrics' => 'array',
    ];

    // 관계 정의
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
