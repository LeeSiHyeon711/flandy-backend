# Plandy 프론트엔드 연동 가이드

## 🚀 백엔드 서버 실행

Laravel 백엔드 서버가 실행 중입니다:
- **URL**: http://localhost:8000
- **API Base URL**: http://localhost:8000/api

## 📡 API 엔드포인트 목록

### 인증 API
```
POST /api/auth/login          # 로그인
POST /api/auth/register       # 회원가입
POST /api/auth/logout         # 로그아웃 (인증 필요)
GET  /api/auth/me            # 현재 사용자 정보 (인증 필요)
PUT  /api/auth/profile       # 프로필 수정 (인증 필요)
PUT  /api/auth/password      # 비밀번호 변경 (인증 필요)
```

### 할 일 관리 API
```
GET    /api/tasks            # 할 일 목록 조회
POST   /api/tasks            # 할 일 생성
GET    /api/tasks/{id}       # 할 일 상세 조회
PUT    /api/tasks/{id}       # 할 일 수정
DELETE /api/tasks/{id}       # 할 일 삭제
```

### 일정 관리 API
```
GET    /api/schedule                    # 일정 목록 조회
GET    /api/schedule/date/{date}        # 특정 날짜 일정 조회
POST   /api/schedule                    # 일정 블록 생성
PUT    /api/schedule/{id}               # 일정 블록 수정
DELETE /api/schedule/{id}               # 일정 블록 삭제
```

### 워라벨 관리 API
```
GET  /api/worklife/scores                    # 워라벨 점수 목록
GET  /api/worklife/scores/week/{weekStart}   # 특정 주 워라벨 점수
POST /api/worklife/scores                    # 워라벨 점수 생성/업데이트
POST /api/worklife/scores/calculate          # 현재 주 점수 계산
GET  /api/worklife/habits                    # 습관 로그 목록
POST /api/worklife/habits                    # 습관 로그 생성
```

### AI 연동 API
```
POST /api/ai/chat                    # AI 채팅 메시지 전송
POST /api/ai/reschedule              # 일정 재조정 요청
POST /api/ai/analyze-worklife        # 워라벨 분석 요청
```

### 웹훅 API (AI 서버용)
```
POST /api/webhook/ai/chat-response      # AI 채팅 응답 수신
POST /api/webhook/ai/schedule-update    # AI 일정 업데이트 수신
POST /api/webhook/ai/worklife-analysis  # AI 워라벨 분석 결과 수신
```

### 헬스체크
```
GET /api/health                        # 서버 상태 확인
```

## 🔐 인증 방식

### 1. 로그인
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "kim@plandy.kr",
    "password": "password123"
  }'
```

### 2. 토큰 사용
모든 인증이 필요한 API 요청에는 Authorization 헤더에 Bearer 토큰을 포함해야 합니다:
```
Authorization: Bearer {your_token_here}
```

## 📊 테스트 데이터

시더를 통해 다음 테스트 사용자들이 생성되었습니다:

| 이메일 | 비밀번호 | 이름 | 역할 |
|--------|----------|------|------|
| kim@plandy.kr | password123 | 김철수 | 프로젝트 매니저 |
| lee@plandy.kr | password123 | 이영희 | 개발자 |
| park@plandy.kr | password123 | 박민수 | DB 설계자 |
| choi@plandy.kr | password123 | 최지영 | 마케터 |
| jung@plandy.kr | password123 | 정현우 | 시스템 관리자 |

## 🌐 CORS 설정

다음 도메인들이 허용되어 있습니다:
- http://localhost:8501 (Streamlit)
- http://localhost:3000 (React)
- http://localhost:8080 (Vue)
- http://127.0.0.1:8501
- http://127.0.0.1:3000
- http://127.0.0.1:8080

## 📱 Streamlit 연동 예시

### 1. API 클라이언트 클래스
```python
import requests
import streamlit as st

class PlandyAPI:
    def __init__(self, base_url="http://localhost:8000/api"):
        self.base_url = base_url
        self.token = None
    
    def login(self, email, password):
        response = requests.post(f"{self.base_url}/auth/login", json={
            "email": email,
            "password": password
        })
        if response.status_code == 200:
            data = response.json()
            self.token = data['data']['token']
            return True
        return False
    
    def get_tasks(self):
        headers = {"Authorization": f"Bearer {self.token}"}
        response = requests.get(f"{self.base_url}/tasks", headers=headers)
        return response.json() if response.status_code == 200 else None
    
    def get_schedule(self, date):
        headers = {"Authorization": f"Bearer {self.token}"}
        response = requests.get(f"{self.base_url}/schedule/date/{date}", headers=headers)
        return response.json() if response.status_code == 200 else None
```

### 2. Streamlit 앱 예시
```python
import streamlit as st
from plandy_api import PlandyAPI

st.title("Plandy - AI 일정 관리")

# 로그인
if 'token' not in st.session_state:
    email = st.text_input("이메일")
    password = st.text_input("비밀번호", type="password")
    
    if st.button("로그인"):
        api = PlandyAPI()
        if api.login(email, password):
            st.session_state.token = api.token
            st.session_state.api = api
            st.success("로그인 성공!")
        else:
            st.error("로그인 실패!")

# 메인 앱
if 'token' in st.session_state:
    api = st.session_state.api
    
    # 할 일 목록
    st.header("할 일 목록")
    tasks = api.get_tasks()
    if tasks:
        for task in tasks['data']:
            st.write(f"- {task['title']}")
    
    # 오늘 일정
    st.header("오늘 일정")
    from datetime import date
    today = date.today().isoformat()
    schedule = api.get_schedule(today)
    if schedule:
        for block in schedule['data']:
            st.write(f"- {block['starts_at']} ~ {block['ends_at']}")
```

## 🔧 환경 설정

### .env 파일 설정
```env
APP_NAME="Plandy"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=plandy
DB_USERNAME=root
DB_PASSWORD=your_password

# AI 서버 설정 (선택사항)
AI_SERVER_URL=http://localhost:8001
AI_API_KEY=your_ai_api_key
```

## 🚨 주의사항

1. **CORS**: 프론트엔드 도메인이 CORS 설정에 포함되어 있는지 확인하세요.
2. **토큰 관리**: 토큰을 안전하게 저장하고 관리하세요.
3. **에러 처리**: API 응답의 success 필드를 확인하여 에러를 처리하세요.
4. **날짜 형식**: 날짜는 YYYY-MM-DD 형식으로 전송하세요.

## 📞 지원

문제가 있거나 질문이 있으시면 이슈를 생성해주세요.
