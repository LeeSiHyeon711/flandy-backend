<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Plandy 데이터베이스 시딩을 시작합니다...');
        
        // 시더 실행 순서 (외래키 관계 고려)
        $this->call([
            UserSeeder::class,           // 1. 사용자 생성
            TaskSeeder::class,           // 2. 작업 생성
            ScheduleBlockSeeder::class,  // 3. 일정 블록 생성
            HabitLogSeeder::class,       // 4. 습관 로그 생성
            BalanceScoreSeeder::class,   // 5. 워라벨 점수 생성
            ChatSeeder::class,           // 6. 채팅 데이터 생성
        ]);
        
        $this->command->info('✅ 모든 시더가 성공적으로 실행되었습니다!');
        $this->command->info('📊 생성된 데이터:');
        $this->command->info('   - 사용자: 5명');
        $this->command->info('   - 작업: 12개');
        $this->command->info('   - 일정 블록: 20+ 개');
        $this->command->info('   - 습관 로그: 150+ 개');
        $this->command->info('   - 워라벨 점수: 60개 (12주 x 5명)');
        $this->command->info('   - 채팅방: 5개');
        $this->command->info('   - 채팅 메시지: 20+ 개');
    }
}