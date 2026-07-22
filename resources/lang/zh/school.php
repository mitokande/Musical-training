<?php

return [

    'nav' => [
        'role_teacher' => '音乐学校',
        'teachers' => '教师',
        'view_as_student' => '查看公开主页',
    ],

    'dashboard' => [
        'title' => '学校面板',
        'subtitle' => '在一个地方管理您的学校、教师和学生。',
        'stat_pending_students' => '待确认',
        'stat_new_students_month' => '本月新增',
        'teacher_stats' => '教师统计',
        'stat_active_teachers' => '活跃教师',
        'stat_pending_teachers' => '待确认',
        'stat_member_students' => '教师的学生',
        'stat_member_classes' => '教师的班级',
        'stat_member_assignments' => '教师的作业',
        'stat_member_avg_score' => '平均分',
    ],

    'profile' => [
        'title' => '学校主页',
        'subtitle' => '管理学校的公开主页。',
    ],

    'public' => [
        'school_badge' => '音乐学校',
        'message_teacher' => '给学校发消息',
    ],

    'admin' => [
        'entity_school' => '音乐学校',
    ],

    'teachers' => [
        'title' => '教师',
        'subtitle' => '将教师加入您的学校，并与他们的学生一起管理。',
        'add_teacher' => '添加教师',
        'no_teachers' => '还没有教师。邀请第一位教师开始吧。',
        'active_since' => '自 :date 起加入',
        'student_count' => ':count 名学生',
        'pending_approval' => '等待教师确认',
        'view_profile' => '查看',
        'remove_teacher' => '移除教师',
        'remove_confirm' => '将这位教师从学校移除？其账号和学生不受影响。',
        'back_to_list' => '全部教师',
        'public_profile' => '公开主页',
        'stat_students' => '活跃学生',
        'stat_classes' => '班级',
        'stat_assignments' => '作业',
        'their_students' => '这位教师的学生',
        'no_students' => '这位教师还没有活跃学生。',
        'view_student' => '查看',
        'pending_invitations' => '待处理邀请',
        'no_invitations' => '没有待处理的邀请。',
        'search_users' => '搜索用户',
        'invite_by_email' => '通过邮件邀请',
        'share_link' => '分享链接',
        'search_placeholder' => '姓名或完整邮箱',
        'send_request' => '发送请求',
        'invite_name' => '姓名（可选）',
        'invite_email' => '邮箱地址',
        'send_invitation' => '发送邀请',
        'link_expires' => '有效期至',
        'create_link' => '创建链接',
        'copy_link' => '复制链接',
        'revoke' => '撤销',
        'status_relationship-requested' => '已发送加入请求。',
        'status_invitation-sent' => '邀请邮件已发送。',
        'status_invitation-link-created' => '邀请链接已创建。',
        'status_invitation-revoked' => '邀请已撤销。',
        'status_relationship-revoked' => '教师已从您的学校移除。',
        'error_self' => '不能将自己的账号添加为教师。',
        'error_target_school' => '学校账号不能被添加为教师。',
        'error_already_related' => '该用户已是成员或有待处理的请求。',
        'error_duplicate_invitation' => '该邮箱已有待处理的邀请。',
        'error_limit_reached' => '您的套餐最多支持 :limit 名教师。升级后可添加更多。',
    ],

    'my_schools' => [
        'title' => '我的学校',
        'subtitle' => '您任教的音乐学校及待处理的加入请求。',
        'no_schools' => '您还不是任何学校的成员。',
        'pending' => '加入请求 — 等待您的确认',
        'since' => '自 :date 起加入',
        'view_public_profile' => '查看学校主页',
        'approve' => '接受',
        'decline' => '拒绝',
        'leave' => '离开学校',
        'leave_confirm' => '确定离开这所学校？您的账号和学生将保留。',
        'status_school-approved' => '您已加入该学校。',
        'status_school-declined' => '已拒绝请求。',
        'status_school-left' => '您已离开该学校。',
        'status_school-joined' => '您已加入该学校。教师面板已就绪。',
    ],

    'invitations' => [
        'title' => '学校邀请',
        'invited_you' => ':school 邀请您以教师身份加入他们的音乐学校。',
        'accept' => '接受邀请',
        'decline_hint' => '如果您不认识这所学校，可以直接忽略此邀请。',
        'unusable' => '此邀请已失效。',
    ],

];
