<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Server Configuration
    |--------------------------------------------------------------------------
    |
    | AI 서버 연동을 위한 설정
    |
    */

    'server_url' => env('AI_SERVER_URL', 'http://localhost:8001'),
    'api_key' => env('AI_API_KEY', 'your_ai_api_key_here'),
    'timeout' => env('AI_TIMEOUT', 60),
    
    /*
    |--------------------------------------------------------------------------
    | AI Agent Configuration
    |--------------------------------------------------------------------------
    |
    | AI 에이전트별 설정
    |
    */
    
    'agents' => [
        'supervisor' => [
            'enabled' => true,
            'priority' => 1,
        ],
        'health' => [
            'enabled' => true,
            'priority' => 2,
        ],
        'plan' => [
            'enabled' => true,
            'priority' => 3,
        ],
        'data' => [
            'enabled' => true,
            'priority' => 4,
        ],
        'worklife' => [
            'enabled' => true,
            'priority' => 5,
        ],
        'communication' => [
            'enabled' => true,
            'priority' => 6,
        ],
    ],
];
