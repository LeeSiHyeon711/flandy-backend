# Plandy 개발 가이드

## 🚀 프로젝트 시작하기

### 1. 백엔드 서버 실행
```bash
# Laragon 환경에서
cd C:\laragon\www\plandy-backend

# PHP 경로 설정 (필요시)
$env:PATH += ";C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64"

# 서버 실행
php artisan serve --host=127.0.0.1 --port=8000
```

### 2. 데이터베이스 확인
```bash
# 마이그레이션 상태 확인
php artisan migrate:status

# 시더 재실행 (필요시)
php artisan migrate:fresh --seed
```

### 3. API 테스트
```bash
# Python 테스트 클라이언트 실행
python test_api_client.py

# 또는 curl로 테스트
curl -X GET http://127.0.0.1:8000/api/health
```

## 🛠️ 개발 환경 설정

### 필수 요구사항
- **PHP**: 8.3.16+
- **MySQL**: 8.0+
- **Laravel**: 11.x
- **Composer**: 최신 버전

### 권장 개발 도구
- **IDE**: VS Code, PhpStorm
- **API 테스트**: Postman, Insomnia
- **데이터베이스**: phpMyAdmin, MySQL Workbench

## 📁 프로젝트 구조 상세

```
plandy-backend/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/                    # API 컨트롤러
│   │           ├── AuthController.php  # 인증 관련
│   │           ├── TaskController.php  # 태스크 관리
│   │           ├── ScheduleController.php # 스케줄 관리
│   │           ├── WorkLifeController.php # 워라밸 관리
│   │           ├── AiController.php    # AI 연동
│   │           └── FeedbackController.php # 피드백 관리
│   ├── Models/                         # Eloquent 모델
│   │   ├── User.php
│   │   ├── Task.php
│   │   ├── ScheduleBlock.php
│   │   ├── HabitLog.php
│   │   ├── BalanceScore.php
│   │   └── ...
│   └── Providers/                      # 서비스 프로바이더
│       ├── AppServiceProvider.php
│       └── RouteServiceProvider.php
├── database/
│   ├── migrations/                     # 데이터베이스 마이그레이션
│   │   ├── 2024_01_01_000001_create_users_table.php
│   │   ├── 2024_01_01_000002_create_tasks_table.php
│   │   └── ...
│   └── seeders/                        # 데이터 시더
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       ├── TaskSeeder.php
│       └── ...
├── routes/
│   └── api.php                         # API 라우트 정의
├── config/                             # 설정 파일
│   ├── database.php
│   ├── cors.php
│   └── sanctum.php
├── storage/
│   └── logs/
│       └── laravel.log                 # 로그 파일
└── tests/                              # 테스트 파일
```

## 🔧 주요 설정 파일

### 환경 변수 (.env)
```env
APP_NAME=Plandy
APP_ENV=local
APP_KEY=base64:your-app-key
APP_DEBUG=true
APP_TIMEZONE=Asia/Seoul
APP_LOCALE=ko
APP_FAKER_LOCALE=ko_KR

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=plandy
DB_USERNAME=root
DB_PASSWORD=your_mysql_password

SANCTUM_STATEFUL_DOMAINS=localhost,127.0.0.1
```

### CORS 설정 (config/cors.php)
```php
<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],
    'allowed_origins_patterns' => [],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,
];
```

## 🧪 테스트 및 디버깅

### 로그 확인
```bash
# 실시간 로그 모니터링 (Windows)
Get-Content storage/logs/laravel.log -Wait

# 특정 오류 검색
Get-Content storage/logs/laravel.log | Select-String "ERROR"
```

### API 디버깅
```bash
# 라우트 목록 확인
php artisan route:list --path=api

# 특정 라우트 확인
php artisan route:list --name=auth

# 캐시 클리어
php artisan config:clear
php artisan route:clear
php artisan cache:clear
```

### 데이터베이스 디버깅
```bash
# 마이그레이션 상태
php artisan migrate:status

# 특정 마이그레이션 롤백
php artisan migrate:rollback --step=1

# 데이터베이스 리셋
php artisan migrate:fresh --seed
```

## 🔄 개발 워크플로우

### 1. 새로운 API 엔드포인트 추가
```bash
# 1. 컨트롤러 생성
php artisan make:controller Api/NewController

# 2. 라우트 추가 (routes/api.php)
Route::apiResource('new-endpoint', NewController::class);

# 3. 모델 생성 (필요시)
php artisan make:model NewModel -m

# 4. 마이그레이션 실행
php artisan migrate

# 5. 시더 생성 (필요시)
php artisan make:seeder NewSeeder
```

### 2. 코드 수정 후 확인사항
```bash
# 1. 문법 오류 확인
php artisan config:cache

# 2. 라우트 캐시 클리어
php artisan route:clear

# 3. API 테스트
curl -X GET http://127.0.0.1:8000/api/health

# 4. 로그 확인
Get-Content storage/logs/laravel.log -Tail 10
```

## 🐛 일반적인 문제 해결

### 1. PHP 명령어를 찾을 수 없음
```bash
# Laragon에서 PHP PATH 설정
$env:PATH += ";C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64"

# 또는 전체 경로 사용
C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64\php.exe artisan serve
```

### 2. 데이터베이스 연결 오류
```bash
# .env 파일 확인
cat .env | grep DB_

# MySQL 서비스 상태 확인
# Laragon에서 MySQL 시작

# 연결 테스트
php artisan tinker
>>> DB::connection()->getPdo();
```

### 3. 404 오류 (API 라우트)
```bash
# 라우트 캐시 클리어
php artisan route:clear

# 라우트 목록 확인
php artisan route:list --path=api

# RouteServiceProvider 확인
# bootstrap/providers.php에 등록되어 있는지 확인
```

### 4. CORS 오류
```bash
# CORS 설정 확인
cat config/cors.php

# 캐시 클리어
php artisan config:clear

# 프론트엔드에서 올바른 헤더 사용
# Accept: application/json
```

### 5. 인증 토큰 오류
```bash
# Sanctum 설치 확인
composer show laravel/sanctum

# 마이그레이션 확인
php artisan migrate:status

# 토큰 테이블 확인
# personal_access_tokens 테이블 존재 여부
```

## 📊 성능 모니터링

### 1. 쿼리 최적화
```php
// N+1 문제 방지
$users = User::with(['tasks', 'scheduleBlocks'])->get();

// 쿼리 로깅 활성화
DB::enableQueryLog();
// ... 쿼리 실행
dd(DB::getQueryLog());
```

### 2. API 응답 시간 측정
```bash
# curl로 응답 시간 측정
curl -w "@curl-format.txt" -o /dev/null -s http://127.0.0.1:8000/api/health

# curl-format.txt 내용:
#      time_namelookup:  %{time_namelookup}\n
#         time_connect:  %{time_connect}\n
#      time_appconnect:  %{time_appconnect}\n
#     time_pretransfer:  %{time_pretransfer}\n
#        time_redirect:  %{time_redirect}\n
#   time_starttransfer:  %{time_starttransfer}\n
#                      ----------\n
#           time_total:  %{time_total}\n
```

## 🔒 보안 고려사항

### 1. 환경 변수 보안
```bash
# .env 파일을 .gitignore에 추가
echo ".env" >> .gitignore

# 프로덕션 환경에서는 APP_DEBUG=false 설정
```

### 2. API 보안
```php
// Rate Limiting 설정
Route::middleware(['throttle:60,1'])->group(function () {
    // API 라우트들
});

// CORS 설정 제한
'allowed_origins' => ['http://localhost:3000', 'https://yourdomain.com'],
```

### 3. 데이터 검증
```php
// Request 클래스 사용
php artisan make:request StoreTaskRequest

// 검증 규칙 정의
public function rules()
{
    return [
        'title' => 'required|string|max:255',
        'email' => 'required|email|unique:users',
    ];
}
```

## 📈 확장 계획

### 1. 단기 계획
- [ ] API 문서화 (Swagger/OpenAPI)
- [ ] 단위 테스트 추가
- [ ] API 버전 관리
- [ ] 에러 핸들링 개선

### 2. 중기 계획
- [ ] 캐싱 시스템 도입 (Redis)
- [ ] 큐 시스템 구현
- [ ] 파일 업로드 기능
- [ ] 실시간 알림 (WebSocket)

### 3. 장기 계획
- [ ] 마이크로서비스 아키텍처
- [ ] 컨테이너화 (Docker)
- [ ] CI/CD 파이프라인
- [ ] 모니터링 시스템

## 📚 참고 자료

- [Laravel 공식 문서](https://laravel.com/docs)
- [Laravel Sanctum 문서](https://laravel.com/docs/sanctum)
- [RESTful API 설계 가이드](https://restfulapi.net/)
- [MySQL 최적화 가이드](https://dev.mysql.com/doc/refman/8.0/en/optimization.html)

## 🤝 기여 가이드

### 1. 코드 스타일
- PSR-12 코딩 표준 준수
- 의미있는 변수명 사용
- 주석 작성 (복잡한 로직)
- 에러 처리 포함

### 2. 커밋 메시지
```
feat: 새로운 기능 추가
fix: 버그 수정
docs: 문서 수정
style: 코드 스타일 변경
refactor: 코드 리팩토링
test: 테스트 추가/수정
```

### 3. Pull Request
- 명확한 제목과 설명
- 변경사항 요약
- 테스트 결과 포함
- 관련 이슈 링크

이제 백엔드 개발 환경이 완전히 구축되었으므로, 위의 가이드를 참고하여 효율적으로 개발을 진행하실 수 있습니다! 🚀
