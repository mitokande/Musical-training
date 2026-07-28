<?php

/**
 * Transactional notification/mailable copy (appointments, email verification,
 * invitations). Rendered in the recipient's locale (User::preferredLocale()).
 * :placeholders are Laravel replacements — keep them intact in translations.
 */
return [

    'appointment' => [
        'status_subject' => 'Appointment update — :when',
        'status' => [
            'confirmed' => 'Your lesson on :when is confirmed.',
            'rejected' => 'The appointment request for :when was declined.',
            'cancelled_teacher' => 'The lesson on :when was cancelled by the teacher.',
            'cancelled_student' => 'The lesson on :when was cancelled by the student.',
            'reschedule' => 'A new time was requested for the lesson on :when.',
            'completed' => 'The lesson on :when was marked as completed.',
            'no_show' => 'The lesson on :when was marked as a no-show.',
            'default' => 'Your appointment status changed.',
        ],
        'lesson_link' => 'Lesson link: :url',
        'view' => 'View appointment',
        'request_subject' => 'New appointment request from :name',
        'request_line' => ':name requested a lesson on :when.',
        'topic' => 'Topic: :topic',
        'review' => 'Review the request',
    ],

    'verify' => [
        'subject' => 'Verify your email address',
        'line1' => 'Please confirm your email address to activate your :app account.',
        'action' => 'Verify email address',
        'line2' => 'If you did not create an account, no further action is required.',
    ],

    'invite' => [
        'teacher_subject' => ':name invited you to Harmoniva',
        'school_subject' => ':name invited you to join their school on Harmoniva',
        'heading' => "You're invited to Harmoniva 🎵",
        'teacher_intro' => '**:name** invited you to join Harmoniva as their student.',
        'school_intro' => '**:name** invited you to join their music school on Harmoniva as a teacher.',
        'teacher_body' => 'Harmoniva is a music education platform with ear training, music theory practice, and guided learning paths. Once connected, your teacher can assign you homework and follow your progress.',
        'school_body' => 'Harmoniva is a music education platform with ear training, music theory practice, and guided learning paths. As a member teacher you get the full teacher toolset — students, classes, assignments, messaging and a booking calendar — and your school can support you in managing your students.',
        'accept' => 'Accept the invitation',
        'expires' => 'This invitation expires on :date.',
        'ignore' => 'If you were not expecting this invitation, you can safely ignore this email.',
        'thanks' => 'Thanks,',
    ],

];
