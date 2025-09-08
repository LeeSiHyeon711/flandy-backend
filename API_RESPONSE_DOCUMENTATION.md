# Plandy API 응답값 및 데이터 구조 문서

## 📋 개요

이 문서는 Plandy 백엔드 API의 모든 엔드포인트별 응답값과 데이터 구조를 상세히 정리한 문서입니다.

## 🔗 기본 정보

- **Base URL**: `http://127.0.0.1:8000/api`
- **Content-Type**: `application/json`
- **Accept**: `application/json`
- **인증**: Bearer Token (Sanctum)

## 📊 공통 응답 구조

### 성공 응답
```json
{
    "success": true,
    "data": { ... } | [ ... ],
    "message": "선택적 메시지"
}
```

### 실패 응답
```json
{
    "success": false,
    "message": "에러 메시지",
    "errors": { ... } // 유효성 검사 실패 시
}
```

---

## 🔐 인증 API (AuthController)

### 1. POST /api/auth/login - 사용자 로그인

**요청:**
```json
{
    "email": "kim@plandy.kr",
    "password": "password"
}
```

**응답 (200):**
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
                "theme": "light",
                "notifications": true,
                "work_hours": ["09:00", "18:00"],
                "break_duration": 15,
                "language": "ko"
            },
            "created_at": "2025-01-01T00:00:00Z",
            "updated_at": "2025-01-01T00:00:00Z"
        },
        "token": "1|abc123def456...",
        "token_type": "Bearer"
    }
}
```

**응답 (422 - 인증 실패):**
```json
{
    "message": "The provided credentials are incorrect.",
    "errors": {
        "email": ["The provided credentials are incorrect."]
    }
}
```

### 2. POST /api/auth/register - 사용자 등록

**요청:**
```json
{
    "name": "새사용자",
    "email": "newuser@plandy.kr",
    "password": "password123",
    "password_confirmation": "password123",
    "timezone": "Asia/Seoul"
}
```

**응답 (201):**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 2,
            "email": "newuser@plandy.kr",
            "name": "새사용자",
            "timezone": "Asia/Seoul",
            "preferences": {
                "theme": "light",
                "notifications": true,
                "work_hours": ["09:00", "18:00"],
                "break_duration": 15,
                "language": "ko"
            },
            "created_at": "2025-01-01T00:00:00Z",
            "updated_at": "2025-01-01T00:00:00Z"
        },
        "token": "2|xyz789abc123...",
        "token_type": "Bearer"
    }
}
```

### 3. POST /api/auth/logout - 로그아웃

**응답 (200):**
```json
{
    "success": true,
    "message": "Successfully logged out"
}
```

### 4. GET /api/auth/me - 현재 사용자 정보

**응답 (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "email": "kim@plandy.kr",
        "name": "김철수",
        "timezone": "Asia/Seoul",
        "preferences": {
            "theme": "light",
            "notifications": true,
            "work_hours": ["09:00", "18:00"],
            "break_duration": 15,
            "language": "ko"
        },
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z"
    }
}
```

### 5. PUT /api/auth/profile - 프로필 업데이트

**요청:**
```json
{
    "name": "김철수 (수정)",
    "timezone": "Asia/Tokyo",
    "preferences": {
        "theme": "dark",
        "notifications": false
    }
}
```

**응답 (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "email": "kim@plandy.kr",
        "name": "김철수 (수정)",
        "timezone": "Asia/Tokyo",
        "preferences": {
            "theme": "dark",
            "notifications": false,
            "work_hours": ["09:00", "18:00"],
            "break_duration": 15,
            "language": "ko"
        },
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T12:00:00Z"
    }
}
```

### 6. PUT /api/auth/password - 비밀번호 변경

**요청:**
```json
{
    "current_password": "oldpassword",
    "password": "newpassword123",
    "password_confirmation": "newpassword123"
}
```

**응답 (200):**
```json
{
    "success": true,
    "message": "Password updated successfully"
}
```

---

## 📝 할 일 관리 API (TaskController)

### 1. GET /api/tasks - 할 일 목록 조회

**응답 (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "기획서 작성",
            "description": "Q1 마케팅 기획서 작성",
            "start_time": "2025-01-15T09:00:00Z",
            "deadline": "2025-01-20T18:00:00Z",
            "status": "in_progress",
            "labels": ["work", "urgent"],
            "user_id": 1,
            "created_at": "2025-01-01T00:00:00Z",
            "updated_at": "2025-01-01T00:00:00Z",
            "schedule_blocks": [
                {
                    "id": 1,
                    "starts_at": "2025-01-15T09:00:00Z",
                    "ends_at": "2025-01-15T11:00:00Z",
                    "state": "scheduled"
                }
            ],
            "feedbacks": []
        }
    ]
}
```

### 2. GET /api/tasks/{id} - 할 일 상세 조회

**응답 (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "기획서 작성",
        "description": "Q1 마케팅 기획서 작성",
        "start_time": "2025-01-15T09:00:00Z",
        "deadline": "2025-01-20T18:00:00Z",
        "status": "in_progress",
        "labels": ["work", "urgent"],
        "user_id": 1,
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T00:00:00Z",
        "schedule_blocks": [
            {
                "id": 1,
                "starts_at": "2025-01-15T09:00:00Z",
                "ends_at": "2025-01-15T11:00:00Z",
                "state": "scheduled",
                "source": "user"
            }
        ],
        "feedbacks": []
    }
}
```

### 3. POST /api/tasks - 할 일 생성

**요청:**
```json
{
    "title": "새 태스크",
    "description": "태스크 설명",
    "start_time": "2025-01-15T09:00:00Z",
    "deadline": "2025-01-20T18:00:00Z",
    "labels": ["work", "important"]
}
```

**응답 (201):**
```json
{
    "success": true,
    "data": {
        "id": 2,
        "title": "새 태스크",
        "description": "태스크 설명",
        "start_time": "2025-01-15T09:00:00Z",
        "deadline": "2025-01-20T18:00:00Z",
        "status": "pending",
        "labels": ["work", "important"],
        "user_id": 1,
        "created_at": "2025-01-01T12:00:00Z",
        "updated_at": "2025-01-01T12:00:00Z"
    }
}
```

### 4. PUT /api/tasks/{id} - 할 일 수정

**요청:**
```json
{
    "title": "수정된 태스크",
    "status": "completed",
    "labels": ["work", "completed"]
}
```

**응답 (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "수정된 태스크",
        "description": "Q1 마케팅 기획서 작성",
        "start_time": "2025-01-15T09:00:00Z",
        "deadline": "2025-01-20T18:00:00Z",
        "status": "completed",
        "labels": ["work", "completed"],
        "user_id": 1,
        "created_at": "2025-01-01T00:00:00Z",
        "updated_at": "2025-01-01T15:00:00Z"
    }
}
```

### 5. DELETE /api/tasks/{id} - 할 일 삭제

**응답 (200):**
```json
{
    "success": true,
    "message": "Task deleted successfully"
}
```

---

## 📅 일정 관리 API (ScheduleController)

### 1. GET /api/schedule - 일정 블록 목록 조회

**응답 (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "task_id": 1,
            "starts_at": "2025-01-06T10:00:00Z",
            "ends_at": "2025-01-06T11:00:00Z",
            "is_locked": false,
            "source": "user",
            "state": "scheduled",
            "user_id": 1,
            "created_at": "2025-01-01T00:00:00Z",
            "updated_at": "2025-01-01T00:00:00Z",
            "task": {
                "id": 1,
                "title": "기획서 작성",
                "status": "in_progress"
            },
            "feedbacks": []
        }
    ]
}
```

### 2. GET /api/schedule/date/{date} - 특정 날짜 일정 조회

**응답 (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "task_id": 1,
            "starts_at": "2025-01-06T10:00:00Z",
            "ends_at": "2025-01-06T11:00:00Z",
            "is_locked": false,
            "source": "user",
            "state": "scheduled",
            "user_id": 1,
            "created_at": "2025-01-01T00:00:00Z",
            "updated_at": "2025-01-01T00:00:00Z",
            "task": {
                "id": 1,
                "title": "기획서 작성",
                "status": "in_progress"
            },
            "feedbacks": []
        }
    ]
}
```

### 3. POST /api/schedule - 일정 블록 생성

**요청:**
```json
{
    "task_id": 1,
    "starts_at": "2025-01-07T14:00:00Z",
    "ends_at": "2025-01-07T15:00:00Z",
    "is_locked": false,
    "source": "user",
    "state": "scheduled"
}
```

**응답 (201):**
```json
{
    "success": true,
    "data": {
        "id": 2,
        "task_id": 1,
        "starts_at": "2025-01-07T14:00:00Z",
        "ends_at": "2025-01-07T15:00:00Z",
        "is_locked": false,
        "source": "user",
        "state": "scheduled",
        "user_id": 1,
        "created_at": "2025-01-01T12:00:00Z",
        "updated_at": "2025-01-01T12:00:00Z"
    }
}
```

### 4. PUT /api/schedule/{id} - 일정 블록 수정

**요청:**
```json
{
    "starts_at": "2025-01-07T15:00:00Z",
    "ends_at": "2025-01-07T16:00:00Z",
    "state": "in_progress"
}
```

**응답 (200):**
```json
{
    "success": true,
    "data": {
        "id": 2,
        "task_id": 1,
        "starts_at": "2025-01-07T15:00:00Z",
        "ends_at": "2025-01-07T16:00:00Z",
        "is_locked": false,
        "source": "user",
        "state": "in_progress",
        "user_id": 1,
        "created_at": "2025-01-01T12:00:00Z",
        "updated_at": "2025-01-01T15:00:00Z"
    }
}
```

### 5. DELETE /api/schedule/{id} - 일정 블록 삭제

**응답 (200):**
```json
{
    "success": true,
    "message": "Schedule block deleted successfully"
}
```

---

## ⚖️ 워라벨 관리 API (WorkLifeController)

### 1. GET /api/worklife/scores - 워라벨 점수 목록 조회

**응답 (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "week_start": "2025-01-06",
            "score": 85,
            "metrics": {
                "work_hours": 40,
                "exercise_hours": 5,
                "sleep_hours": 56,
                "social_hours": 10,
                "stress_level": 3,
                "satisfaction": 4
            },
            "created_at": "2025-01-06T00:00:00Z",
            "updated_at": "2025-01-06T00:00:00Z"
        }
    ]
}
```

### 2. GET /api/worklife/scores/week/{weekStart} - 특정 주 워라벨 점수 조회

**응답 (200):**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "user_id": 1,
        "week_start": "2025-01-06",
        "score": 85,
        "metrics": {
            "work_hours": 40,
            "exercise_hours": 5,
            "sleep_hours": 56,
            "social_hours": 10,
            "stress_level": 3,
            "satisfaction": 4
        },
        "created_at": "2025-01-06T00:00:00Z",
        "updated_at": "2025-01-06T00:00:00Z"
    }
}
```

**응답 (404 - 점수 없음):**
```json
{
    "success": false,
    "message": "Balance score not found for this week"
}
```

### 3. POST /api/worklife/scores - 워라벨 점수 생성/업데이트

**요청:**
```json
{
    "week_start": "2025-01-13",
    "score": 90,
    "metrics": {
        "work_hours": 35,
        "exercise_hours": 7,
        "sleep_hours": 56,
        "social_hours": 15,
        "stress_level": 2,
        "satisfaction": 5
    }
}
```

**응답 (201):**
```json
{
    "success": true,
    "data": {
        "id": 2,
        "user_id": 1,
        "week_start": "2025-01-13",
        "score": 90,
        "metrics": {
            "work_hours": 35,
            "exercise_hours": 7,
            "sleep_hours": 56,
            "social_hours": 15,
            "stress_level": 2,
            "satisfaction": 5
        },
        "created_at": "2025-01-13T00:00:00Z",
        "updated_at": "2025-01-13T00:00:00Z"
    }
}
```

### 4. GET /api/worklife/habits - 습관 로그 목록 조회

**응답 (200):**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "habit_type": "exercise",
            "logged_at": "2025-01-06T00:00:00Z",
            "amount": 30,
            "note": "30분 조깅 완료",
            "created_at": "2025-01-06T00:00:00Z",
            "updated_at": "2025-01-06T00:00:00Z"
        }
    ]
}
```

### 5. POST /api/worklife/habits - 습관 로그 생성

**요청:**
```json
{
    "habit_type": "exercise",
    "logged_at": "2025-01-06T00:00:00Z",
    "amount": 30,
    "note": "30분 조깅 완료"
}
```

**응답 (201):**
```json
{
    "success": true,
    "data": {
        "id": 2,
        "user_id": 1,
        "habit_type": "exercise",
        "logged_at": "2025-01-06T00:00:00Z",
        "amount": 30,
        "note": "30분 조깅 완료",
        "created_at": "2025-01-06T12:00:00Z",
        "updated_at": "2025-01-06T12:00:00Z"
    }
}
```

### 6. POST /api/worklife/scores/calculate - 현재 주 워라벨 점수 계산

**응답 (200):**
```json
{
    "success": true,
    "data": {
        "id": 3,
        "user_id": 1,
        "week_start": "2025-01-06",
        "score": 75,
        "metrics": {
            "work_hours": 40,
            "exercise_hours": 5,
            "sleep_hours": 56,
            "social_hours": 10
        },
        "created_at": "2025-01-06T00:00:00Z",
        "updated_at": "2025-01-06T00:00:00Z"
    }
}
```

---

## 🤖 AI 연동 API (AiController)

### 1. POST /api/ai/chat - AI 채팅 메시지 전송

**요청:**
```json
{
    "chat_room_id": 1,
    "message": "오늘 할 일을 추천해줘"
}
```

**응답 (200 - 성공):**
```json
{
    "success": true,
    "data": {
        "user_message": {
            "id": 1,
            "chat_room_id": 1,
            "user_id": 1,
            "sended_type": "user",
            "content": "오늘 할 일을 추천해줘",
            "created_at": "2025-01-06T12:00:00Z"
        },
        "ai_message": {
            "id": 2,
            "chat_room_id": 1,
            "user_id": 1,
            "sended_type": "ai",
            "content": "오늘은 중요한 태스크 3개를 우선적으로 처리하시는 것을 추천합니다...",
            "metadata": {
                "suggestions": [
                    {
                        "type": "task_priority",
                        "content": "기획서 작성 작업을 오전에 완료하세요"
                    }
                ]
            },
            "created_at": "2025-01-06T12:00:05Z"
        },
        "tool_invocations": [
            {
                "tool_name": "get_user_tasks",
                "args": {"user_id": 1},
                "result": {"tasks": [...]},
                "status": "OK"
            }
        ]
    }
}
```

**응답 (500 - AI 서버 오류):**
```json
{
    "success": false,
    "message": "AI server communication error",
    "data": {
        "user_message": {
            "id": 1,
            "chat_room_id": 1,
            "user_id": 1,
            "sended_type": "user",
            "content": "오늘 할 일을 추천해줘",
            "created_at": "2025-01-06T12:00:00Z"
        },
        "error_message": {
            "id": 3,
            "chat_room_id": 1,
            "user_id": 1,
            "sended_type": "system",
            "content": "죄송합니다. AI 서버와의 통신 중 오류가 발생했습니다.",
            "metadata": {
                "error": "Connection timeout"
            },
            "created_at": "2025-01-06T12:00:10Z"
        }
    }
}
```

### 2. POST /api/ai/reschedule - 일정 재조정 요청

**요청:**
```json
{
    "date": "2025-01-07",
    "reason": "긴급 회의가 생겼습니다",
    "preferences": {
        "work_hours": ["09:00", "18:00"],
        "break_duration": 15
    }
}
```

**응답 (200):**
```json
{
    "success": true,
    "data": {
        "original_schedule": [
            {
                "id": 1,
                "starts_at": "2025-01-07T10:00:00Z",
                "ends_at": "2025-01-07T11:00:00Z",
                "task": {
                    "id": 1,
                    "title": "기획서 작성"
                }
            }
        ],
        "new_schedule": [
            {
                "id": 2,
                "task_id": 1,
                "starts_at": "2025-01-07T14:00:00Z",
                "ends_at": "2025-01-07T15:00:00Z",
                "source": "ai",
                "state": "scheduled",
                "user_id": 1,
                "created_at": "2025-01-06T12:00:00Z",
                "updated_at": "2025-01-06T12:00:00Z"
            }
        ],
        "reasoning": "긴급 회의를 위해 오후 시간으로 일정을 조정했습니다."
    }
}
```

### 3. POST /api/ai/analyze-worklife - 워라벨 점수 분석 요청

**요청:**
```json
{
    "week_start": "2025-01-06"
}
```

**응답 (200):**
```json
{
    "success": true,
    "data": {
        "balance_score": {
            "id": 1,
            "user_id": 1,
            "week_start": "2025-01-06",
            "score": 85,
            "metrics": {
                "work_hours": 40,
                "exercise_hours": 5,
                "sleep_hours": 56,
                "social_hours": 10,
                "stress_level": 3,
                "satisfaction": 4
            },
            "created_at": "2025-01-06T00:00:00Z",
            "updated_at": "2025-01-06T12:00:00Z"
        },
        "analysis": {
            "score": 85,
            "metrics": {
                "work_hours": 40,
                "exercise_hours": 5,
                "sleep_hours": 56,
                "social_hours": 10,
                "stress_level": 3,
                "satisfaction": 4
            },
            "insights": [
                "운동 시간이 부족합니다",
                "수면 시간은 적절합니다",
                "사회적 활동이 활발합니다"
            ]
        },
        "recommendations": [
            {
                "type": "exercise",
                "priority": "high",
                "content": "주 3회 이상 운동을 권장합니다"
            },
            {
                "type": "work_life_balance",
                "priority": "medium",
                "content": "업무 시간을 조금 줄이고 개인 시간을 늘려보세요"
            }
        ]
    }
}
```

---

## 📊 데이터 모델 구조

### User (사용자)
```json
{
    "id": 1,
    "email": "kim@plandy.kr",
    "name": "김철수",
    "timezone": "Asia/Seoul",
    "preferences": {
        "theme": "light",
        "notifications": true,
        "work_hours": ["09:00", "18:00"],
        "break_duration": 15,
        "language": "ko"
    },
    "created_at": "2025-01-01T00:00:00Z",
    "updated_at": "2025-01-01T00:00:00Z"
}
```

### Task (할 일)
```json
{
    "id": 1,
    "title": "기획서 작성",
    "description": "Q1 마케팅 기획서 작성",
    "start_time": "2025-01-15T09:00:00Z",
    "deadline": "2025-01-20T18:00:00Z",
    "status": "in_progress",
    "labels": ["work", "urgent"],
    "user_id": 1,
    "created_at": "2025-01-01T00:00:00Z",
    "updated_at": "2025-01-01T00:00:00Z"
}
```

### ScheduleBlock (일정 블록)
```json
{
    "id": 1,
    "task_id": 1,
    "starts_at": "2025-01-06T10:00:00Z",
    "ends_at": "2025-01-06T11:00:00Z",
    "is_locked": false,
    "source": "user",
    "state": "scheduled",
    "user_id": 1,
    "created_at": "2025-01-01T00:00:00Z",
    "updated_at": "2025-01-01T00:00:00Z"
}
```

### BalanceScore (워라벨 점수)
```json
{
    "id": 1,
    "user_id": 1,
    "week_start": "2025-01-06",
    "score": 85,
    "metrics": {
        "work_hours": 40,
        "exercise_hours": 5,
        "sleep_hours": 56,
        "social_hours": 10,
        "stress_level": 3,
        "satisfaction": 4
    },
    "created_at": "2025-01-06T00:00:00Z",
    "updated_at": "2025-01-06T00:00:00Z"
}
```

### HabitLog (습관 로그)
```json
{
    "id": 1,
    "user_id": 1,
    "habit_type": "exercise",
    "logged_at": "2025-01-06T00:00:00Z",
    "amount": 30,
    "note": "30분 조깅 완료",
    "created_at": "2025-01-06T00:00:00Z",
    "updated_at": "2025-01-06T00:00:00Z"
}
```

---

## ⚠️ 에러 응답 코드

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

### 403 Forbidden
```json
{
    "message": "This action is unauthorized."
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "Resource not found"
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

---

## 📝 참고사항

1. **날짜 형식**: 모든 날짜는 ISO 8601 형식 (UTC)을 사용합니다.
2. **인증**: Bearer Token을 Authorization 헤더에 포함해야 합니다.
3. **페이지네이션**: 현재는 페이지네이션이 구현되지 않았습니다.
4. **필터링**: 대부분의 목록 API는 다양한 필터 옵션을 지원합니다.
5. **에러 처리**: 모든 API는 일관된 에러 응답 형식을 사용합니다.
6. **토큰 만료**: 인증 토큰은 24시간 후 만료됩니다.
7. **AI 서버**: AI 관련 API는 별도의 AI 서버와 통신합니다.
