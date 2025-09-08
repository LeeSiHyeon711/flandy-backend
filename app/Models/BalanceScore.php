<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="BalanceScore",
 *     type="object",
 *     title="BalanceScore",
 *     description="워라벨 점수 정보",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="week_start", type="string", format="date", example="2025-01-06"),
 *     @OA\Property(property="score", type="integer", example=85),
 *     @OA\Property(property="metrics", type="object", example={"work": 80, "life": 90, "stress": 3}),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01T00:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01T00:00:00Z")
 * )
 */
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
