<?php

return [

    'nav' => [
        'role_teacher' => '音楽教室',
        'teachers' => '講師',
        'view_as_student' => '公開プロフィールを見る',
    ],

    'dashboard' => [
        'title' => 'スクールパネル',
        'subtitle' => '教室・講師・生徒をひとつの場所で管理できます。',
        'stat_pending_students' => '承認待ち',
        'stat_new_students_month' => '今月の新規',
        'teacher_stats' => '講師の統計',
        'stat_active_teachers' => '在籍中の講師',
        'stat_pending_teachers' => '承認待ち',
        'stat_member_students' => '講師の生徒',
        'stat_member_classes' => '講師のクラス',
        'stat_member_assignments' => '講師の課題',
        'stat_member_avg_score' => '平均スコア',
    ],

    'profile' => [
        'title' => '教室プロフィール',
        'subtitle' => '教室の公開プロフィールを管理します。',
    ],

    'public' => [
        'school_badge' => '音楽教室',
        'message_teacher' => '教室にメッセージを送る',
    ],

    'admin' => [
        'entity_school' => '音楽教室',
    ],

    'teachers' => [
        'title' => '講師',
        'subtitle' => '教室に講師を追加し、生徒と一緒に管理しましょう。',
        'add_teacher' => '講師を追加',
        'no_teachers' => 'まだ講師がいません。最初の講師を招待しましょう。',
        'active_since' => ':date から在籍',
        'student_count' => '生徒 :count 人',
        'pending_approval' => '講師の承認待ち',
        'view_profile' => '表示',
        'remove_teacher' => '講師を外す',
        'remove_confirm' => 'この講師を教室から外しますか？アカウントと生徒はそのまま残ります。',
        'back_to_list' => '講師一覧',
        'public_profile' => '公開プロフィール',
        'stat_students' => 'アクティブな生徒',
        'stat_classes' => 'クラス',
        'stat_assignments' => '課題',
        'their_students' => 'この講師の生徒',
        'no_students' => 'この講師にはまだアクティブな生徒がいません。',
        'view_student' => '表示',
        'pending_invitations' => '保留中の招待',
        'no_invitations' => '保留中の招待はありません。',
        'search_users' => 'ユーザー検索',
        'invite_by_email' => 'メールで招待',
        'share_link' => 'リンクを共有',
        'search_placeholder' => '名前・姓・正確なメールアドレス',
        'send_request' => 'リクエストを送る',
        'invite_name' => '名前（任意）',
        'invite_email' => 'メールアドレス',
        'send_invitation' => '招待を送る',
        'link_expires' => '有効期限',
        'create_link' => 'リンクを作成',
        'copy_link' => 'リンクをコピー',
        'revoke' => '取り消す',
        'status_relationship-requested' => '在籍リクエストを送信しました。',
        'status_invitation-sent' => '招待メールを送信しました。',
        'status_invitation-link-created' => '招待リンクを作成しました。',
        'status_invitation-revoked' => '招待を取り消しました。',
        'status_relationship-revoked' => '講師を教室から外しました。',
        'error_self' => '自分のアカウントを講師として追加することはできません。',
        'error_target_school' => 'スクールアカウントは講師として追加できません。',
        'error_already_related' => 'このユーザーはすでにメンバーであるか、保留中のリクエストがあります。',
        'error_duplicate_invitation' => 'このメールアドレスへの保留中の招待がすでにあります。',
        'error_limit_reached' => '現在のプランで登録できる講師は最大 :limit 人です。追加するにはアップグレードしてください。',
    ],

    'my_schools' => [
        'title' => '所属教室',
        'subtitle' => 'あなたが教えている音楽教室と、保留中の在籍リクエスト。',
        'no_schools' => 'まだどの教室にも所属していません。',
        'pending' => '在籍リクエスト — あなたの承認待ちです',
        'since' => ':date から在籍',
        'view_public_profile' => '教室のプロフィールを見る',
        'approve' => '承認',
        'decline' => '辞退',
        'leave' => '教室を退会',
        'leave_confirm' => 'この教室を退会しますか？自分のアカウントと生徒はそのまま残ります。',
        'status_school-approved' => '教室に参加しました。',
        'status_school-declined' => 'リクエストを辞退しました。',
        'status_school-left' => '教室を退会しました。',
        'status_school-joined' => '教室に参加しました。講師パネルの準備ができています。',
    ],

    'invitations' => [
        'title' => '教室からの招待',
        'invited_you' => ':school があなたを講師として音楽教室に招待しています。',
        'accept' => '招待を受ける',
        'decline_hint' => 'この教室に心当たりがない場合は、この招待を無視してかまいません。',
        'unusable' => 'この招待は無効になりました。',
    ],

];
