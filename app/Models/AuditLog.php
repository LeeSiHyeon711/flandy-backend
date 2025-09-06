<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'actor',
        'action',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    // 관계 정의
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
