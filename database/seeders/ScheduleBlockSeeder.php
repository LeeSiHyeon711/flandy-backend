<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ScheduleBlock;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class ScheduleBlockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $tasks = Task::all();
        
        if ($users->isEmpty() || $tasks->isEmpty()) {
            $this->command->warn('사용자나 작업이 없습니다. UserSeeder와 TaskSeeder를 먼저 실행하세요.');
            return;
        }

        $scheduleBlocks = [];

        // 각 작업에 대해 일정 블록 생성
        foreach ($tasks as $task) {
            $startTime = $task->start_time;
            $endTime = $task->deadline;
            
            // 작업 시간이 4시간 이상이면 여러 블록으로 나누기
            $duration = $startTime->diffInMinutes($endTime);
            
            if ($duration > 240) { // 4시간 이상
                $blockCount = ceil($duration / 120); // 2시간씩 나누기
                $blockDuration = $duration / $blockCount;
                
                for ($i = 0; $i < $blockCount; $i++) {
                    $blockStart = $startTime->copy()->addMinutes($i * $blockDuration);
                    $blockEnd = $startTime->copy()->addMinutes(($i + 1) * $blockDuration);
                    
                    $scheduleBlocks[] = [
                        'task_id' => $task->id,
                        'user_id' => $task->user_id,
                        'starts_at' => $blockStart,
                        'ends_at' => $blockEnd,
                        'is_locked' => $i === 0, // 첫 번째 블록만 잠금
                        'source' => 'ai',
                        'state' => 'scheduled',
                    ];
                }
            } else {
                // 단일 블록
                $scheduleBlocks[] = [
                    'task_id' => $task->id,
                    'user_id' => $task->user_id,
                    'starts_at' => $startTime,
                    'ends_at' => $endTime,
                    'is_locked' => false,
                    'source' => 'user',
                    'state' => 'scheduled',
                ];
            }
        }

        // 추가적인 자유 시간 블록들 (AI가 추천한 휴식 시간)
        $freeTimeBlocks = [
            [
                'user_id' => $users->where('email', 'kim@plandy.kr')->first()->id,
                'starts_at' => Carbon::now()->addDays(1)->setTime(12, 0),
                'ends_at' => Carbon::now()->addDays(1)->setTime(13, 0),
                'is_locked' => false,
                'source' => 'ai',
                'state' => 'scheduled',
            ],
            [
                'user_id' => $users->where('email', 'kim@plandy.kr')->first()->id,
                'starts_at' => Carbon::now()->addDays(1)->setTime(15, 30),
                'ends_at' => Carbon::now()->addDays(1)->setTime(15, 45),
                'is_locked' => false,
                'source' => 'ai',
                'state' => 'scheduled',
            ],
            [
                'user_id' => $users->where('email', 'lee@plandy.kr')->first()->id,
                'starts_at' => Carbon::now()->addDays(1)->setTime(12, 30),
                'ends_at' => Carbon::now()->addDays(1)->setTime(13, 30),
                'is_locked' => false,
                'source' => 'ai',
                'state' => 'scheduled',
            ],
            [
                'user_id' => $users->where('email', 'park@plandy.kr')->first()->id,
                'starts_at' => Carbon::now()->addDays(1)->setTime(11, 0),
                'ends_at' => Carbon::now()->addDays(1)->setTime(12, 0),
                'is_locked' => false,
                'source' => 'ai',
                'state' => 'scheduled',
            ],
            [
                'user_id' => $users->where('email', 'choi@plandy.kr')->first()->id,
                'starts_at' => Carbon::now()->addDays(1)->setTime(14, 0),
                'ends_at' => Carbon::now()->addDays(1)->setTime(14, 15),
                'is_locked' => false,
                'source' => 'ai',
                'state' => 'scheduled',
            ],
            [
                'user_id' => $users->where('email', 'jung@plandy.kr')->first()->id,
                'starts_at' => Carbon::now()->addDays(1)->setTime(11, 0),
                'ends_at' => Carbon::now()->addDays(1)->setTime(12, 0),
                'is_locked' => false,
                'source' => 'ai',
                'state' => 'scheduled',
            ],
        ];

        // 모든 일정 블록 생성
        foreach ($scheduleBlocks as $blockData) {
            ScheduleBlock::create($blockData);
        }

        foreach ($freeTimeBlocks as $blockData) {
            ScheduleBlock::create($blockData);
        }
    }
}