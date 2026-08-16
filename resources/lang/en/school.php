<?php

/*
 * School-panel label overrides. The school panel shares the teacher CRM
 * blades via crm_trans(): any key present here wins inside /school/*,
 * everything else falls back to teacher.php. The teachers/my_schools/
 * invitations groups back the school-only membership module.
 */

return [

    'nav' => [
        'role_teacher' => 'Music School',
        'teachers' => 'Teachers',
        'view_as_student' => 'View public profile',
    ],

    'dashboard' => [
        'title' => 'School Panel',
        'subtitle' => 'Manage your school, teachers and students from one place.',
        'stat_pending_students' => 'Pending approvals',
        'stat_new_students_month' => 'New this month',
        'teacher_stats' => 'Teacher statistics',
        'stat_active_teachers' => 'Active teachers',
        'stat_pending_teachers' => 'Pending approvals',
        'stat_member_students' => 'Their students',
        'stat_member_classes' => 'Their classes',
        'stat_member_assignments' => 'Their assignments',
        'stat_member_avg_score' => 'Avg. score',
    ],

    'profile' => [
        // School wording for the public-profile meta description fallback;
        // teacher.profile.seo_description_location supplies the city/country half.
        'seo_description_fallback' => 'Discover :name on Harmoniva — music lessons, teachers and ear training for every level.',
        'title' => 'School Profile',
        'subtitle' => 'Manage your public school profile.',
    ],

    'public' => [
        'school_badge' => 'Music School',
        'message_teacher' => 'Message the school',
    ],

    'admin' => [
        'entity_school' => 'Music School',
    ],

    'teachers' => [
        'title' => 'Teachers',
        'subtitle' => 'Add teachers to your school and manage them together with their students.',
        'add_teacher' => 'Add teacher',
        'no_teachers' => 'No teachers yet. Invite your first teacher to get started.',
        'active_since' => 'Member since :date',
        'student_count' => ':count students',
        'pending_approval' => 'Waiting for the teacher\'s approval',
        'view_profile' => 'View',
        'remove_teacher' => 'Remove teacher',
        'remove_confirm' => 'Remove this teacher from your school? Their account and students stay intact.',
        'back_to_list' => 'All teachers',
        'public_profile' => 'Public profile',
        'stat_students' => 'Active students',
        'stat_classes' => 'Classes',
        'stat_assignments' => 'Assignments',
        'their_students' => 'Students of this teacher',
        'no_students' => 'This teacher has no active students yet.',
        'view_student' => 'View',
        'pending_invitations' => 'Pending invitations',
        'no_invitations' => 'No pending invitations.',
        'search_users' => 'Search users',
        'invite_by_email' => 'Invite by email',
        'share_link' => 'Share a link',
        'search_placeholder' => 'Name, surname or exact email',
        'send_request' => 'Send request',
        'invite_name' => 'Name (optional)',
        'invite_email' => 'Email address',
        'send_invitation' => 'Send invitation',
        'link_expires' => 'Expires',
        'create_link' => 'Create link',
        'copy_link' => 'Copy link',
        'revoke' => 'Revoke',
        'status_relationship-requested' => 'Membership request sent.',
        'status_invitation-sent' => 'Invitation email sent.',
        'status_invitation-link-created' => 'Invitation link created.',
        'status_invitation-revoked' => 'Invitation revoked.',
        'status_relationship-revoked' => 'Teacher removed from your school.',
        'error_self' => 'You cannot add your own account as a teacher.',
        'error_target_school' => 'School accounts cannot be added as teachers.',
        'error_already_related' => 'This user is already a member or has a pending request.',
        'error_duplicate_invitation' => 'A pending invitation for this email already exists.',
        'error_limit_reached' => 'Your plan allows up to :limit teachers. Upgrade to add more.',
    ],

    'my_schools' => [
        'title' => 'My Schools',
        'subtitle' => 'Music schools you teach at, and pending membership requests.',
        'no_schools' => 'You are not a member of any school yet.',
        'pending' => 'Membership request — waiting for your approval',
        'since' => 'Member since :date',
        'view_public_profile' => 'View school profile',
        'approve' => 'Accept',
        'decline' => 'Decline',
        'leave' => 'Leave school',
        'leave_confirm' => 'Leave this school? You will keep your own account and students.',
        'status_school-approved' => 'You joined the school.',
        'status_school-declined' => 'Request declined.',
        'status_school-left' => 'You left the school.',
        'status_school-joined' => 'You joined the school. Your teacher panel is ready.',
    ],

    'invitations' => [
        'title' => 'School invitation',
        'invited_you' => ':school invited you to join their music school as a teacher.',
        'accept' => 'Accept the invitation',
        'decline_hint' => 'If you do not know this school, you can simply ignore this invitation.',
        'unusable' => 'This invitation is no longer valid.',
    ],

];
