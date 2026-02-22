<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // 데모 유저 생성 (이미 있으면 스킵)
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

        // 데모 팀 생성 (이미 있으면 스킵)
        $team = Team::firstOrCreate(
            ['name' => 'Flandy 데모팀'],
            [
                'description' => 'Flandy 데모 체험용 팀입니다.',
                'owner_id' => $demo->id,
            ]
        );

        // 데모 유저를 팀 admin으로 추가 (이미 있으면 스킵)
        TeamMember::firstOrCreate(
            ['team_id' => $team->id, 'user_id' => $demo->id],
            ['role' => 'admin']
        );

        $this->command->info("Demo account ready: demo@flandy.kr / demo1234");
    }
}
