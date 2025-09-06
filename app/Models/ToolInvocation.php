<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ToolInvocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_id',
        'chat_room_id',
        'user_id',
        'tool_name',
        'args',
        'result',
        'status',
    ];

    protected $casts = [
        'args' => 'array',
        'result' => 'array',
    ];

    // 관계 정의
    public function message()
    {
        return $this->belongsTo(ChatMessage::class, 'message_id');
    }

    public function chatRoom()
    {
        return $this->belongsTo(ChatRoom::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
