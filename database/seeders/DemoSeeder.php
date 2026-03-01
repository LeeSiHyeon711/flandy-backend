<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Task;
use App\Models\ScheduleBlock;
use App\Models\Sprint;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. 데모 유저 생성 ──
        $demo = User::firstOrCreate(
            ['email' => 'demo@flandy.kr'],
            [
                'name' => '데모 사용자',
                'password_hash' => Hash::make('demo1234'),
                'timezone' => 'Asia/Seoul',
                'preferences' => [
                    'theme' => 'dark',
                    'notifications' => true,
                    'work_hours' => ['09:00', '18:00'],
                    'break_duration' => 15,
                    'language' => 'ko',
                ],
            ]
        );

        // 팀원용 더미 유저들
        $member1 = User::firstOrCreate(
            ['email' => 'alice@flandy.kr'],
            [
                'name' => '김앨리스',
                'password_hash' => Hash::make('password123'),
                'timezone' => 'Asia/Seoul',
                'preferences' => ['theme' => 'light', 'language' => 'ko'],
            ]
        );
        $member2 = User::firstOrCreate(
            ['email' => 'bob@flandy.kr'],
            [
                'name' => '이밥',
                'password_hash' => Hash::make('password123'),
                'timezone' => 'Asia/Seoul',
                'preferences' => ['theme' => 'dark', 'language' => 'ko'],
            ]
        );

        // ── 2. 데모 태스크들 ──
        $today = Carbon::today();

        $tasks = [
            [
                'user_id' => $demo->id,
                'title' => 'Flutter 앱 UI 개선',
                'description' => '홈 화면과 대시보드 UI를 Material 3 디자인에 맞춰 개선합니다.',
                'start_time' => $today->copy()->setTime(9, 0),
                'deadline' => $today->copy()->addDays(2)->setTime(18, 0),
                'status' => 'in_progress',
                'labels' => ['개발', 'UI', 'Flutter'],
                'meta' => ['priority' => 'high', 'estimated_duration' => 480, 'category' => 'work'],
                'story_points' => 5,
            ],
            [
                'user_id' => $demo->id,
                'title' => 'API 엔드포인트 테스트 작성',
                'description' => '백엔드 API의 단위 테스트를 작성합니다. 인증, 태스크, 일정 CRUD를 커버합니다.',
                'start_time' => $today->copy()->setTime(14, 0),
                'deadline' => $today->copy()->addDays(3)->setTime(17, 0),
                'status' => 'pending',
                'labels' => ['개발', '테스트', 'Backend'],
                'meta' => ['priority' => 'high', 'estimated_duration' => 360, 'category' => 'work'],
                'story_points' => 3,
            ],
            [
                'user_id' => $demo->id,
                'title' => '주간 팀 회의',
                'description' => '매주 월요일 오전 스프린트 진행 상황 공유 및 이슈 논의',
                'start_time' => $today->copy()->addDay()->setTime(10, 0),
                'deadline' => $today->copy()->addDay()->setTime(11, 0),
                'repeat_rule' => ['type' => 'weekly', 'days' => [1]],
                'status' => 'pending',
                'labels' => ['회의', '팀'],
                'meta' => ['priority' => 'medium', 'estimated_duration' => 60, 'category' => 'work'],
                'story_points' => 1,
            ],
            [
                'user_id' => $demo->id,
                'title' => '운동 - 러닝 5km',
                'description' => '저녁 러닝으로 체력 관리',
                'start_time' => $today->copy()->setTime(19, 0),
                'deadline' => $today->copy()->setTime(20, 0),
                'repeat_rule' => ['type' => 'weekly', 'days' => [1, 3, 5]],
                'on_fail' => 'skip',
                'status' => 'pending',
                'labels' => ['건강', '운동'],
                'meta' => ['priority' => 'medium', 'estimated_duration' => 60, 'category' => 'health'],
            ],
            [
                'user_id' => $demo->id,
                'title' => '독서 - 클린 아키텍처',
                'description' => '로버트 마틴의 클린 아키텍처 30페이지 읽기',
                'start_time' => $today->copy()->setTime(21, 0),
                'deadline' => $today->copy()->setTime(22, 0),
                'repeat_rule' => ['type' => 'daily'],
                'on_fail' => 'reschedule',
                'status' => 'pending',
                'labels' => ['학습', '독서'],
                'meta' => ['priority' => 'low', 'estimated_duration' => 60, 'category' => 'learning'],
            ],
            [
                'user_id' => $demo->id,
                'title' => 'DB 마이그레이션 리팩터링',
                'description' => '기존 마이그레이션 파일 정리 및 인덱스 최적화',
                'start_time' => $today->copy()->addDays(1)->setTime(14, 0),
                'deadline' => $today->copy()->addDays(1)->setTime(17, 0),
                'status' => 'pending',
                'labels' => ['개발', 'DB'],
                'meta' => ['priority' => 'medium', 'estimated_duration' => 180, 'category' => 'work'],
                'story_points' => 3,
            ],
            [
                'user_id' => $demo->id,
                'title' => '마케팅 자료 준비',
                'description' => '서비스 소개 슬라이드 10장 작성',
                'start_time' => $today->copy()->addDays(2)->setTime(10, 0),
                'deadline' => $today->copy()->addDays(4)->setTime(18, 0),
                'status' => 'pending',
                'labels' => ['마케팅', '문서'],
                'meta' => ['priority' => 'low', 'estimated_duration' => 240, 'category' => 'work'],
                'story_points' => 2,
            ],
            [
                'user_id' => $demo->id,
                'title' => '버그 수정: 로그인 후 리다이렉트',
                'description' => '로그인 성공 후 이전 페이지로 돌아가지 않는 버그 수정',
                'start_time' => $today->copy()->subDay()->setTime(10, 0),
                'deadline' => $today->copy()->subDay()->setTime(12, 0),
                'status' => 'completed',
                'labels' => ['개발', '버그'],
                'meta' => ['priority' => 'high', 'estimated_duration' => 120, 'category' => 'work'],
                'story_points' => 2,
            ],
        ];

        $createdTasks = [];
        foreach ($tasks as $taskData) {
            $createdTasks[] = Task::firstOrCreate(
                ['user_id' => $demo->id, 'title' => $taskData['title']],
                $taskData
            );
        }

        // ── 3. 데모 일정 블록들 ──
        $schedules = [
            // 오늘
            [
                'title' => 'Flutter UI 작업',
                'task_id' => $createdTasks[0]->id,
                'user_id' => $demo->id,
                'starts_at' => $today->copy()->setTime(9, 0),
                'ends_at' => $today->copy()->setTime(12, 0),
                'source' => 'user',
                'state' => 'in_progress',
            ],
            [
                'title' => '점심시간',
                'user_id' => $demo->id,
                'starts_at' => $today->copy()->setTime(12, 0),
                'ends_at' => $today->copy()->setTime(13, 0),
                'source' => 'ai',
                'state' => 'scheduled',
            ],
            [
                'title' => 'API 테스트 작성',
                'task_id' => $createdTasks[1]->id,
                'user_id' => $demo->id,
                'starts_at' => $today->copy()->setTime(14, 0),
                'ends_at' => $today->copy()->setTime(16, 0),
                'source' => 'user',
                'state' => 'scheduled',
            ],
            [
                'title' => '휴식',
                'user_id' => $demo->id,
                'starts_at' => $today->copy()->setTime(16, 0),
                'ends_at' => $today->copy()->setTime(16, 15),
                'source' => 'ai',
                'state' => 'scheduled',
            ],
            [
                'title' => '코드 리뷰',
                'user_id' => $demo->id,
                'starts_at' => $today->copy()->setTime(16, 30),
                'ends_at' => $today->copy()->setTime(18, 0),
                'source' => 'user',
                'state' => 'scheduled',
            ],
            [
                'title' => '러닝 5km',
                'task_id' => $createdTasks[3]->id,
                'user_id' => $demo->id,
                'starts_at' => $today->copy()->setTime(19, 0),
                'ends_at' => $today->copy()->setTime(20, 0),
                'source' => 'user',
                'state' => 'scheduled',
            ],
            [
                'title' => '독서',
                'task_id' => $createdTasks[4]->id,
                'user_id' => $demo->id,
                'starts_at' => $today->copy()->setTime(21, 0),
                'ends_at' => $today->copy()->setTime(22, 0),
                'source' => 'user',
                'state' => 'scheduled',
            ],
            // 내일
            [
                'title' => '주간 팀 회의',
                'task_id' => $createdTasks[2]->id,
                'user_id' => $demo->id,
                'starts_at' => $today->copy()->addDay()->setTime(10, 0),
                'ends_at' => $today->copy()->addDay()->setTime(11, 0),
                'source' => 'user',
                'state' => 'scheduled',
            ],
            [
                'title' => 'DB 마이그레이션 작업',
                'task_id' => $createdTasks[5]->id,
                'user_id' => $demo->id,
                'starts_at' => $today->copy()->addDay()->setTime(14, 0),
                'ends_at' => $today->copy()->addDay()->setTime(17, 0),
                'source' => 'ai',
                'state' => 'scheduled',
            ],
            // 모레
            [
                'title' => '마케팅 자료 작성',
                'task_id' => $createdTasks[6]->id,
                'user_id' => $demo->id,
                'starts_at' => $today->copy()->addDays(2)->setTime(10, 0),
                'ends_at' => $today->copy()->addDays(2)->setTime(12, 0),
                'source' => 'user',
                'state' => 'scheduled',
            ],
            [
                'title' => 'Flutter UI 마무리',
                'task_id' => $createdTasks[0]->id,
                'user_id' => $demo->id,
                'starts_at' => $today->copy()->addDays(2)->setTime(14, 0),
                'ends_at' => $today->copy()->addDays(2)->setTime(18, 0),
                'source' => 'ai',
                'state' => 'scheduled',
            ],
        ];

        foreach ($schedules as $scheduleData) {
            ScheduleBlock::firstOrCreate(
                [
                    'user_id' => $demo->id,
                    'title' => $scheduleData['title'],
                    'starts_at' => $scheduleData['starts_at'],
                ],
                $scheduleData
            );
        }

        // ── 4. 데모 팀 + 멤버 ──
        $team = Team::firstOrCreate(
            ['name' => 'Flandy 데모팀'],
            [
                'description' => 'Flandy 데모 체험용 팀입니다. 프로젝트 관리와 스프린트를 체험해보세요.',
                'owner_id' => $demo->id,
            ]
        );

        TeamMember::firstOrCreate(
            ['team_id' => $team->id, 'user_id' => $demo->id],
            ['role' => 'admin']
        );
        TeamMember::firstOrCreate(
            ['team_id' => $team->id, 'user_id' => $member1->id],
            ['role' => 'member']
        );
        TeamMember::firstOrCreate(
            ['team_id' => $team->id, 'user_id' => $member2->id],
            ['role' => 'member']
        );

        // 두 번째 팀
        $team2 = Team::firstOrCreate(
            ['name' => '사이드 프로젝트'],
            [
                'description' => '주말 사이드 프로젝트 팀',
                'owner_id' => $member1->id,
            ]
        );

        TeamMember::firstOrCreate(
            ['team_id' => $team2->id, 'user_id' => $member1->id],
            ['role' => 'admin']
        );
        TeamMember::firstOrCreate(
            ['team_id' => $team2->id, 'user_id' => $demo->id],
            ['role' => 'member']
        );

        // ── 5. 스프린트 ──
        $sprint = Sprint::firstOrCreate(
            ['team_id' => $team->id, 'name' => 'Sprint 1 - MVP 개발'],
            [
                'goal' => '핵심 기능 개발 완료 및 내부 테스트',
                'start_date' => $today->copy()->subDays(5),
                'end_date' => $today->copy()->addDays(9),
                'status' => 'active',
            ]
        );

        // 스프린트에 태스크 할당
        foreach ([$createdTasks[0], $createdTasks[1], $createdTasks[5], $createdTasks[7]] as $task) {
            if ($task->sprint_id !== $sprint->id) {
                $task->update([
                    'sprint_id' => $sprint->id,
                    'team_id' => $team->id,
                    'assignee_id' => $demo->id,
                ]);
            }
        }

        Sprint::firstOrCreate(
            ['team_id' => $team->id, 'name' => 'Sprint 2 - 안정화'],
            [
                'goal' => '버그 수정 및 성능 최적화',
                'start_date' => $today->copy()->addDays(10),
                'end_date' => $today->copy()->addDays(24),
                'status' => 'planning',
            ]
        );

        $this->command->info("Demo data seeded:");
        $this->command->info("  Account: demo@flandy.kr / demo1234");
        $this->command->info("  Tasks: " . count($tasks));
        $this->command->info("  Schedules: " . count($schedules));
        $this->command->info("  Teams: 2 (Flandy 데모팀, 사이드 프로젝트)");
        $this->command->info("  Sprints: 2 (Sprint 1 active, Sprint 2 planned)");
    }
}
