<?php

return [

    'nav' => [
        'role_teacher' => 'École de Musique',
        'teachers' => 'Professeurs',
        'view_as_student' => 'Voir le profil public',
    ],

    'dashboard' => [
        'title' => 'Panneau de l\'École',
        'subtitle' => 'Gérez votre école, vos professeurs et vos élèves depuis un seul endroit.',
        'stat_pending_students' => 'En attente d\'approbation',
        'stat_new_students_month' => 'Nouveaux ce mois-ci',
        'teacher_stats' => 'Statistiques des professeurs',
        'stat_active_teachers' => 'Professeurs actifs',
        'stat_pending_teachers' => 'En attente d\'approbation',
        'stat_member_students' => 'Leurs élèves',
        'stat_member_classes' => 'Leurs classes',
        'stat_member_assignments' => 'Leurs devoirs',
        'stat_member_avg_score' => 'Score moyen',
    ],

    'profile' => [
        'title' => 'Profil de l\'École',
        'subtitle' => 'Gérez le profil public de votre école.',
    ],

    'public' => [
        'school_badge' => 'École de Musique',
        'message_teacher' => 'Envoyer un message à l\'école',
    ],

    'admin' => [
        'entity_school' => 'École de Musique',
    ],

    'teachers' => [
        'title' => 'Professeurs',
        'subtitle' => 'Ajoutez des professeurs à votre école et gérez-les avec leurs élèves.',
        'add_teacher' => 'Ajouter un professeur',
        'no_teachers' => 'Pas encore de professeurs. Invitez votre premier professeur pour commencer.',
        'active_since' => 'Membre depuis le :date',
        'student_count' => ':count élèves',
        'pending_approval' => 'En attente de l\'approbation du professeur',
        'view_profile' => 'Voir',
        'remove_teacher' => 'Retirer le professeur',
        'remove_confirm' => 'Retirer ce professeur de votre école ? Son compte et ses élèves restent intacts.',
        'back_to_list' => 'Tous les professeurs',
        'public_profile' => 'Profil public',
        'stat_students' => 'Élèves actifs',
        'stat_classes' => 'Classes',
        'stat_assignments' => 'Devoirs',
        'their_students' => 'Élèves de ce professeur',
        'no_students' => 'Ce professeur n\'a pas encore d\'élèves actifs.',
        'view_student' => 'Voir',
        'pending_invitations' => 'Invitations en attente',
        'no_invitations' => 'Aucune invitation en attente.',
        'search_users' => 'Rechercher des utilisateurs',
        'invite_by_email' => 'Inviter par e-mail',
        'share_link' => 'Partager un lien',
        'search_placeholder' => 'Prénom, nom ou e-mail exact',
        'send_request' => 'Envoyer la demande',
        'invite_name' => 'Nom (facultatif)',
        'invite_email' => 'Adresse e-mail',
        'send_invitation' => 'Envoyer l\'invitation',
        'link_expires' => 'Expire le',
        'create_link' => 'Créer le lien',
        'copy_link' => 'Copier le lien',
        'revoke' => 'Révoquer',
        'status_relationship-requested' => 'Demande d\'adhésion envoyée.',
        'status_invitation-sent' => 'E-mail d\'invitation envoyé.',
        'status_invitation-link-created' => 'Lien d\'invitation créé.',
        'status_invitation-revoked' => 'Invitation révoquée.',
        'status_relationship-revoked' => 'Professeur retiré de votre école.',
        'error_self' => 'Vous ne pouvez pas ajouter votre propre compte comme professeur.',
        'error_target_school' => 'Les comptes d\'école ne peuvent pas être ajoutés comme professeurs.',
        'error_already_related' => 'Cet utilisateur est déjà membre ou a une demande en attente.',
        'error_duplicate_invitation' => 'Une invitation en attente existe déjà pour cet e-mail.',
        'error_limit_reached' => 'Votre forfait permet jusqu\'à :limit professeurs. Passez à un forfait supérieur pour en ajouter plus.',
    ],

    'my_schools' => [
        'title' => 'Mes Écoles',
        'subtitle' => 'Écoles de musique où vous enseignez et demandes d\'adhésion en attente.',
        'no_schools' => 'Vous n\'êtes encore membre d\'aucune école.',
        'pending' => 'Demande d\'adhésion — en attente de votre approbation',
        'since' => 'Membre depuis le :date',
        'view_public_profile' => 'Voir le profil de l\'école',
        'approve' => 'Accepter',
        'decline' => 'Refuser',
        'leave' => 'Quitter l\'école',
        'leave_confirm' => 'Quitter cette école ? Vous conserverez votre compte et vos élèves.',
        'status_school-approved' => 'Vous avez rejoint l\'école.',
        'status_school-declined' => 'Demande refusée.',
        'status_school-left' => 'Vous avez quitté l\'école.',
        'status_school-joined' => 'Vous avez rejoint l\'école. Votre panneau professeur est prêt.',
    ],

    'invitations' => [
        'title' => 'Invitation d\'école',
        'invited_you' => ':school vous a invité à rejoindre son école de musique en tant que professeur.',
        'accept' => 'Accepter l\'invitation',
        'decline_hint' => 'Si vous ne connaissez pas cette école, vous pouvez simplement ignorer cette invitation.',
        'unusable' => 'Cette invitation n\'est plus valide.',
    ],

];
