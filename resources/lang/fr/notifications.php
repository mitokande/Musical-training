<?php

return [

    'appointment' => [
        'status_subject' => 'Mise à jour du rendez-vous — :when',
        'status' => [
            'confirmed' => 'Ton cours du :when est confirmé.',
            'rejected' => 'La demande de rendez-vous pour le :when a été refusée.',
            'cancelled_teacher' => 'Le cours du :when a été annulé par le professeur.',
            'cancelled_student' => "Le cours du :when a été annulé par l'élève.",
            'reschedule' => 'Un nouvel horaire a été demandé pour le cours du :when.',
            'completed' => 'Le cours du :when a été marqué comme terminé.',
            'no_show' => 'Le cours du :when a été marqué comme absence.',
            'default' => 'Le statut de ton rendez-vous a changé.',
        ],
        'lesson_link' => 'Lien du cours : :url',
        'view' => 'Voir le rendez-vous',
        'request_subject' => 'Nouvelle demande de rendez-vous de :name',
        'request_line' => ':name a demandé un cours le :when.',
        'topic' => 'Sujet : :topic',
        'review' => 'Examiner la demande',
    ],

    'verify' => [
        'preheader' => 'Un clic et ton compte Harmoniva est actif',
        'title' => 'Confirme ton e-mail',
        'btn_sub' => 'Le lien est valable 60 minutes.',
        'fallback' => 'Le bouton ne fonctionne pas ? Copie ce lien dans ton navigateur :',
        'subject' => 'Vérifie ton adresse e-mail',
        'line1' => 'Confirme ton adresse e-mail pour activer ton compte :app.',
        'action' => "Vérifier l'e-mail",
        'line2' => "Si tu n'as pas créé de compte, aucune action n'est nécessaire.",
    ],

    'invite' => [
        'teacher_subject' => ':name t\'a invité sur Harmoniva',
        'school_subject' => ':name t\'a invité à rejoindre son école sur Harmoniva',
        'heading' => 'Tu es invité sur Harmoniva 🎵',
        'teacher_intro' => '**:name** t\'a invité à rejoindre Harmoniva en tant qu\'élève.',
        'school_intro' => '**:name** t\'a invité à rejoindre son école de musique sur Harmoniva en tant que professeur.',
        'teacher_body' => "Harmoniva est une plateforme d'éducation musicale avec entraînement de l'oreille, pratique de la théorie musicale et parcours d'apprentissage guidés. Une fois connectés, ton professeur peut te donner des devoirs et suivre ta progression.",
        'school_body' => "Harmoniva est une plateforme d'éducation musicale avec entraînement de l'oreille, pratique de la théorie musicale et parcours d'apprentissage guidés. En tant que professeur membre, tu disposes de tous les outils pour enseigner — élèves, cours, devoirs, messagerie et un calendrier de réservation — et ton école peut t'aider à gérer tes élèves.",
        'accept' => "Accepter l'invitation",
        'expires' => 'Cette invitation expire le :date.',
        'ignore' => "Si tu n'attendais pas cette invitation, tu peux ignorer cet e-mail en toute tranquillité.",
        'thanks' => 'Merci,',
    ],

];
