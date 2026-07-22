<?php

return [

    'nav' => [
        'role_teacher' => 'مدرسة موسيقى',
        'teachers' => 'المعلمون',
        'view_as_student' => 'عرض الملف العام',
    ],

    'dashboard' => [
        'title' => 'لوحة المدرسة',
        'subtitle' => 'أدر مدرستك ومعلميك وطلابك من مكان واحد.',
        'stat_pending_students' => 'بانتظار الموافقة',
        'stat_new_students_month' => 'جدد هذا الشهر',
        'teacher_stats' => 'إحصائيات المعلمين',
        'stat_active_teachers' => 'المعلمون النشطون',
        'stat_pending_teachers' => 'بانتظار الموافقة',
        'stat_member_students' => 'طلابهم',
        'stat_member_classes' => 'فصولهم',
        'stat_member_assignments' => 'واجباتهم',
        'stat_member_avg_score' => 'متوسط الدرجات',
    ],

    'profile' => [
        'title' => 'ملف المدرسة',
        'subtitle' => 'أدر الملف العام لمدرستك.',
    ],

    'public' => [
        'school_badge' => 'مدرسة موسيقى',
        'message_teacher' => 'مراسلة المدرسة',
    ],

    'admin' => [
        'entity_school' => 'مدرسة موسيقى',
    ],

    'teachers' => [
        'title' => 'المعلمون',
        'subtitle' => 'أضف معلمين إلى مدرستك وأدرهم مع طلابهم.',
        'add_teacher' => 'إضافة معلم',
        'no_teachers' => 'لا يوجد معلمون بعد. ادعُ أول معلم للبدء.',
        'active_since' => 'عضو منذ :date',
        'student_count' => ':count طالبًا',
        'pending_approval' => 'بانتظار موافقة المعلم',
        'view_profile' => 'عرض',
        'remove_teacher' => 'إزالة المعلم',
        'remove_confirm' => 'هل تريد إزالة هذا المعلم من مدرستك؟ سيبقى حسابه وطلابه دون تغيير.',
        'back_to_list' => 'كل المعلمين',
        'public_profile' => 'الملف العام',
        'stat_students' => 'الطلاب النشطون',
        'stat_classes' => 'الفصول',
        'stat_assignments' => 'الواجبات',
        'their_students' => 'طلاب هذا المعلم',
        'no_students' => 'ليس لدى هذا المعلم طلاب نشطون بعد.',
        'view_student' => 'عرض',
        'pending_invitations' => 'الدعوات المعلقة',
        'no_invitations' => 'لا توجد دعوات معلقة.',
        'search_users' => 'بحث عن مستخدمين',
        'invite_by_email' => 'دعوة عبر البريد',
        'share_link' => 'مشاركة رابط',
        'search_placeholder' => 'الاسم أو اللقب أو البريد الإلكتروني الكامل',
        'send_request' => 'إرسال الطلب',
        'invite_name' => 'الاسم (اختياري)',
        'invite_email' => 'البريد الإلكتروني',
        'send_invitation' => 'إرسال الدعوة',
        'link_expires' => 'تنتهي في',
        'create_link' => 'إنشاء رابط',
        'copy_link' => 'نسخ الرابط',
        'revoke' => 'إلغاء',
        'status_relationship-requested' => 'تم إرسال طلب العضوية.',
        'status_invitation-sent' => 'تم إرسال بريد الدعوة.',
        'status_invitation-link-created' => 'تم إنشاء رابط الدعوة.',
        'status_invitation-revoked' => 'تم إلغاء الدعوة.',
        'status_relationship-revoked' => 'تمت إزالة المعلم من مدرستك.',
        'error_self' => 'لا يمكنك إضافة حسابك الخاص كمعلم.',
        'error_target_school' => 'لا يمكن إضافة حسابات المدارس كمعلمين.',
        'error_already_related' => 'هذا المستخدم عضو بالفعل أو لديه طلب معلق.',
        'error_duplicate_invitation' => 'توجد دعوة معلقة بالفعل لهذا البريد.',
        'error_limit_reached' => 'خطتك تسمح بحد أقصى :limit معلمًا. قم بالترقية لإضافة المزيد.',
    ],

    'my_schools' => [
        'title' => 'مدارسي',
        'subtitle' => 'مدارس الموسيقى التي تدرّس فيها وطلبات العضوية المعلقة.',
        'no_schools' => 'لست عضوًا في أي مدرسة بعد.',
        'pending' => 'طلب عضوية — بانتظار موافقتك',
        'since' => 'عضو منذ :date',
        'view_public_profile' => 'عرض ملف المدرسة',
        'approve' => 'قبول',
        'decline' => 'رفض',
        'leave' => 'مغادرة المدرسة',
        'leave_confirm' => 'هل تريد مغادرة هذه المدرسة؟ ستحتفظ بحسابك وطلابك.',
        'status_school-approved' => 'انضممت إلى المدرسة.',
        'status_school-declined' => 'تم رفض الطلب.',
        'status_school-left' => 'غادرت المدرسة.',
        'status_school-joined' => 'انضممت إلى المدرسة. لوحة المعلم الخاصة بك جاهزة.',
    ],

    'invitations' => [
        'title' => 'دعوة مدرسة',
        'invited_you' => 'دعتك :school للانضمام إلى مدرستها الموسيقية كمعلم.',
        'accept' => 'قبول الدعوة',
        'decline_hint' => 'إذا كنت لا تعرف هذه المدرسة، يمكنك ببساطة تجاهل هذه الدعوة.',
        'unusable' => 'هذه الدعوة لم تعد صالحة.',
    ],

];
