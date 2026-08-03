<?php

/**
 * Textes des e-mails système (modèles de l'Email Center). Les champs
 * {{placeholder}} sont remplacés par le TemplateRenderer pour chaque
 * destinataire : à conserver tels quels. Conserver aussi le HTML en ligne
 * (<strong>, <a href>).
 */
return [

    'footer' => [
        'manage_prefs' => 'Gérer les préférences e-mail',
        'unsubscribe' => 'Se désabonner',
    ],

    'hi' => 'Bonjour {{user_first_name}},',
    'guide_block' => [
        'title' => "Nouveau dans l'entraînement de l'oreille ? Commence ici.",
        'slogan' => "« Entraîne-toi plus intelligemment, pas plus durement. » Notre guide pas à pas t'accompagne de ton premier intervalle jusqu'à une écoute fluide.",
        'button' => "📖 Lire le guide de l'utilisateur",
    ],

    'welcome' => [
        'subject' => 'Bienvenue sur {{app_name}}, {{user_first_name}} ! 🎵',
        'preheader' => "Ton oreille musicale commence son entraînement aujourd'hui — voici tout ce que tu débloques",
        'title' => 'Bienvenue à bord, {{user_first_name}} !',
        'subtitle' => "Tu viens de rejoindre {{app_name}}, la façon la plus agréable d'entraîner ton oreille musicale. Voici ce qui t'attend :",
        'f1_t' => 'Passe le test de niveau', 'f1_d' => 'Nous adaptons un parcours d\'apprentissage personnalisé à ton niveau exact.',
        'f2_t' => 'Entraîne-toi avec du vrai audio', 'f2_d' => 'Notes isolées, intervalles, accords, gammes, rythme et dictée mélodique.',
        'f3_t' => 'Suis chaque session', 'f3_d' => 'La précision, les séries et les graphiques de progression te font avancer.',
        'f4_t' => 'Entraînement assisté par IA', 'f4_d' => 'Des exercices intelligents ciblant tes points faibles (Premium).',
        'btn' => "🚀 Commencer l'entraînement", 'btn_sub' => 'Aucune configuration : passe directement à ta première session.',
        'ps' => 'Des questions ? Réponds simplement à cet e-mail — une vraie personne lit chacun. 💜',
    ],

    'first_exercise' => [
        'subject' => '{{user_first_name}}, ton premier exercice t\'attend 🎧',
        'preheader' => 'Entraîner ton oreille ne prend que 5 minutes',
        'title' => 'Prêt pour ta première session ?',
        'p1' => "Tu as créé ton compte {{app_name}} il y a quelques jours, mais tu n'as pas encore essayé d'exercice. Le tout premier prend moins de cinq minutes — et c'est celui qui met ton oreille en route.",
        'btn' => '🎧 Essayer ton premier exercice', 'btn_sub' => "Moins de 5 minutes. Le plus dur, c'est de commencer.",
        'p2' => "Tu ne sais pas par où commencer ? Le <a href=\"{{app_url}}/learn\" style=\"color:#7c3aed;font-weight:600;\">parcours d'apprentissage</a> te guide pas à pas, ou parcours d'abord le <a href=\"{{guide_url}}\" style=\"color:#7c3aed;font-weight:600;\">guide de l'utilisateur</a>.",
    ],

    'learning_path' => [
        'subject' => "Ton parcours d'apprentissage te réclame, {{user_first_name}} 🎼",
        'preheader' => 'Reprends exactement là où tu t\'es arrêté',
        'title' => "Reprends là où tu t'es arrêté",
        'p1' => "Ton oreille s'affinait — ne laisse pas ces progrès s'estomper. Ton parcours d'apprentissage est exactement là où tu l'as laissé, séries comprises, prêt quand tu l'es.",
        'btn' => '🎼 Continuer le parcours', 'btn_sub' => "Même une courte session entretient l'élan.",
        'p2' => "🔥 La régularité l'emporte sur l'intensité. Cinq minutes concentrées aujourd'hui, c'est déjà gagné.",
    ],

    'weekly_progress' => [
        'subject' => 'Ta semaine sur {{app_name}} : {{weekly_sessions}} sessions 📈',
        'preheader' => "Ton récap hebdo d'entraînement de l'oreille",
        'title' => 'Ta semaine en résumé',
        'subtitle' => 'Beau travail cette semaine, {{user_first_name}}. Voici le récap :',
        'sessions' => 'Sessions', 'accuracy' => 'Précision', 'minutes' => 'Minutes',
        'btn' => '📈 Continue comme ça', 'btn_sub' => 'De petites semaines finissent par former une oreille entraînée.',
    ],

    're_engagement' => [
        'subject' => 'Nous avons sauvegardé ta progression, {{user_first_name}} 🎹',
        'preheader' => 'Ta progression est en sécurité — reviens quand tu veux',
        'title' => 'Ta progression est en sécurité chez nous',
        'p1' => 'Cela fait un moment depuis ta dernière session de pratique sur {{app_name}}. Bonne nouvelle : tes statistiques, tes séries et ta progression dans le parcours sont sauvegardées exactement là où tu les as laissées.',
        'btn' => "🎹 Reprendre l'entraînement", 'btn_sub' => "Cinq minutes aujourd'hui valent mieux qu'une heure un jour.",
    ],

    'premium_intro' => [
        'subject' => 'Découvre {{app_name}} Premium, {{user_first_name}} ⭐',
        'preheader' => 'Pratique illimitée, coaching IA et plus — vois ce que Premium ajoute',
        'badge' => '✦ PREMIUM',
        'title' => 'Va plus loin dans ton entraînement',
        'subtitle' => 'Bonjour {{user_first_name}} — tu explores {{app_name}} depuis quelques jours. Voici ce que <strong style="color:#7c3aed;">Premium</strong> débloque quand tu veux :',
        'f1_t' => 'Exercices quotidiens illimités', 'f1_d' => 'Fini la limite de 3 par jour — pratique autant que ton oreille le souhaite.',
        'f2_t' => 'Entraînement assisté par IA', 'f2_d' => 'Des exercices générés autour de tes points faibles personnels.',
        'f3_t' => 'Modèles enregistrés illimités', 'f3_d' => 'Garde chaque exercice favori à portée de main.',
        'f4_t' => 'Dictée mélodique complète', 'f4_d' => 'Le moteur de dictée complet, avec rythme et mélodies tonales.',
        'btn' => '⭐ Découvrir Premium', 'btn_sub' => 'Améliore quand tu veux. Annule quand tu veux.',
        'p2' => "Curieux de voir comment tout s'articule ? Le <a href=\"{{guide_url}}\" style=\"color:#7c3aed;font-weight:600;\">guide de l'utilisateur</a> montre chaque fonctionnalité en action.",
    ],

    'premium_upsell' => [
        'subject' => 'Tu as dépassé le forfait gratuit, {{user_first_name}} ⭐',
        'preheader' => 'Exercices illimités, mode IA et plus avec Premium',
        'title' => 'Tu fais le travail',
        'subtitle' => "Bonjour {{user_first_name}} — tu pratiques avec régularité. C'est exactement ainsi que l'oreille se forme. Voici ce qui te ferait progresser :",
        'f1_t' => 'Exercices quotidiens illimités', 'f1_d' => 'Tu butes sans cesse sur la limite gratuite de 3 par jour — supprime-la entièrement.',
        'f2_t' => 'Entraînement assisté par IA', 'f2_d' => 'Adapté aux intervalles et accords que tu manques le plus.',
        'f3_t' => 'Modèles enregistrés illimités', 'f3_d' => 'Enregistre chaque exercice de ta routine.',
        'btn' => '⭐ Voir les forfaits Premium', 'btn_sub' => 'Tu as mérité la mise à niveau.',
    ],

    'trial_ending' => [
        'subject' => 'Ton essai Premium se termine dans {{trial_days_left}} jours',
        'preheader' => 'Garde ta pratique illimitée',
        'title' => 'Ton essai touche à sa fin',
        'p1' => 'Ton essai gratuit <strong>{{app_name}} Premium</strong> se termine le <strong>{{trial_ends_on}}</strong> — soit dans {{trial_days_left}} jours.',
        'p2' => "Rien ne sera facturé : nous n'avons jamais pris tes coordonnées bancaires. À la fin de l'essai, ton compte revient simplement au forfait gratuit, et tout ton historique de pratique reste intact.",
        'p3' => "Tu veux garder les exercices illimités, l'entraînement assisté par IA et tout ce que Premium débloque ? Tu peux t'abonner à tout moment.",
        'btn' => '💳 Gérer mon forfait', 'btn_sub' => 'Garde ta pratique illimitée sans manquer une note.',
    ],

    'trial_ended' => [
        'subject' => 'Ton essai Premium est terminé, {{user_first_name}}',
        'preheader' => 'Tu reviens au forfait gratuit — ta progression est en sécurité',
        'title' => "Merci d'avoir essayé Premium",
        'p1' => "Ton essai gratuit est terminé et ton compte est revenu au <strong>forfait gratuit</strong>. Tu n'as pas été facturé — nous n'avons jamais demandé de carte.",
        'p2' => "Tout ce que tu as pratiqué pendant l'essai est sauvegardé : tes statistiques, tes séries et ta progression sont toujours là.",
        'btn' => '⭐ Voir les forfaits Premium', 'btn_sub' => 'Reprends Premium en un clic.',
    ],

    // --- Professeurs ---
    'welcome_teacher' => [
        'subject' => 'Bienvenue sur {{app_name}} pour les professeurs, {{user_first_name}} ! 🎓',
        'preheader' => 'Configure ton profil, fais-toi découvrir et commence à enseigner',
        'badge' => '🎓 POUR LES PROFESSEURS',
        'title' => 'Bienvenue à bord, {{user_first_name}} !',
        'subtitle' => 'Ton compte professeur <strong style="color:#7c3aed;">{{app_name}}</strong> est prêt. Voici comment le configurer et commencer à toucher des élèves :',
        'f1_t' => 'Complète ton profil public', 'f1_d' => 'Ajoute ta bio, tes instruments et ton expérience, puis soumets-le pour approbation.',
        'f2_t' => 'Définis tes disponibilités', 'f2_d' => 'Ouvre ton calendrier pour que les élèves réservent directement des cours.',
        'f3_t' => 'Connecte-toi avec des élèves', 'f3_d' => "Invite tes propres élèves ou fais-toi découvrir dans l'annuaire.",
        'f4_t' => 'Publie du contenu', 'f4_d' => 'Partage des articles et des leçons pour bâtir ta réputation.',
        'btn' => '🎓 Ouvrir le tableau de bord professeur', 'btn_sub' => 'Ton QG d\'enseignement — profil, calendrier, élèves et messages.',
        'promo_t' => 'Nouveau dans l\'enseignement sur {{app_name}} ?', 'promo_s' => "« Entraîne l'oreille, développe ton studio. » Vois comment fonctionnent les profils, les réservations et les paiements.", 'promo_btn' => "📖 Comment fonctionne l'enseignement",
        'ps' => 'Des questions sur ton compte professeur ? Réponds — nous serons ravis de t\'aider. 💜',
    ],

    'premium_intro_teacher' => [
        'subject' => 'Développe ton enseignement avec {{app_name}} Premium, {{user_first_name}} ⭐',
        'preheader' => 'Réservations, liens de paiement, publication de contenu et profil mis en avant',
        'badge' => '✦ PREMIUM PROFESSEUR',
        'title' => 'Développe ton studio d\'enseignement',
        'subtitle' => 'Bonjour {{user_first_name}} — tu es sur {{app_name}} depuis quelques jours. Voici ce que <strong style="color:#7c3aed;">Premium</strong> débloque pour les professeurs :',
        'f1_t' => 'Accepte réservations et paiements', 'f1_d' => 'Laisse les élèves réserver et payer des cours avec tes propres liens de paiement.',
        'f2_t' => 'Publie du contenu illimité', 'f2_d' => 'Articles, leçons et médias pour mettre en valeur ton expertise.',
        'f3_t' => 'Profil mis en avant et prioritaire', 'f3_d' => "Démarque-toi dans l'annuaire des professeurs et fais-toi découvrir plus vite.",
        'f4_t' => 'Outils de gestion des élèves', 'f4_d' => 'Devoirs, suivi de progression et messagerie au même endroit.',
        'btn' => '⭐ Voir Premium Professeur', 'btn_sub' => 'Transforme ton enseignement en un studio florissant.',
        'p2' => "Tu veux d'abord la vue d'ensemble ? <a href=\"{{guide_url}}\" style=\"color:#7c3aed;font-weight:600;\">Vois comment fonctionne l'enseignement</a>.",
    ],

    'trial_ending_teacher' => [
        'subject' => 'Ton essai Premium Professeur se termine dans {{trial_days_left}} jours',
        'preheader' => 'Garde les réservations, les paiements et la publication de contenu',
        'title' => 'Ton essai professeur touche à sa fin',
        'p1' => 'Ton essai gratuit <strong>{{app_name}} Premium Professeur</strong> se termine le <strong>{{trial_ends_on}}</strong> — dans {{trial_days_left}} jours.',
        'p2' => "Rien ne sera facturé : nous n'avons jamais pris tes coordonnées bancaires. À la fin de l'essai, ton compte professeur revient à <strong>Basic</strong>, et des fonctions comme les réservations, les liens de paiement et la publication de contenu se mettent en pause — mais ton profil et tes élèves restent intacts.",
        'btn' => '💳 Garder Premium Professeur', 'btn_sub' => 'Ne perds pas tes réservations ni ton contenu.',
    ],

    'trial_ended_teacher' => [
        'subject' => 'Ton essai Premium Professeur est terminé, {{user_first_name}}',
        'preheader' => 'Ton profil d\'enseignant est en sécurité — tu reviens à Basic',
        'title' => 'Ton essai professeur est terminé',
        'p1' => "Ton essai gratuit est terminé et ton compte professeur est revenu à <strong>Basic</strong>. Tu n'as pas été facturé — nous n'avons jamais demandé de carte.",
        'p2' => 'Ton profil public, tes élèves et tes messages sont en sécurité. Les réservations, les liens de paiement et la publication de contenu sont prêts à être réactivés dès que tu passes à un forfait supérieur.',
        'btn' => '⭐ Voir Premium Professeur', 'btn_sub' => 'Récupère tes outils de studio en un clic.',
    ],

    // --- Écoles ---
    'welcome_school' => [
        'subject' => 'Bienvenue sur {{app_name}} pour les écoles, {{user_first_name}} ! 🏫',
        'preheader' => 'Configure ton école, ajoute tes professeurs et gère tout au même endroit',
        'badge' => '🏫 POUR LES ÉCOLES',
        'title' => 'Bienvenue à bord, {{user_first_name}} !',
        'subtitle' => 'Ton compte école <strong style="color:#7c3aed;">{{app_name}}</strong> est prêt. Voici comment le configurer et embarquer tes professeurs :',
        'f1_t' => 'Configure le profil de ton école', 'f1_d' => "Ajoute les informations et l'identité de ton école, puis soumets-le pour approbation.",
        'f2_t' => 'Ajoute tes professeurs', 'f2_d' => 'Invite ou connecte des professeurs membres et gère-les depuis un seul panneau.',
        'f3_t' => 'Gère les adhésions', 'f3_d' => 'Gère les relations avec les professeurs, les invitations et les accès de façon centralisée.',
        'f4_t' => 'Fais-toi découvrir', 'f4_d' => "Mets ton école en avant dans l'annuaire public.",
        'btn' => '🏫 Ouvrir le panneau école', 'btn_sub' => 'Ton QG d\'école — profil, professeurs et adhésions.',
        'promo_t' => 'Nouveau sur {{app_name}} pour les écoles ?', 'promo_s' => '« Un seul foyer pour toute ton école de musique. » Vois comment écoles et professeurs collaborent.', 'promo_btn' => '📖 Comment fonctionnent les écoles',
        'ps' => 'Besoin d\'aide pour configurer ton école ? Réponds — on t\'aide à démarrer. 💜',
    ],

    'premium_intro_school' => [
        'subject' => 'Débloque {{app_name}} Premium pour ton école, {{user_first_name}} ⭐',
        'preheader' => 'Professeurs illimités, identité d\'école et visibilité prioritaire',
        'badge' => '✦ PREMIUM ÉCOLE',
        'title' => 'Tout ce dont ton école a besoin',
        'subtitle' => 'Bonjour {{user_first_name}} — voici ce que <strong style="color:#7c3aed;">Premium</strong> débloque pour ton école sur {{app_name}} :',
        'f1_t' => 'Professeurs membres illimités', 'f1_d' => 'Ajoute autant de professeurs que nécessaire à ton école.',
        'f2_t' => 'Identité d\'école', 'f2_d' => 'Présente ton école avec ta propre identité dans tout Harmoniva.',
        'f3_t' => 'Visibilité prioritaire', 'f3_d' => "Apparais plus haut et fais-toi découvrir dans l'annuaire.",
        'f4_t' => 'Supervision et outils', 'f4_d' => 'Gère professeurs, adhésions et activité depuis un seul panneau.',
        'btn' => '⭐ Voir Premium École', 'btn_sub' => "Tout ce qu'il faut à ton école de musique pour grandir.",
        'p2' => "Tu veux d'abord voir ? <a href=\"{{guide_url}}\" style=\"color:#7c3aed;font-weight:600;\">Comment fonctionnent les écoles sur {{app_name}}</a>.",
    ],

    'trial_ending_school' => [
        'subject' => 'Ton essai Premium École se termine dans {{trial_days_left}} jours',
        'preheader' => 'Garde les professeurs illimités et l\'identité de ton école',
        'title' => 'Ton essai école touche à sa fin',
        'p1' => 'Ton essai gratuit <strong>{{app_name}} Premium École</strong> se termine le <strong>{{trial_ends_on}}</strong> — dans {{trial_days_left}} jours.',
        'p2' => "Rien ne sera facturé : nous n'avons jamais pris tes coordonnées bancaires. À la fin de l'essai, ton compte école revient à <strong>Basic</strong> — mais le profil de ton école, tes professeurs et tes adhésions restent intacts.",
        'btn' => '💳 Garder Premium École', 'btn_sub' => 'Conserve tes professeurs et ton identité.',
    ],

    'trial_ended_school' => [
        'subject' => 'Ton essai Premium École est terminé, {{user_first_name}}',
        'preheader' => 'Le profil de ton école est en sécurité — tu reviens à Basic',
        'title' => 'Ton essai école est terminé',
        'p1' => "Ton essai gratuit est terminé et ton compte école est revenu à <strong>Basic</strong>. Tu n'as pas été facturé — nous n'avons jamais demandé de carte.",
        'p2' => 'Le profil de ton école, tes professeurs et tes adhésions sont en sécurité. Les fonctions Premium École sont prêtes à être réactivées dès que tu passes à un forfait supérieur.',
        'btn' => '⭐ Voir Premium École', 'btn_sub' => 'Réactive les outils de ton école en un clic.',
    ],

];
