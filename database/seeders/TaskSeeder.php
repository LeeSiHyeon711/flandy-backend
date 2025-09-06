<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class TaskSeeder extends Seeder
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

        $tasks = [
            // 김철수의 작업들
            [
                'user_id' => $users->where('email', 'kim@plandy.kr')->first()->id,
                'title' => '프로젝트 기획서 작성',
                'description' => '새로운 웹 서비스 프로젝트의 기획서를 작성하고 요구사항을 정리합니다.',
                'start_time' => Carbon::now()->addDays(1)->setTime(9, 0),
                'deadline' => Carbon::now()->addDays(3)->setTime(18, 0),
                'repeat_rule' => null,
                'on_fail' => 'reschedule',
                'status' => 'pending',
                'labels' => ['업무', '중요', '기획'],
                'meta' => [
                    'priority' => 'high',
                    'estimated_duration' => 240, // 4시간
                    'category' => 'work'
                ],
            ],
            [
                'user_id' => $users->where('email', 'kim@plandy.kr')->first()->id,
                'title' => '운동하기',
                'description' => '헬스장에서 1시간 운동하기',
                'start_time' => Carbon::now()->addDays(1)->setTime(19, 0),
                'deadline' => Carbon::now()->addDays(1)->setTime(20, 0),
                'repeat_rule' => ['type' => 'weekly', 'days' => [1, 3, 5]], // 월, 수, 금
                'on_fail' => 'skip',
                'status' => 'pending',
                'labels' => ['건강', '운동', '습관'],
                'meta' => [
                    'priority' => 'medium',
                    'estimated_duration' => 60,
                    'category' => 'health'
                ],
            ],
            [
                'user_id' => $users->where('email', 'kim@plandy.kr')->first()->id,
                'title' => '독서하기',
                'description' => '비즈니스 서적 30페이지 읽기',
                'start_time' => Carbon::now()->addDays(2)->setTime(21, 0),
                'deadline' => Carbon::now()->addDays(2)->setTime(22, 0),
                'repeat_rule' => ['type' => 'daily'],
                'on_fail' => 'reschedule',
                'status' => 'pending',
                'labels' => ['학습', '독서', '개발'],
                'meta' => [
                    'priority' => 'low',
                    'estimated_duration' => 60,
                    'category' => 'learning'
                ],
            ],

            // 이영희의 작업들
            [
                'user_id' => $users->where('email', 'lee@plandy.kr')->first()->id,
                'title' => '코드 리뷰',
                'description' => '팀원들의 PR 코드 리뷰 및 피드백 작성',
                'start_time' => Carbon::now()->addDays(1)->setTime(10, 0),
                'deadline' => Carbon::now()->addDays(1)->setTime(12, 0),
                'repeat_rule' => null,
                'on_fail' => 'reschedule',
                'status' => 'pending',
                'labels' => ['업무', '코드리뷰', '팀워크'],
                'meta' => [
                    'priority' => 'high',
                    'estimated_duration' => 120,
                    'category' => 'work'
                ],
            ],
            [
                'user_id' => $users->where('email', 'lee@plandy.kr')->first()->id,
                'title' => '요가 클래스',
                'description' => '온라인 요가 클래스 참여',
                'start_time' => Carbon::now()->addDays(2)->setTime(18, 30),
                'deadline' => Carbon::now()->addDays(2)->setTime(19, 30),
                'repeat_rule' => ['type' => 'weekly', 'days' => [2, 4]], // 화, 목
                'on_fail' => 'skip',
                'status' => 'pending',
                'labels' => ['건강', '요가', '스트레스해소'],
                'meta' => [
                    'priority' => 'medium',
                    'estimated_duration' => 60,
                    'category' => 'health'
                ],
            ],

            // 박민수의 작업들
            [
                'user_id' => $users->where('email', 'park@plandy.kr')->first()->id,
                'title' => '데이터베이스 설계',
                'description' => '새로운 기능을 위한 DB 스키마 설계',
                'start_time' => Carbon::now()->addDays(1)->setTime(14, 0),
                'deadline' => Carbon::now()->addDays(2)->setTime(17, 0),
                'repeat_rule' => null,
                'on_fail' => 'reschedule',
                'status' => 'pending',
                'labels' => ['업무', 'DB', '설계'],
                'meta' => [
                    'priority' => 'high',
                    'estimated_duration' => 180,
                    'category' => 'work'
                ],
            ],
            [
                'user_id' => $users->where('email', 'park@plandy.kr')->first()->id,
                'title' => '가족과 저녁식사',
                'description' => '가족과 함께 저녁식사하며 대화하기',
                'start_time' => Carbon::now()->addDays(1)->setTime(19, 0),
                'deadline' => Carbon::now()->addDays(1)->setTime(20, 30),
                'repeat_rule' => ['type' => 'weekly', 'days' => [0]], // 일요일
                'on_fail' => 'reschedule',
                'status' => 'pending',
                'labels' => ['가족', '식사', '소통'],
                'meta' => [
                    'priority' => 'high',
                    'estimated_duration' => 90,
                    'category' => 'family'
                ],
            ],

            // 최지영의 작업들
            [
                'user_id' => $users->where('email', 'choi@plandy.kr')->first()->id,
                'title' => '마케팅 전략 회의',
                'description' => 'Q2 마케팅 전략 수립을 위한 팀 회의',
                'start_time' => Carbon::now()->addDays(1)->setTime(11, 0),
                'deadline' => Carbon::now()->addDays(1)->setTime(13, 0),
                'repeat_rule' => null,
                'on_fail' => 'reschedule',
                'status' => 'pending',
                'labels' => ['업무', '회의', '마케팅'],
                'meta' => [
                    'priority' => 'high',
                    'estimated_duration' => 120,
                    'category' => 'work'
                ],
            ],
            [
                'user_id' => $users->where('email', 'choi@plandy.kr')->first()->id,
                'title' => '언어학습',
                'description' => '영어 회화 연습 30분',
                'start_time' => Carbon::now()->addDays(1)->setTime(20, 0),
                'deadline' => Carbon::now()->addDays(1)->setTime(20, 30),
                'repeat_rule' => ['type' => 'daily'],
                'on_fail' => 'reschedule',
                'status' => 'pending',
                'labels' => ['학습', '영어', '언어'],
                'meta' => [
                    'priority' => 'medium',
                    'estimated_duration' => 30,
                    'category' => 'learning'
                ],
            ],

            // 정현우의 작업들
            [
                'user_id' => $users->where('email', 'jung@plandy.kr')->first()->id,
                'title' => '시스템 모니터링',
                'description' => '서버 상태 점검 및 성능 모니터링',
                'start_time' => Carbon::now()->addDays(1)->setTime(9, 0),
                'deadline' => Carbon::now()->addDays(1)->setTime(10, 0),
                'repeat_rule' => ['type' => 'daily'],
                'on_fail' => 'reschedule',
                'status' => 'pending',
                'labels' => ['업무', '모니터링', '시스템'],
                'meta' => [
                    'priority' => 'high',
                    'estimated_duration' => 60,
                    'category' => 'work'
                ],
            ],
            [
                'user_id' => $users->where('email', 'jung@plandy.kr')->first()->id,
                'title' => '취미 활동',
                'description' => '사진 촬영 및 편집',
                'start_time' => Carbon::now()->addDays(2)->setTime(15, 0),
                'deadline' => Carbon::now()->addDays(2)->setTime(17, 0),
                'repeat_rule' => ['type' => 'weekly', 'days' => [6]], // 토요일
                'on_fail' => 'skip',
                'status' => 'pending',
                'labels' => ['취미', '사진', '창작'],
                'meta' => [
                    'priority' => 'low',
                    'estimated_duration' => 120,
                    'category' => 'hobby'
                ],
            ],
        ];

        foreach ($tasks as $taskData) {
            Task::create($taskData);
        }
    }
}