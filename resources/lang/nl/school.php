<?php

return [

    'nav' => [
        'role_teacher' => 'Muziekschool',
        'teachers' => 'Docenten',
        'view_as_student' => 'Openbaar profiel bekijken',
    ],

    'dashboard' => [
        'title' => 'Schoolpaneel',
        'subtitle' => 'Beheer je school, docenten en leerlingen vanaf één plek.',
        'stat_pending_students' => 'Wacht op goedkeuring',
        'stat_new_students_month' => 'Nieuw deze maand',
        'teacher_stats' => 'Docentstatistieken',
        'stat_active_teachers' => 'Actieve docenten',
        'stat_pending_teachers' => 'Wacht op goedkeuring',
        'stat_member_students' => 'Hun leerlingen',
        'stat_member_classes' => 'Hun klassen',
        'stat_member_assignments' => 'Hun opdrachten',
        'stat_member_avg_score' => 'Gem. score',
    ],

    'profile' => [
        'title' => 'Schoolprofiel',
        'subtitle' => 'Beheer het openbare profiel van je school.',
    ],

    'public' => [
        'school_badge' => 'Muziekschool',
        'message_teacher' => 'Stuur de school een bericht',
    ],

    'admin' => [
        'entity_school' => 'Muziekschool',
    ],

    'teachers' => [
        'title' => 'Docenten',
        'subtitle' => 'Voeg docenten toe aan je school en beheer ze samen met hun leerlingen.',
        'add_teacher' => 'Docent toevoegen',
        'no_teachers' => 'Nog geen docenten. Nodig je eerste docent uit om te beginnen.',
        'active_since' => 'Lid sinds :date',
        'student_count' => ':count leerlingen',
        'pending_approval' => 'Wacht op goedkeuring van de docent',
        'view_profile' => 'Bekijken',
        'remove_teacher' => 'Docent verwijderen',
        'remove_confirm' => 'Deze docent uit je school verwijderen? Het account en de leerlingen blijven behouden.',
        'back_to_list' => 'Alle docenten',
        'public_profile' => 'Openbaar profiel',
        'stat_students' => 'Actieve leerlingen',
        'stat_classes' => 'Klassen',
        'stat_assignments' => 'Opdrachten',
        'their_students' => 'Leerlingen van deze docent',
        'no_students' => 'Deze docent heeft nog geen actieve leerlingen.',
        'view_student' => 'Bekijken',
        'pending_invitations' => 'Openstaande uitnodigingen',
        'no_invitations' => 'Geen openstaande uitnodigingen.',
        'search_users' => 'Gebruikers zoeken',
        'invite_by_email' => 'Uitnodigen per e-mail',
        'share_link' => 'Link delen',
        'search_placeholder' => 'Voornaam, achternaam of exact e-mailadres',
        'send_request' => 'Verzoek versturen',
        'invite_name' => 'Naam (optioneel)',
        'invite_email' => 'E-mailadres',
        'send_invitation' => 'Uitnodiging versturen',
        'link_expires' => 'Verloopt op',
        'create_link' => 'Link aanmaken',
        'copy_link' => 'Link kopiëren',
        'revoke' => 'Intrekken',
        'status_relationship-requested' => 'Lidmaatschapsverzoek verstuurd.',
        'status_invitation-sent' => 'Uitnodigingsmail verstuurd.',
        'status_invitation-link-created' => 'Uitnodigingslink aangemaakt.',
        'status_invitation-revoked' => 'Uitnodiging ingetrokken.',
        'status_relationship-revoked' => 'Docent uit je school verwijderd.',
        'error_self' => 'Je kunt je eigen account niet als docent toevoegen.',
        'error_target_school' => 'Schoolaccounts kunnen niet als docent worden toegevoegd.',
        'error_already_related' => 'Deze gebruiker is al lid of heeft een openstaand verzoek.',
        'error_duplicate_invitation' => 'Er bestaat al een openstaande uitnodiging voor dit e-mailadres.',
        'error_limit_reached' => 'Je abonnement staat maximaal :limit docenten toe. Upgrade om er meer toe te voegen.',
    ],

    'my_schools' => [
        'title' => 'Mijn Scholen',
        'subtitle' => 'Muziekscholen waar je lesgeeft en openstaande lidmaatschapsverzoeken.',
        'no_schools' => 'Je bent nog geen lid van een school.',
        'pending' => 'Lidmaatschapsverzoek — wacht op jouw goedkeuring',
        'since' => 'Lid sinds :date',
        'view_public_profile' => 'Schoolprofiel bekijken',
        'approve' => 'Accepteren',
        'decline' => 'Afwijzen',
        'leave' => 'School verlaten',
        'leave_confirm' => 'Deze school verlaten? Je behoudt je eigen account en leerlingen.',
        'status_school-approved' => 'Je bent lid geworden van de school.',
        'status_school-declined' => 'Verzoek afgewezen.',
        'status_school-left' => 'Je hebt de school verlaten.',
        'status_school-joined' => 'Je bent lid geworden van de school. Je docentenpaneel staat klaar.',
    ],

    'invitations' => [
        'title' => 'Schooluitnodiging',
        'invited_you' => ':school heeft je uitgenodigd om als docent lid te worden van hun muziekschool.',
        'accept' => 'Uitnodiging accepteren',
        'decline_hint' => 'Als je deze school niet kent, kun je deze uitnodiging gewoon negeren.',
        'unusable' => 'Deze uitnodiging is niet meer geldig.',
    ],

];
