<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HabitLog;
use App\Models\User;
use Carbon\Carbon;

class HabitLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        
        if ($users->isEmpty()) {
            $this->command->warn('사용자가 없습니다. UserSeeder를 먼저 실행하세요.');
            return;
        }

        $habitLogs = [];

        // 각 사용자별로 최근 30일간의 습관 로그 생성
        foreach ($users as $user) {
            $userEmail = $user->email;
            
            // 사용자별 습관 타입 정의
            $userHabits = $this->getUserHabits($userEmail);
            
            for ($i = 0; $i < 30; $i++) {
                $date = Carbon::now()->subDays($i);
                
                foreach ($userHabits as $habitType => $habitData) {
                    // 80% 확률로 습관 실행
                    if (rand(1, 100) <= 80) {
                        $habitLogs[] = [
                            'user_id' => $user->id,
                            'habit_type' => $habitType,
                            'logged_at' => $date->copy()->setTime(
                                rand($habitData['time_range'][0], $habitData['time_range'][1]), 
                                rand(0, 59)
                            ),
                            'amount' => $habitData['amount_range'][0] + 
                                       (rand(0, 100) / 100) * 
                                       ($habitData['amount_range'][1] - $habitData['amount_range'][0]),
                            'note' => $this->getRandomNote($habitType),
                        ];
                    }
                }
            }
        }

        foreach ($habitLogs as $logData) {
            HabitLog::create($logData);
        }
    }

    private function getUserHabits($userEmail)
    {
        $habits = [
            'kim@plandy.kr' => [
                'exercise' => [
                    'time_range' => [18, 21],
                    'amount_range' => [45, 90], // 분
                ],
                'study' => [
                    'time_range' => [20, 23],
                    'amount_range' => [20, 60], // 분
                ],
                'diet' => [
                    'time_range' => [8, 20],
                    'amount_range' => [1, 3], // 리터
                ],
                'sleep' => [
                    'time_range' => [22, 24],
                    'amount_range' => [6, 8], // 시간
                ],
            ],
            'lee@plandy.kr' => [
                'exercise' => [
                    'time_range' => [18, 20],
                    'amount_range' => [30, 60], // 분
                ],
                'study' => [
                    'time_range' => [7, 8],
                    'amount_range' => [10, 30], // 분
                ],
                'diet' => [
                    'time_range' => [12, 13],
                    'amount_range' => [1, 1], // 식사 횟수
                ],
                'exercise' => [
                    'time_range' => [19, 21],
                    'amount_range' => [20, 40], // 분
                ],
            ],
            'park@plandy.kr' => [
                'work' => [
                    'time_range' => [20, 23],
                    'amount_range' => [60, 120], // 분
                ],
                'social' => [
                    'time_range' => [19, 21],
                    'amount_range' => [60, 120], // 분
                ],
                'study' => [
                    'time_range' => [21, 23],
                    'amount_range' => [30, 60], // 분
                ],
                'exercise' => [
                    'time_range' => [6, 8],
                    'amount_range' => [30, 60], // 분
                ],
            ],
            'choi@plandy.kr' => [
                'study' => [
                    'time_range' => [20, 21],
                    'amount_range' => [20, 40], // 분
                ],
                'social' => [
                    'time_range' => [18, 20],
                    'amount_range' => [60, 120], // 분
                ],
                'hobby' => [
                    'time_range' => [21, 23],
                    'amount_range' => [30, 90], // 분
                ],
                'other' => [
                    'time_range' => [22, 23],
                    'amount_range' => [0, 1], // 시간
                ],
            ],
            'jung@plandy.kr' => [
                'work' => [
                    'time_range' => [9, 10],
                    'amount_range' => [30, 60], // 분
                ],
                'hobby' => [
                    'time_range' => [15, 17],
                    'amount_range' => [60, 120], // 분
                ],
                'study' => [
                    'time_range' => [21, 22],
                    'amount_range' => [20, 40], // 분
                ],
                'exercise' => [
                    'time_range' => [16, 18],
                    'amount_range' => [60, 120], // 분
                ],
            ],
        ];

        return $habits[$userEmail] ?? [];
    }

    private function getRandomNote($habitType)
    {
        $notes = [
            'exercise' => ['좋은 운동이었다', '조금 힘들었지만 만족', '새로운 운동 도전'],
            'study' => ['흥미로운 내용이었다', '새로운 지식을 얻었다', '집중이 잘 되었다'],
            'diet' => ['충분히 마셨다', '물 마시는 습관이 좋아졌다', '영양가 있는 식사를 했다'],
            'sleep' => ['잠을 잘 잤다', '피곤했지만 충분히 쉬었다', '새로운 하루 준비'],
            'work' => ['새로운 기술을 배웠다', '코딩 실력이 향상되었다', '문제를 해결했다'],
            'hobby' => ['창의적인 아이디어가 떠올랐다', '작품을 완성했다', '좋은 사진을 찍었다'],
            'social' => ['새로운 인맥을 만들었다', '좋은 정보를 얻었다', '가족과 좋은 시간을 보냈다'],
            'other' => ['SNS에서 벗어나 휴식을 취했다', '마음이 편안해졌다', '자연을 즐겼다'],
        ];

        $habitNotes = $notes[$habitType] ?? ['좋은 하루였다'];
        return $habitNotes[array_rand($habitNotes)];
    }
}