<?php

return [

    'nav' => [
        'role_teacher' => 'Musikskola',
        'teachers' => 'Lärare',
        'view_as_student' => 'Visa offentlig profil',
    ],

    'dashboard' => [
        'title' => 'Skolpanel',
        'subtitle' => 'Hantera din skola, dina lärare och elever på ett ställe.',
        'stat_pending_students' => 'Väntar på godkännande',
        'stat_new_students_month' => 'Nya denna månad',
        'teacher_stats' => 'Lärarstatistik',
        'stat_active_teachers' => 'Aktiva lärare',
        'stat_pending_teachers' => 'Väntar på godkännande',
        'stat_member_students' => 'Deras elever',
        'stat_member_classes' => 'Deras klasser',
        'stat_member_assignments' => 'Deras uppgifter',
        'stat_member_avg_score' => 'Snittpoäng',
    ],

    'profile' => [
        'title' => 'Skolprofil',
        'subtitle' => 'Hantera din skolas offentliga profil.',
    ],

    'public' => [
        'school_badge' => 'Musikskola',
        'message_teacher' => 'Skicka meddelande till skolan',
    ],

    'admin' => [
        'entity_school' => 'Musikskola',
    ],

    'teachers' => [
        'title' => 'Lärare',
        'subtitle' => 'Lägg till lärare i din skola och hantera dem tillsammans med deras elever.',
        'add_teacher' => 'Lägg till lärare',
        'no_teachers' => 'Inga lärare ännu. Bjud in din första lärare för att komma igång.',
        'active_since' => 'Medlem sedan :date',
        'student_count' => ':count elever',
        'pending_approval' => 'Väntar på lärarens godkännande',
        'view_profile' => 'Visa',
        'remove_teacher' => 'Ta bort lärare',
        'remove_confirm' => 'Ta bort denna lärare från din skola? Kontot och eleverna påverkas inte.',
        'back_to_list' => 'Alla lärare',
        'public_profile' => 'Offentlig profil',
        'stat_students' => 'Aktiva elever',
        'stat_classes' => 'Klasser',
        'stat_assignments' => 'Uppgifter',
        'their_students' => 'Denna lärares elever',
        'no_students' => 'Denna lärare har inga aktiva elever ännu.',
        'view_student' => 'Visa',
        'pending_invitations' => 'Väntande inbjudningar',
        'no_invitations' => 'Inga väntande inbjudningar.',
        'search_users' => 'Sök användare',
        'invite_by_email' => 'Bjud in via e-post',
        'share_link' => 'Dela länk',
        'search_placeholder' => 'Förnamn, efternamn eller exakt e-post',
        'send_request' => 'Skicka förfrågan',
        'invite_name' => 'Namn (valfritt)',
        'invite_email' => 'E-postadress',
        'send_invitation' => 'Skicka inbjudan',
        'link_expires' => 'Gäller till',
        'create_link' => 'Skapa länk',
        'copy_link' => 'Kopiera länk',
        'revoke' => 'Återkalla',
        'status_relationship-requested' => 'Medlemsförfrågan skickad.',
        'status_invitation-sent' => 'Inbjudningsmejl skickat.',
        'status_invitation-link-created' => 'Inbjudningslänk skapad.',
        'status_invitation-revoked' => 'Inbjudan återkallad.',
        'status_relationship-revoked' => 'Läraren har tagits bort från din skola.',
        'error_self' => 'Du kan inte lägga till ditt eget konto som lärare.',
        'error_target_school' => 'Skolkonton kan inte läggas till som lärare.',
        'error_already_related' => 'Denna användare är redan medlem eller har en väntande förfrågan.',
        'error_duplicate_invitation' => 'En väntande inbjudan finns redan för denna e-post.',
        'error_limit_reached' => 'Din plan tillåter upp till :limit lärare. Uppgradera för att lägga till fler.',
    ],

    'my_schools' => [
        'title' => 'Mina Skolor',
        'subtitle' => 'Musikskolor där du undervisar och väntande medlemsförfrågningar.',
        'no_schools' => 'Du är inte medlem i någon skola ännu.',
        'pending' => 'Medlemsförfrågan — väntar på ditt godkännande',
        'since' => 'Medlem sedan :date',
        'view_public_profile' => 'Visa skolans profil',
        'approve' => 'Acceptera',
        'decline' => 'Avböj',
        'leave' => 'Lämna skolan',
        'leave_confirm' => 'Lämna denna skola? Du behåller ditt eget konto och dina elever.',
        'status_school-approved' => 'Du har gått med i skolan.',
        'status_school-declined' => 'Förfrågan avböjd.',
        'status_school-left' => 'Du har lämnat skolan.',
        'status_school-joined' => 'Du har gått med i skolan. Din lärarpanel är redo.',
    ],

    'invitations' => [
        'title' => 'Skolinbjudan',
        'invited_you' => ':school har bjudit in dig att gå med i deras musikskola som lärare.',
        'accept' => 'Acceptera inbjudan',
        'decline_hint' => 'Om du inte känner till denna skola kan du helt enkelt ignorera inbjudan.',
        'unusable' => 'Denna inbjudan är inte längre giltig.',
    ],

];
