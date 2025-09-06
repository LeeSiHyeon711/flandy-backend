# Plandy API 레퍼런스

## 🔗 기본 정보
- **Base URL**: `http://127.0.0.1:8000/api`
- **Content-Type**: `application/json`
- **Accept**: `application/json`

## 🔐 인증
모든 API 요청에는 Bearer 토큰이 필요합니다 (헬스 체크 제외).

```http
Authorization: Bearer {your_token}
```

## 📋 API 엔드포인트 상세

### 인증 API

#### 로그인
```http
POST /api/auth/login
```

**Request Body:**
```json
{
    "email": "kim@plandy.kr",
    "password": "password"
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "email": "kim@plandy.kr",
            "name": "김철수",
            "timezone": "Asia/Seoul",
            "preferences": {
                "language": "ko",
                "theme": "light"
            }
        },
        "token": "1|abc123...",
        "token_type": "Bearer"
    }
}
```

#### 회원가입
```http
POST /api/auth/register
```

**Request Body:**
```json
{
    "email": "newuser@plandy.kr",
    "password": "password123",
    "name": "새사용자"
}
```

#### 로그아웃
```http
POST /api/auth/logout
```

#### 현재 사용자 정보
```http
GET /api/auth/me
```

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "email": "kim@plandy.kr",
        "name": "김철수",
        "timezone": "Asia/Seoul",
        "preferences": {
            "language": "ko",
            "theme": "light"
        }
    }
}
```

### 태스크 관리 API

#### 태스크 목록 조회
```http
GET /api/tasks
```

**Query Parameters:**
- `status`: 태스크 상태 필터 (pending, in_progress, completed, cancelled)
- `priority`: 우선순위 필터 (low, medium, high, urgent)
- `date`: 날짜 필터 (YYYY-MM-DD)

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "기획서 작성",
            "description": "Q1 마케팅 기획서 작성",
            "status": "in_progress",
            "priority": "high",
            "deadline": "2025-01-15T09:00:00Z",
            "labels": ["work", "urgent"],
            "created_at": "2025-01-01T00:00:00Z",
            "updated_at": "2025-01-01T00:00:00Z"
        }
    ]
}
```

#### 태스크 생성
```http
POST /api/tasks
```

**Request Body:**
```json
{
    "title": "새 태스크",
    "description": "태스크 설명",
    "priority": "medium",
    "deadline": "2025-01-20T18:00:00Z",
    "labels": ["work"]
}
```

#### 태스크 상세 조회
```http
GET /api/tasks/{id}
```

#### 태스크 수정
```http
PUT /api/tasks/{id}
```

#### 태스크 삭제
```http
DELETE /api/tasks/{id}
```

### 스케줄 관리 API

#### 스케줄 목록 조회
```http
GET /api/schedule
```

**Query Parameters:**
- `date`: 특정 날짜의 스케줄 조회 (YYYY-MM-DD)
- `start_date`: 시작 날짜
- `end_date`: 종료 날짜

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "팀 미팅",
            "description": "주간 팀 미팅",
            "start_time": "2025-01-06T10:00:00Z",
            "end_time": "2025-01-06T11:00:00Z",
            "state": "scheduled",
            "source": "user",
            "task_id": 1,
            "created_at": "2025-01-01T00:00:00Z"
        }
    ]
}
```

#### 스케줄 생성
```http
POST /api/schedule
```

**Request Body:**
```json
{
    "title": "새 스케줄",
    "description": "스케줄 설명",
    "start_time": "2025-01-07T14:00:00Z",
    "end_time": "2025-01-07T15:00:00Z",
    "task_id": 1
}
```

#### 특정 날짜 스케줄 조회
```http
GET /api/schedule/date/{date}
```

### 워라밸 관리 API

#### 습관 로그 조회
```http
GET /api/worklife/habits
```

**Query Parameters:**
- `date`: 특정 날짜의 습관 로그 (YYYY-MM-DD)
- `habit_type`: 습관 타입 필터

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "habit_type": "exercise",
            "completed": true,
            "note": "30분 조깅 완료",
            "date": "2025-01-06",
            "created_at": "2025-01-06T00:00:00Z"
        }
    ]
}
```

#### 습관 로그 생성
```http
POST /api/worklife/habits
```

**Request Body:**
```json
{
    "habit_type": "exercise",
    "completed": true,
    "note": "30분 조깅 완료"
}
```

#### 워라밸 점수 조회
```http
GET /api/worklife/scores
```

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "week_start": "2025-01-06",
            "overall_score": 8.5,
            "work_score": 8.0,
            "life_score": 9.0,
            "stress_level": 3,
            "satisfaction": 4,
            "created_at": "2025-01-06T00:00:00Z"
        }
    ]
}
```

#### 주간 워라밸 점수 조회
```http
GET /api/worklife/scores/week/{weekStart}
```

### AI 연동 API

#### AI 채팅
```http
POST /api/ai/chat
```

**Request Body:**
```json
{
    "message": "오늘 할 일을 추천해줘",
    "context": {
        "current_tasks": 5,
        "schedule_count": 3
    }
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "response": "오늘은 중요한 태스크 3개를 우선적으로 처리하시는 것을 추천합니다...",
        "suggestions": [
            {
                "type": "task_priority",
                "content": "기획서 작성 작업을 오전에 완료하세요"
            }
        ]
    }
}
```

#### 워라밸 분석
```http
POST /api/ai/analyze-worklife
```

**Request Body:**
```json
{
    "period": "week",
    "include_suggestions": true
}
```

### 피드백 관리 API

#### 피드백 목록 조회
```http
GET /api/feedbacks
```

#### 피드백 생성
```http
POST /api/feedbacks
```

**Request Body:**
```json
{
    "type": "feature_request",
    "title": "새로운 기능 요청",
    "content": "캘린더 뷰에 드래그 앤 드롭 기능을 추가해주세요",
    "rating": 5
}
```

### 시스템 API

#### 서버 상태 확인
```http
GET /api/health
```

**Response:**
```json
{
    "status": "healthy",
    "timestamp": "2025-01-06T14:30:45.492102Z",
    "version": "1.0.0"
}
```

## 📊 데이터 모델

### User (사용자)
```json
{
    "id": 1,
    "email": "kim@plandy.kr",
    "name": "김철수",
    "timezone": "Asia/Seoul",
    "preferences": {
        "language": "ko",
        "theme": "light",
        "notifications": true
    },
    "created_at": "2025-01-01T00:00:00Z",
    "updated_at": "2025-01-01T00:00:00Z"
}
```

### Task (태스크)
```json
{
    "id": 1,
    "title": "기획서 작성",
    "description": "Q1 마케팅 기획서 작성",
    "status": "in_progress",
    "priority": "high",
    "deadline": "2025-01-15T09:00:00Z",
    "labels": ["work", "urgent"],
    "user_id": 1,
    "created_at": "2025-01-01T00:00:00Z",
    "updated_at": "2025-01-01T00:00:00Z"
}
```

### ScheduleBlock (스케줄)
```json
{
    "id": 1,
    "title": "팀 미팅",
    "description": "주간 팀 미팅",
    "start_time": "2025-01-06T10:00:00Z",
    "end_time": "2025-01-06T11:00:00Z",
    "state": "scheduled",
    "source": "user",
    "task_id": 1,
    "user_id": 1,
    "created_at": "2025-01-01T00:00:00Z"
}
```

### HabitLog (습관 로그)
```json
{
    "id": 1,
    "habit_type": "exercise",
    "completed": true,
    "note": "30분 조깅 완료",
    "date": "2025-01-06",
    "user_id": 1,
    "created_at": "2025-01-06T00:00:00Z"
}
```

### BalanceScore (워라밸 점수)
```json
{
    "id": 1,
    "week_start": "2025-01-06",
    "overall_score": 8.5,
    "work_score": 8.0,
    "life_score": 9.0,
    "stress_level": 3,
    "satisfaction": 4,
    "user_id": 1,
    "created_at": "2025-01-06T00:00:00Z"
}
```

## ⚠️ 에러 응답

### 400 Bad Request
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

### 401 Unauthorized
```json
{
    "message": "Unauthenticated."
}
```

### 422 Unprocessable Entity
```json
{
    "message": "The provided credentials are incorrect.",
    "errors": {
        "email": ["The provided credentials are incorrect."]
    }
}
```

### 500 Internal Server Error
```json
{
    "message": "Server Error"
}
```

## 🔧 사용 예제

### Python 예제
```python
import requests

# 로그인
response = requests.post('http://127.0.0.1:8000/api/auth/login', json={
    'email': 'kim@plandy.kr',
    'password': 'password'
})

if response.status_code == 200:
    data = response.json()
    token = data['data']['token']
    
    # 인증된 요청
    headers = {'Authorization': f'Bearer {token}'}
    tasks_response = requests.get('http://127.0.0.1:8000/api/tasks', headers=headers)
    print(tasks_response.json())
```

### JavaScript 예제
```javascript
// 로그인
const loginResponse = await fetch('http://127.0.0.1:8000/api/auth/login', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        email: 'kim@plandy.kr',
        password: 'password'
    })
});

const loginData = await loginResponse.json();
const token = loginData.data.token;

// 인증된 요청
const tasksResponse = await fetch('http://127.0.0.1:8000/api/tasks', {
    headers: {
        'Authorization': `Bearer ${token}`
    }
});

const tasks = await tasksResponse.json();
console.log(tasks);
```

## 📝 참고사항

1. **날짜 형식**: 모든 날짜는 ISO 8601 형식 (UTC)을 사용합니다.
2. **페이지네이션**: 목록 API는 향후 페이지네이션을 지원할 예정입니다.
3. **필터링**: 대부분의 목록 API는 다양한 필터 옵션을 지원합니다.
4. **에러 처리**: 모든 API는 일관된 에러 응답 형식을 사용합니다.
5. **토큰 만료**: 인증 토큰은 24시간 후 만료됩니다.
