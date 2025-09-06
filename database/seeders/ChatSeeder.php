<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use App\Models\User;
use Carbon\Carbon;

class ChatSeeder extends Seeder
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

        $chatRooms = [];
        $chatMessages = [];

        // 각 사용자별로 채팅방과 메시지 생성
        foreach ($users as $user) {
            // 사용자별 채팅방 생성
            $chatRoom = ChatRoom::create([
                'user_id' => $user->id,
                'title' => $user->name . '님의 AI 어시스턴트',
            ]);

            $chatRooms[] = $chatRoom;

            // 각 채팅방에 대화 메시지들 생성
            $conversations = $this->getUserConversations($user->email);
            
            foreach ($conversations as $conversation) {
                // 사용자 메시지
                $userMessage = ChatMessage::create([
                    'chat_room_id' => $chatRoom->id,
                    'user_id' => $user->id,
                    'sended_type' => 'user',
                    'content' => $conversation['user_message'],
                    'metadata' => [
                        'timestamp' => $conversation['timestamp'],
                        'message_type' => 'text',
                    ],
                ]);

                // AI 응답 메시지
                $aiMessage = ChatMessage::create([
                    'chat_room_id' => $chatRoom->id,
                    'user_id' => $user->id,
                    'sended_type' => 'ai',
                    'content' => $conversation['ai_response'],
                    'metadata' => [
                        'timestamp' => $conversation['timestamp']->copy()->addSeconds(rand(1, 10)),
                        'message_type' => 'text',
                        'ai_agent' => $conversation['ai_agent'] ?? 'SupervisorAgent',
                        'confidence' => rand(80, 95),
                    ],
                ]);

                $chatMessages[] = $userMessage;
                $chatMessages[] = $aiMessage;
            }
        }
    }

    private function getUserConversations($userEmail)
    {
        $conversations = [
            'kim@plandy.kr' => [
                [
                    'user_message' => '오늘 일정을 확인해줘',
                    'ai_response' => '안녕하세요 김철수님! 오늘 일정을 확인해드리겠습니다.\n\n오늘 예정된 일정:\n- 09:00-12:00: 프로젝트 기획서 작성\n- 12:00-13:00: 점심시간 (AI 추천 휴식)\n- 15:30-15:45: 휴식시간\n- 19:00-20:00: 운동하기\n- 21:00-22:00: 독서하기\n\n현재 워라벨 점수는 75점으로 양호한 상태입니다. 오늘도 화이팅하세요!',
                    'timestamp' => Carbon::now()->subDays(1)->setTime(8, 30),
                    'ai_agent' => 'SupervisorAgent',
                ],
                [
                    'user_message' => '운동 시간을 늘리고 싶어',
                    'ai_response' => '운동 시간을 늘리는 것은 좋은 생각입니다! 현재 1시간 운동을 하고 계시는데, 다음과 같이 조정해드릴까요?\n\n추천 방안:\n1. 운동 시간을 1.5시간으로 연장\n2. 주 3회에서 4회로 증가\n3. 유산소와 근력운동을 균형있게 배치\n\n어떤 방안이 마음에 드시나요?',
                    'timestamp' => Carbon::now()->subDays(2)->setTime(20, 15),
                    'ai_agent' => 'HealthAgent',
                ],
                [
                    'user_message' => '내일 일정을 조정해줘',
                    'ai_response' => '내일 일정을 조정해드리겠습니다. 현재 예정된 일정을 확인하고 최적화된 시간표를 제안해드릴게요.\n\n조정된 일정:\n- 08:30-11:30: 프로젝트 기획서 작성 (30분 앞당김)\n- 11:30-12:30: 점심시간\n- 14:00-16:00: 추가 작업 시간 확보\n- 18:00-19:00: 운동시간 (1시간 앞당김)\n- 20:30-21:30: 독서시간\n\n이렇게 조정하면 더 여유로운 하루가 될 것 같습니다!',
                    'timestamp' => Carbon::now()->subDays(3)->setTime(21, 45),
                    'ai_agent' => 'PlanAgent',
                ],
            ],
            'lee@plandy.kr' => [
                [
                    'user_message' => '워라벨 점수가 어떻게 되나요?',
                    'ai_response' => '이영희님의 현재 워라벨 점수는 85점입니다! 매우 좋은 상태네요.\n\n주요 지표:\n- 업무시간: 42시간/주 (적절)\n- 개인시간: 28시간/주 (충분)\n- 건강점수: 88점\n- 스트레스 수준: 2/6 (낮음)\n- 만족도: 87점\n\n특히 요가와 명상 습관이 워라벨 향상에 큰 도움이 되고 있습니다. 계속 유지해주세요!',
                    'timestamp' => Carbon::now()->subDays(1)->setTime(9, 0),
                    'ai_agent' => 'WorkLifeBalanceAgent',
                ],
                [
                    'user_message' => '코드 리뷰 시간이 부족해',
                    'ai_response' => '코드 리뷰 시간이 부족하다고 하셨네요. 현재 2시간으로 설정되어 있는데, 다음과 같이 조정해드릴까요?\n\n제안사항:\n1. 코드 리뷰 시간을 3시간으로 연장\n2. 오전 시간대에 집중 리뷰 시간 배정\n3. 팀원들과 리뷰 시간 조율\n\n어떤 방안이 좋으실까요?',
                    'timestamp' => Carbon::now()->subDays(2)->setTime(11, 30),
                    'ai_agent' => 'PlanAgent',
                ],
            ],
            'park@plandy.kr' => [
                [
                    'user_message' => '가족 시간을 더 늘리고 싶어',
                    'ai_response' => '가족 시간을 늘리고 싶으시군요! 현재 주 1회 가족 저녁식사가 예정되어 있는데, 다음과 같이 확장해보는 것은 어떨까요?\n\n추천 방안:\n1. 주 2회 가족 시간 확보 (화요일, 일요일)\n2. 주말 오후 가족 활동 시간 추가\n3. 평일 저녁 30분 가족 대화 시간\n\n가족과의 소중한 시간을 더 많이 가질 수 있도록 도와드리겠습니다!',
                    'timestamp' => Carbon::now()->subDays(1)->setTime(19, 30),
                    'ai_agent' => 'WorkLifeBalanceAgent',
                ],
                [
                    'user_message' => '데이터베이스 설계가 어려워',
                    'ai_response' => '데이터베이스 설계가 어렵다고 하셨네요. 도움을 드리겠습니다!\n\n설계 과정:\n1. 요구사항 분석 및 엔티티 식별\n2. 관계 설정 및 정규화\n3. 인덱스 및 성능 최적화\n4. 보안 및 백업 전략\n\n어떤 부분에서 어려움을 겪고 계신가요? 구체적으로 말씀해주시면 더 정확한 도움을 드릴 수 있습니다.',
                    'timestamp' => Carbon::now()->subDays(2)->setTime(15, 0),
                    'ai_agent' => 'DataAgent',
                ],
            ],
            'choi@plandy.kr' => [
                [
                    'user_message' => '마케팅 회의 준비를 도와줘',
                    'ai_response' => '마케팅 회의 준비를 도와드리겠습니다! Q2 마케팅 전략 회의를 위한 체크리스트를 만들어드릴게요.\n\n준비사항:\n1. Q1 성과 분석 자료\n2. 경쟁사 분석 보고서\n3. Q2 목표 및 KPI 설정\n4. 예산 계획서\n5. 채널별 전략안\n\n각 항목별로 상세한 자료를 준비해드릴까요?',
                    'timestamp' => Carbon::now()->subDays(1)->setTime(10, 0),
                    'ai_agent' => 'PlanAgent',
                ],
                [
                    'user_message' => '영어 실력 향상 팁을 알려줘',
                    'ai_response' => '영어 실력 향상을 위한 팁을 알려드리겠습니다!\n\n효과적인 학습 방법:\n1. 매일 30분 영어 회화 연습 (현재 진행 중)\n2. 영어 뉴스나 팟캐스트 청취\n3. 영어 일기 쓰기\n4. 언어 교환 파트너 찾기\n5. 영어로 업무 관련 자료 읽기\n\n현재 매일 30분씩 연습하고 계시니, 꾸준히 하시면 분명 향상될 거예요!',
                    'timestamp' => Carbon::now()->subDays(2)->setTime(20, 30),
                    'ai_agent' => 'HealthAgent',
                ],
            ],
            'jung@plandy.kr' => [
                [
                    'user_message' => '시스템 모니터링 알림을 설정해줘',
                    'ai_response' => '시스템 모니터링 알림을 설정해드리겠습니다!\n\n설정된 알림:\n1. CPU 사용률 80% 이상 시 알림\n2. 메모리 사용률 85% 이상 시 알림\n3. 디스크 공간 90% 이상 시 알림\n4. 서비스 응답시간 3초 이상 시 알림\n5. 일일 시스템 상태 리포트 (오전 9시)\n\n추가로 설정하고 싶은 모니터링 항목이 있으시면 말씀해주세요!',
                    'timestamp' => Carbon::now()->subDays(1)->setTime(9, 15),
                    'ai_agent' => 'DataAgent',
                ],
                [
                    'user_message' => '사진 촬영 장소를 추천해줘',
                    'ai_response' => '사진 촬영 장소를 추천해드리겠습니다! 현재 계절과 날씨를 고려한 추천 장소입니다.\n\n추천 장소:\n1. 한강공원 (일몰 촬영 최적)\n2. 북촌한옥마을 (전통적인 분위기)\n3. 이태원 (다양한 문화적 요소)\n4. 경복궁 (역사적 배경)\n5. 남산타워 (서울 전경)\n\n어떤 스타일의 사진을 찍고 싶으신가요?',
                    'timestamp' => Carbon::now()->subDays(2)->setTime(16, 0),
                    'ai_agent' => 'HealthAgent',
                ],
            ],
        ];

        return $conversations[$userEmail] ?? [];
    }
}