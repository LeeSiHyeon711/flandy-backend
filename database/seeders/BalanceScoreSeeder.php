<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BalanceScore;
use App\Models\User;
use Carbon\Carbon;

class BalanceScoreSeeder extends Seeder
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

        $balanceScores = [];

        // 각 사용자별로 최근 12주간의 워라벨 점수 생성
        foreach ($users as $user) {
            for ($week = 0; $week < 12; $week++) {
                $weekStart = Carbon::now()->subWeeks($week)->startOfWeek();
                
                // 사용자별 기본 점수와 변동성 설정
                $baseScore = $this->getUserBaseScore($user->email);
                $variation = rand(-15, 15); // ±15점 변동
                $score = max(0, min(100, $baseScore + $variation));
                
                $balanceScores[] = [
                    'user_id' => $user->id,
                    'week_start' => $weekStart,
                    'score' => $score,
                    'metrics' => $this->generateMetrics($score, $user->email),
                ];
            }
        }

        foreach ($balanceScores as $scoreData) {
            BalanceScore::create($scoreData);
        }
    }

    private function getUserBaseScore($userEmail)
    {
        $baseScores = [
            'kim@plandy.kr' => 75, // 김철수: 중간 정도의 워라벨
            'lee@plandy.kr' => 85, // 이영희: 좋은 워라벨 관리
            'park@plandy.kr' => 70, // 박민수: 약간 낮은 워라벨
            'choi@plandy.kr' => 80, // 최지영: 양호한 워라벨
            'jung@plandy.kr' => 78, // 정현우: 보통 수준
        ];

        return $baseScores[$userEmail] ?? 75;
    }

    private function generateMetrics($score, $userEmail)
    {
        // 점수에 따른 메트릭 생성
        $workHours = $this->getWorkHours($score);
        $personalHours = $this->getPersonalHours($score);
        $healthScore = $this->getHealthScore($score);
        $stressLevel = $this->getStressLevel($score);
        $satisfaction = $this->getSatisfaction($score);

        $metrics = [
            'work_life_balance' => $score,
            'work_hours' => $workHours,
            'personal_hours' => $personalHours,
            'health_score' => $healthScore,
            'stress_level' => $stressLevel,
            'satisfaction' => $satisfaction,
            'productivity' => $this->getProductivity($score),
            'energy_level' => $this->getEnergyLevel($score),
            'social_time' => $this->getSocialTime($score),
            'hobby_time' => $this->getHobbyTime($score),
            'family_time' => $this->getFamilyTime($score),
            'sleep_quality' => $this->getSleepQuality($score),
            'exercise_frequency' => $this->getExerciseFrequency($score),
            'meal_regularity' => $this->getMealRegularity($score),
            'break_time' => $this->getBreakTime($score),
        ];

        // 사용자별 특성 반영
        $userSpecificMetrics = $this->getUserSpecificMetrics($userEmail, $score);
        $metrics = array_merge($metrics, $userSpecificMetrics);

        return $metrics;
    }

    private function getWorkHours($score)
    {
        // 점수가 높을수록 적절한 근무시간
        if ($score >= 80) return rand(35, 45);
        if ($score >= 60) return rand(40, 50);
        if ($score >= 40) return rand(45, 55);
        return rand(50, 65);
    }

    private function getPersonalHours($score)
    {
        // 점수가 높을수록 개인시간 확보
        if ($score >= 80) return rand(25, 35);
        if ($score >= 60) return rand(20, 30);
        if ($score >= 40) return rand(15, 25);
        return rand(10, 20);
    }

    private function getHealthScore($score)
    {
        return max(0, min(100, $score + rand(-10, 10)));
    }

    private function getStressLevel($score)
    {
        // 점수가 높을수록 스트레스 낮음
        if ($score >= 80) return rand(1, 3);
        if ($score >= 60) return rand(2, 4);
        if ($score >= 40) return rand(3, 5);
        return rand(4, 6);
    }

    private function getSatisfaction($score)
    {
        return max(0, min(100, $score + rand(-15, 15)));
    }

    private function getProductivity($score)
    {
        return max(0, min(100, $score + rand(-5, 10)));
    }

    private function getEnergyLevel($score)
    {
        return max(0, min(100, $score + rand(-10, 5)));
    }

    private function getSocialTime($score)
    {
        if ($score >= 80) return rand(5, 10);
        if ($score >= 60) return rand(3, 8);
        if ($score >= 40) return rand(2, 6);
        return rand(1, 4);
    }

    private function getHobbyTime($score)
    {
        if ($score >= 80) return rand(8, 15);
        if ($score >= 60) return rand(5, 12);
        if ($score >= 40) return rand(3, 8);
        return rand(1, 5);
    }

    private function getFamilyTime($score)
    {
        if ($score >= 80) return rand(10, 20);
        if ($score >= 60) return rand(8, 15);
        if ($score >= 40) return rand(5, 12);
        return rand(3, 8);
    }

    private function getSleepQuality($score)
    {
        return max(0, min(100, $score + rand(-10, 10)));
    }

    private function getExerciseFrequency($score)
    {
        if ($score >= 80) return rand(4, 7);
        if ($score >= 60) return rand(3, 5);
        if ($score >= 40) return rand(2, 4);
        return rand(1, 3);
    }

    private function getMealRegularity($score)
    {
        if ($score >= 80) return rand(80, 100);
        if ($score >= 60) return rand(60, 85);
        if ($score >= 40) return rand(40, 70);
        return rand(20, 50);
    }

    private function getBreakTime($score)
    {
        if ($score >= 80) return rand(60, 90);
        if ($score >= 60) return rand(45, 75);
        if ($score >= 40) return rand(30, 60);
        return rand(15, 45);
    }

    private function getUserSpecificMetrics($userEmail, $score)
    {
        $specificMetrics = [];

        switch ($userEmail) {
            case 'kim@plandy.kr':
                $specificMetrics = [
                    'project_completion_rate' => rand(70, 95),
                    'meeting_efficiency' => rand(60, 90),
                    'learning_time' => rand(2, 5),
                ];
                break;
            case 'lee@plandy.kr':
                $specificMetrics = [
                    'code_review_quality' => rand(80, 100),
                    'team_collaboration' => rand(75, 95),
                    'wellness_activities' => rand(4, 7),
                ];
                break;
            case 'park@plandy.kr':
                $specificMetrics = [
                    'database_design_quality' => rand(70, 95),
                    'family_communication' => rand(60, 90),
                    'technical_learning' => rand(3, 6),
                ];
                break;
            case 'choi@plandy.kr':
                $specificMetrics = [
                    'marketing_effectiveness' => rand(75, 95),
                    'language_progress' => rand(60, 85),
                    'creative_output' => rand(2, 5),
                ];
                break;
            case 'jung@plandy.kr':
                $specificMetrics = [
                    'system_reliability' => rand(85, 100),
                    'photography_skills' => rand(70, 95),
                    'tech_trend_awareness' => rand(75, 95),
                ];
                break;
        }

        return $specificMetrics;
    }
}