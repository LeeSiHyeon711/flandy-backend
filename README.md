# Plandy - AI 기반 일정/워라벨 관리 시스템

Plandy는 AI 에이전트를 활용한 지능형 일정 관리 및 워라벨 분석 시스템입니다.

## 🏗️ 시스템 아키텍처

### 구성 요소
- **프론트엔드**: Streamlit (Python)
- **백엔드**: Laravel (PHP)
- **AI 서버**: Python (에이전트 기반)
- **데이터베이스**: MySQL

### AI 에이전트 구조
```
SupervisorAgent (전체 흐름 제어)
├── HealthAgent (건강/습관 데이터 처리)
├── PlanAgent (일정 생성·조정)
├── DataAgent (DB와 데이터 연동)
├── WorkLifeBalanceAgent (워라벨 점수 계산)
└── CommunicationAgent (사용자에게 답변 전달)
```

## 📊 데이터베이스 구조

### 주요 테이블
- `users`: 사용자 정보
- `tasks`: 할 일 정의
- `schedule_blocks`: 시간 블록 단위 일정
- `feedbacks`: 실행 후 피드백 로그
- `habit_logs`: 습관 기록
- `balance_scores`: 워라벨 점수 기록
- `chat_rooms`, `chat_messages`, `tool_invocations`: 대화 및 도구 호출 기록
- `audit_logs`: 로그 기록

## 🚀 설치 및 실행

### 1. Laravel 백엔드 설정

```bash
# 의존성 설치
composer install

# 환경 설정
cp .env.example .env
php artisan key:generate

# 데이터베이스 설정
# .env 파일에서 DB 설정 수정

# 마이그레이션 실행
php artisan migrate

# 서버 실행
php artisan serve
```

### 2. Streamlit 프론트엔드 설정

```bash
# 의존성 설치
pip install streamlit requests plotly pandas

# 앱 실행
streamlit run streamlit_api_examples.py
```

### 3. AI 서버 설정

```bash
# AI 서버 실행 (별도 구현 필요)
python ai_server.py
```

## 📡 API 엔드포인트

### 할 일 관리
- `GET /api/tasks` - 할 일 목록 조회
- `POST /api/tasks` - 할 일 생성
- `PUT /api/tasks/{id}` - 할 일 수정
- `DELETE /api/tasks/{id}` - 할 일 삭제

### 일정 관리
- `GET /api/schedule` - 일정 목록 조회
- `GET /api/schedule/date/{date}` - 특정 날짜 일정 조회
- `POST /api/schedule` - 일정 블록 생성
- `PUT /api/schedule/{id}` - 일정 블록 수정
- `DELETE /api/schedule/{id}` - 일정 블록 삭제

### 워라벨 관리
- `GET /api/worklife/scores` - 워라벨 점수 목록 조회
- `GET /api/worklife/scores/week/{weekStart}` - 특정 주 워라벨 점수 조회
- `POST /api/worklife/scores` - 워라벨 점수 생성/업데이트
- `GET /api/worklife/habits` - 습관 로그 목록 조회
- `POST /api/worklife/habits` - 습관 로그 생성

### AI 연동
- `POST /api/ai/chat` - AI 채팅 메시지 전송
- `POST /api/ai/reschedule` - 일정 재조정 요청
- `POST /api/ai/analyze-worklife` - 워라벨 분석 요청

### 웹훅 (AI 서버용)
- `POST /api/webhook/ai/chat-response` - AI 채팅 응답 수신
- `POST /api/webhook/ai/schedule-update` - AI 일정 업데이트 수신
- `POST /api/webhook/ai/worklife-analysis` - AI 워라벨 분석 결과 수신

## 🔧 설정

### 환경 변수
```env
# AI 서버 연동
AI_SERVER_URL=http://localhost:8001
AI_API_KEY=your_ai_api_key_here

# 데이터베이스
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=plandy
DB_USERNAME=root
DB_PASSWORD=
```

## 📱 Streamlit 사용 예시

```python
from streamlit_api_examples import PlandyAPI

# API 클라이언트 초기화
api = PlandyAPI("http://localhost:8000/api", "your_token")

# 할 일 조회
tasks = api.get_tasks()

# 일정 조회
schedule = api.get_schedule("2024-01-15")

# AI 채팅
response = api.send_ai_chat(1, "오늘 일정을 알려줘")

# 워라벨 분석
analysis = api.analyze_worklife_balance("2024-01-15")
```

## 🔐 인증

API는 Laravel Sanctum을 사용한 토큰 기반 인증을 지원합니다.

```bash
# 토큰 생성 (예시)
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password"}'
```

## 📈 모니터링

### 헬스체크
- `GET /api/health` - 서버 상태 확인

### 로그
- Laravel 로그: `storage/logs/laravel.log`
- API 요청 로그: `audit_logs` 테이블

## 🤝 기여

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 라이선스

이 프로젝트는 MIT 라이선스 하에 배포됩니다.

## 📞 지원

문제가 있거나 질문이 있으시면 이슈를 생성해주세요.