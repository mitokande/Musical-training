<?php

return [

    'nav' => [
        'role_teacher' => 'Musikschule',
        'teachers' => 'Lehrkräfte',
        'view_as_student' => 'Öffentliches Profil ansehen',
    ],

    'dashboard' => [
        'title' => 'Schulpanel',
        'subtitle' => 'Verwalten Sie Ihre Schule, Lehrkräfte und Schüler an einem Ort.',
        'stat_pending_students' => 'Ausstehende Zusagen',
        'stat_new_students_month' => 'Neu diesen Monat',
        'teacher_stats' => 'Lehrkräfte-Statistiken',
        'stat_active_teachers' => 'Aktive Lehrkräfte',
        'stat_pending_teachers' => 'Ausstehende Zusagen',
        'stat_member_students' => 'Ihre Schüler',
        'stat_member_classes' => 'Ihre Klassen',
        'stat_member_assignments' => 'Ihre Aufgaben',
        'stat_member_avg_score' => 'Ø Punktzahl',
    ],

    'profile' => [
        'title' => 'Schulprofil',
        'subtitle' => 'Verwalten Sie Ihr öffentliches Schulprofil.',
    ],

    'public' => [
        'school_badge' => 'Musikschule',
        'message_teacher' => 'Nachricht an die Schule',
    ],

    'admin' => [
        'entity_school' => 'Musikschule',
    ],

    'teachers' => [
        'title' => 'Lehrkräfte',
        'subtitle' => 'Fügen Sie Ihrer Schule Lehrkräfte hinzu und verwalten Sie sie gemeinsam mit ihren Schülern.',
        'add_teacher' => 'Lehrkraft hinzufügen',
        'no_teachers' => 'Noch keine Lehrkräfte. Laden Sie Ihre erste Lehrkraft ein.',
        'active_since' => 'Mitglied seit :date',
        'student_count' => ':count Schüler',
        'pending_approval' => 'Wartet auf die Zustimmung der Lehrkraft',
        'view_profile' => 'Ansehen',
        'remove_teacher' => 'Lehrkraft entfernen',
        'remove_confirm' => 'Diese Lehrkraft aus Ihrer Schule entfernen? Konto und Schüler bleiben erhalten.',
        'back_to_list' => 'Alle Lehrkräfte',
        'public_profile' => 'Öffentliches Profil',
        'stat_students' => 'Aktive Schüler',
        'stat_classes' => 'Klassen',
        'stat_assignments' => 'Aufgaben',
        'their_students' => 'Schüler dieser Lehrkraft',
        'no_students' => 'Diese Lehrkraft hat noch keine aktiven Schüler.',
        'view_student' => 'Ansehen',
        'pending_invitations' => 'Ausstehende Einladungen',
        'no_invitations' => 'Keine ausstehenden Einladungen.',
        'search_users' => 'Benutzer suchen',
        'invite_by_email' => 'Per E-Mail einladen',
        'share_link' => 'Link teilen',
        'search_placeholder' => 'Vorname, Nachname oder genaue E-Mail',
        'send_request' => 'Anfrage senden',
        'invite_name' => 'Name (optional)',
        'invite_email' => 'E-Mail-Adresse',
        'send_invitation' => 'Einladung senden',
        'link_expires' => 'Gültig bis',
        'create_link' => 'Link erstellen',
        'copy_link' => 'Link kopieren',
        'revoke' => 'Zurückziehen',
        'status_relationship-requested' => 'Mitgliedschaftsanfrage gesendet.',
        'status_invitation-sent' => 'Einladungs-E-Mail gesendet.',
        'status_invitation-link-created' => 'Einladungslink erstellt.',
        'status_invitation-revoked' => 'Einladung zurückgezogen.',
        'status_relationship-revoked' => 'Lehrkraft aus Ihrer Schule entfernt.',
        'error_self' => 'Sie können Ihr eigenes Konto nicht als Lehrkraft hinzufügen.',
        'error_target_school' => 'Schulkonten können nicht als Lehrkräfte hinzugefügt werden.',
        'error_already_related' => 'Dieser Benutzer ist bereits Mitglied oder hat eine ausstehende Anfrage.',
        'error_duplicate_invitation' => 'Für diese E-Mail existiert bereits eine ausstehende Einladung.',
        'error_limit_reached' => 'Ihr Plan erlaubt bis zu :limit Lehrkräfte. Führen Sie ein Upgrade durch, um mehr hinzuzufügen.',
    ],

    'my_schools' => [
        'title' => 'Meine Schulen',
        'subtitle' => 'Musikschulen, an denen Sie unterrichten, und ausstehende Mitgliedschaftsanfragen.',
        'no_schools' => 'Sie sind noch kein Mitglied einer Schule.',
        'pending' => 'Mitgliedschaftsanfrage — wartet auf Ihre Zustimmung',
        'since' => 'Mitglied seit :date',
        'view_public_profile' => 'Schulprofil ansehen',
        'approve' => 'Annehmen',
        'decline' => 'Ablehnen',
        'leave' => 'Schule verlassen',
        'leave_confirm' => 'Diese Schule verlassen? Ihr eigenes Konto und Ihre Schüler bleiben Ihnen erhalten.',
        'status_school-approved' => 'Sie sind der Schule beigetreten.',
        'status_school-declined' => 'Anfrage abgelehnt.',
        'status_school-left' => 'Sie haben die Schule verlassen.',
        'status_school-joined' => 'Sie sind der Schule beigetreten. Ihr Lehrkräfte-Panel ist bereit.',
    ],

    'invitations' => [
        'title' => 'Schuleinladung',
        'invited_you' => ':school hat Sie eingeladen, als Lehrkraft der Musikschule beizutreten.',
        'accept' => 'Einladung annehmen',
        'decline_hint' => 'Wenn Sie diese Schule nicht kennen, können Sie die Einladung einfach ignorieren.',
        'unusable' => 'Diese Einladung ist nicht mehr gültig.',
    ],

];
