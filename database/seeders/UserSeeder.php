<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'email' => 'kim@plandy.kr',
                'password_hash' => Hash::make('password123'),
                'name' => '김철수',
                'timezone' => 'Asia/Seoul',
                'preferences' => [
                    'theme' => 'light',
                    'notifications' => true,
                    'work_hours' => ['09:00', '18:00'],
                    'break_duration' => 15,
                    'language' => 'ko'
                ],
            ],
            [
                'email' => 'lee@plandy.kr',
                'password_hash' => Hash::make('password123'),
                'name' => '이영희',
                'timezone' => 'Asia/Seoul',
                'preferences' => [
                    'theme' => 'dark',
                    'notifications' => true,
                    'work_hours' => ['08:30', '17:30'],
                    'break_duration' => 20,
                    'language' => 'ko'
                ],
            ],
            [
                'email' => 'park@plandy.kr',
                'password_hash' => Hash::make('password123'),
                'name' => '박민수',
                'timezone' => 'Asia/Seoul',
                'preferences' => [
                    'theme' => 'light',
                    'notifications' => false,
                    'work_hours' => ['10:00', '19:00'],
                    'break_duration' => 10,
                    'language' => 'ko'
                ],
            ],
            [
                'email' => 'choi@plandy.kr',
                'password_hash' => Hash::make('password123'),
                'name' => '최지영',
                'timezone' => 'Asia/Seoul',
                'preferences' => [
                    'theme' => 'dark',
                    'notifications' => true,
                    'work_hours' => ['09:30', '18:30'],
                    'break_duration' => 25,
                    'language' => 'ko'
                ],
            ],
            [
                'email' => 'jung@plandy.kr',
                'password_hash' => Hash::make('password123'),
                'name' => '정현우',
                'timezone' => 'Asia/Seoul',
                'preferences' => [
                    'theme' => 'light',
                    'notifications' => true,
                    'work_hours' => ['08:00', '17:00'],
                    'break_duration' => 30,
                    'language' => 'ko'
                ],
            ],
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }
    }
}