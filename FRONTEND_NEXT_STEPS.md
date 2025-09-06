# 프론트엔드 개발 다음 단계 가이드

## 🎯 Streamlit 프론트엔드 개발 로드맵

### 1단계: 기본 인증 시스템 구현 (우선순위: 높음)

#### 1.1 로그인/회원가입 페이지
```python
# 필요한 기능
- 로그인 폼 (이메일, 비밀번호)
- 회원가입 폼 (이메일, 비밀번호, 이름)
- 세션 관리 (Streamlit session_state 활용)
- 토큰 저장 및 관리
```

#### 1.2 API 클라이언트 구현
```python
# API 클라이언트 클래스 예시
class PlandyAPIClient:
    def __init__(self, base_url="http://127.0.0.1:8000/api"):
        self.base_url = base_url
        self.token = None
    
    def login(self, email, password):
        # 로그인 API 호출
        pass
    
    def register(self, email, password, name):
        # 회원가입 API 호출
        pass
```

### 2단계: 대시보드 구현 (우선순위: 높음)

#### 2.1 메인 대시보드
```python
# 대시보드 구성 요소
- 오늘의 태스크 목록
- 오늘의 스케줄
- 워라밸 점수 표시
- 습관 체크리스트
- AI 추천 사항
```

#### 2.2 네비게이션 시스템
```python
# 사이드바 메뉴 구성
- 대시보드
- 태스크 관리
- 스케줄 관리
- 워라밸 분석
- AI 어시스턴트
- 설정
```

### 3단계: 태스크 관리 시스템 (우선순위: 높음)

#### 3.1 태스크 CRUD 기능
```python
# 태스크 관리 기능
- 태스크 목록 조회 (필터링, 정렬)
- 새 태스크 생성
- 태스크 수정/삭제
- 태스크 상태 변경 (완료, 진행중, 대기)
- 우선순위 설정
- 마감일 설정
```

#### 3.2 태스크 UI 컴포넌트
```python
# UI 컴포넌트
- 태스크 카드
- 태스크 필터 (상태, 우선순위, 날짜)
- 드래그 앤 드롭 (선택사항)
- 진행률 표시
```

### 4단계: 스케줄 관리 시스템 (우선순위: 중간)

#### 4.1 캘린더 뷰
```python
# 캘린더 기능
- 월간/주간/일간 뷰
- 스케줄 블록 표시
- 스케줄 생성/수정/삭제
- 시간 충돌 검사
- AI 스케줄 추천
```

#### 4.2 스케줄 UI 컴포넌트
```python
# UI 컴포넌트
- 캘린더 위젯
- 스케줄 모달
- 시간 선택기
- 반복 스케줄 설정
```

### 5단계: 워라밸 분석 시스템 (우선순위: 중간)

#### 5.1 습관 추적
```python
# 습관 관리 기능
- 습관 목록 관리
- 일일 습관 체크
- 습관 통계 (연속일, 달성률)
- 습관 카테고리별 분류
```

#### 5.2 워라밸 점수 시각화
```python
# 시각화 컴포넌트
- 워라밸 점수 차트
- 주간/월간 트렌드
- 카테고리별 점수 분석
- 목표 설정 및 추적
```

### 6단계: AI 어시스턴트 (우선순위: 중간)

#### 6.1 AI 채팅 인터페이스
```python
# AI 채팅 기능
- 실시간 채팅 UI
- 메시지 히스토리
- AI 응답 스트리밍
- 컨텍스트 유지
```

#### 6.2 AI 추천 시스템
```python
# AI 추천 기능
- 태스크 우선순위 추천
- 스케줄 최적화 제안
- 워라밸 개선 방안
- 개인화된 팁 제공
```

### 7단계: 고급 기능 (우선순위: 낮음)

#### 7.1 데이터 시각화
```python
# 고급 시각화
- 생산성 트렌드 분석
- 시간 사용 패턴 분석
- 목표 달성률 대시보드
- 커스텀 리포트 생성
```

#### 7.2 설정 및 개인화
```python
# 설정 기능
- 프로필 관리
- 알림 설정
- 테마 설정
- 데이터 내보내기/가져오기
```

## 🛠️ 기술 구현 가이드

### Streamlit 세션 관리
```python
import streamlit as st

# 세션 상태 초기화
if 'user_token' not in st.session_state:
    st.session_state.user_token = None
if 'user_info' not in st.session_state:
    st.session_state.user_info = None

# 로그인 상태 확인
def is_logged_in():
    return st.session_state.user_token is not None
```

### API 호출 패턴
```python
import requests
import streamlit as st

def api_call(method, endpoint, data=None, headers=None):
    base_url = "http://127.0.0.1:8000/api"
    url = f"{base_url}{endpoint}"
    
    if headers is None:
        headers = {}
    
    if st.session_state.user_token:
        headers['Authorization'] = f"Bearer {st.session_state.user_token}"
    
    try:
        if method == 'GET':
            response = requests.get(url, headers=headers)
        elif method == 'POST':
            response = requests.post(url, json=data, headers=headers)
        # ... 다른 HTTP 메서드들
        
        return response.json() if response.status_code == 200 else None
    except Exception as e:
        st.error(f"API 호출 오류: {e}")
        return None
```

### 에러 처리
```python
def handle_api_error(response):
    if response.status_code == 401:
        st.error("로그인이 필요합니다.")
        st.session_state.user_token = None
        st.rerun()
    elif response.status_code == 422:
        errors = response.json().get('errors', {})
        for field, messages in errors.items():
            st.error(f"{field}: {', '.join(messages)}")
    else:
        st.error(f"오류가 발생했습니다: {response.status_code}")
```

## 📦 필요한 Python 패키지

```txt
streamlit>=1.28.0
requests>=2.31.0
pandas>=2.0.0
plotly>=5.15.0
datetime
json
```

## 🚀 개발 시작하기

### 1. 프로젝트 구조 생성
```
plandy-frontend/
├── app.py                 # 메인 애플리케이션
├── pages/                 # 페이지 모듈들
│   ├── dashboard.py
│   ├── tasks.py
│   ├── schedule.py
│   ├── worklife.py
│   └── ai_assistant.py
├── components/            # 재사용 가능한 컴포넌트
│   ├── api_client.py
│   ├── auth.py
│   └── charts.py
├── utils/                 # 유틸리티 함수들
│   ├── helpers.py
│   └── constants.py
└── requirements.txt
```

### 2. 첫 번째 페이지 구현
```python
# app.py
import streamlit as st
from pages import dashboard, tasks, schedule, worklife, ai_assistant

st.set_page_config(
    page_title="Plandy",
    page_icon="📅",
    layout="wide"
)

# 사이드바 네비게이션
page = st.sidebar.selectbox(
    "페이지 선택",
    ["대시보드", "태스크", "스케줄", "워라밸", "AI 어시스턴트"]
)

if page == "대시보드":
    dashboard.show()
elif page == "태스크":
    tasks.show()
# ... 다른 페이지들
```

## 🔗 백엔드 연동 체크리스트

- [ ] API 클라이언트 클래스 구현
- [ ] 인증 토큰 관리 시스템
- [ ] 에러 처리 및 사용자 피드백
- [ ] 로딩 상태 표시
- [ ] 데이터 캐싱 전략
- [ ] 오프라인 모드 지원 (선택사항)

## 📊 성능 최적화 팁

1. **API 호출 최적화**
   - 불필요한 API 호출 방지
   - 데이터 캐싱 활용
   - 배치 요청 사용

2. **UI/UX 개선**
   - 로딩 스피너 표시
   - 에러 메시지 개선
   - 반응형 디자인

3. **데이터 관리**
   - 세션 상태 효율적 관리
   - 메모리 사용량 최적화
   - 대용량 데이터 처리

## 🎨 UI/UX 가이드라인

### 색상 팔레트
```python
# Plandy 브랜드 컬러
PRIMARY_COLOR = "#FF2D20"  # Laravel Red
SECONDARY_COLOR = "#1F2937"  # Dark Gray
SUCCESS_COLOR = "#10B981"   # Green
WARNING_COLOR = "#F59E0B"   # Yellow
ERROR_COLOR = "#EF4444"     # Red
```

### 컴포넌트 스타일링
```python
# Streamlit CSS 커스터마이징
st.markdown("""
<style>
    .main-header {
        font-size: 2.5rem;
        font-weight: bold;
        color: #FF2D20;
        text-align: center;
        margin-bottom: 2rem;
    }
    .task-card {
        border: 1px solid #E5E7EB;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1rem;
        background-color: #F9FAFB;
    }
</style>
""", unsafe_allow_html=True)
```

이제 백엔드가 완전히 준비되었으므로, 위의 가이드를 따라 Streamlit 프론트엔드를 단계별로 개발하실 수 있습니다! 🚀
