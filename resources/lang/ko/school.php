<?php

return [

    'nav' => [
        'role_teacher' => '음악 학원',
        'teachers' => '강사',
        'view_as_student' => '공개 프로필 보기',
    ],

    'dashboard' => [
        'title' => '학원 패널',
        'subtitle' => '학원, 강사, 학생을 한곳에서 관리하세요.',
        'stat_pending_students' => '승인 대기',
        'stat_new_students_month' => '이번 달 신규',
        'teacher_stats' => '강사 통계',
        'stat_active_teachers' => '활동 중인 강사',
        'stat_pending_teachers' => '승인 대기',
        'stat_member_students' => '강사의 학생',
        'stat_member_classes' => '강사의 클래스',
        'stat_member_assignments' => '강사의 과제',
        'stat_member_avg_score' => '평균 점수',
    ],

    'profile' => [
        'title' => '학원 프로필',
        'subtitle' => '학원의 공개 프로필을 관리하세요.',
    ],

    'public' => [
        'school_badge' => '음악 학원',
        'message_teacher' => '학원에 메시지 보내기',
    ],

    'admin' => [
        'entity_school' => '음악 학원',
    ],

    'teachers' => [
        'title' => '강사',
        'subtitle' => '학원에 강사를 추가하고 학생들과 함께 관리하세요.',
        'add_teacher' => '강사 추가',
        'no_teachers' => '아직 강사가 없습니다. 첫 강사를 초대해 보세요.',
        'active_since' => ':date부터 소속',
        'student_count' => '학생 :count명',
        'pending_approval' => '강사 승인 대기 중',
        'view_profile' => '보기',
        'remove_teacher' => '강사 제외',
        'remove_confirm' => '이 강사를 학원에서 제외할까요? 강사의 계정과 학생은 그대로 유지됩니다.',
        'back_to_list' => '전체 강사',
        'public_profile' => '공개 프로필',
        'stat_students' => '활동 중인 학생',
        'stat_classes' => '클래스',
        'stat_assignments' => '과제',
        'their_students' => '이 강사의 학생',
        'no_students' => '이 강사에게는 아직 활동 중인 학생이 없습니다.',
        'view_student' => '보기',
        'pending_invitations' => '대기 중인 초대',
        'no_invitations' => '대기 중인 초대가 없습니다.',
        'search_users' => '사용자 검색',
        'invite_by_email' => '이메일로 초대',
        'share_link' => '링크 공유',
        'search_placeholder' => '이름, 성 또는 정확한 이메일',
        'send_request' => '요청 보내기',
        'invite_name' => '이름 (선택)',
        'invite_email' => '이메일 주소',
        'send_invitation' => '초대 보내기',
        'link_expires' => '만료일',
        'create_link' => '링크 만들기',
        'copy_link' => '링크 복사',
        'revoke' => '철회',
        'status_relationship-requested' => '소속 요청을 보냈습니다.',
        'status_invitation-sent' => '초대 이메일을 보냈습니다.',
        'status_invitation-link-created' => '초대 링크를 만들었습니다.',
        'status_invitation-revoked' => '초대를 철회했습니다.',
        'status_relationship-revoked' => '강사를 학원에서 제외했습니다.',
        'error_self' => '자신의 계정을 강사로 추가할 수 없습니다.',
        'error_target_school' => '학원 계정은 강사로 추가할 수 없습니다.',
        'error_already_related' => '이 사용자는 이미 소속되어 있거나 대기 중인 요청이 있습니다.',
        'error_duplicate_invitation' => '이 이메일로 대기 중인 초대가 이미 있습니다.',
        'error_limit_reached' => '현재 플랜에서는 최대 :limit명의 강사만 등록할 수 있습니다. 더 추가하려면 업그레이드하세요.',
    ],

    'my_schools' => [
        'title' => '내 학원',
        'subtitle' => '내가 가르치는 음악 학원과 대기 중인 소속 요청.',
        'no_schools' => '아직 소속된 학원이 없습니다.',
        'pending' => '소속 요청 — 승인 대기 중',
        'since' => ':date부터 소속',
        'view_public_profile' => '학원 프로필 보기',
        'approve' => '수락',
        'decline' => '거절',
        'leave' => '학원 탈퇴',
        'leave_confirm' => '이 학원에서 탈퇴할까요? 내 계정과 학생은 그대로 유지됩니다.',
        'status_school-approved' => '학원에 합류했습니다.',
        'status_school-declined' => '요청을 거절했습니다.',
        'status_school-left' => '학원에서 탈퇴했습니다.',
        'status_school-joined' => '학원에 합류했습니다. 강사 패널이 준비되었습니다.',
    ],

    'invitations' => [
        'title' => '학원 초대',
        'invited_you' => ':school에서 강사로 함께하자고 초대했습니다.',
        'accept' => '초대 수락',
        'decline_hint' => '모르는 학원이라면 이 초대를 무시해도 됩니다.',
        'unusable' => '이 초대는 더 이상 유효하지 않습니다.',
    ],

];
