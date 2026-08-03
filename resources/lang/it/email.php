<?php

/**
 * Testi delle e-mail di sistema (modelli dell'Email Center). I campi
 * {{placeholder}} vengono sostituiti dal TemplateRenderer per ogni
 * destinatario: mantienili invariati. Mantieni anche l'HTML inline
 * (<strong>, <a href>).
 */
return [

    'footer' => [
        'manage_prefs' => 'Gestisci le preferenze e-mail',
        'unsubscribe' => 'Annulla iscrizione',
    ],

    'hi' => 'Ciao {{user_first_name}},',
    'guide_block' => [
        'title' => "Nuovo nell'allenamento dell'orecchio? Inizia da qui.",
        'slogan' => '“Allenati in modo più intelligente, non più faticoso.” La nostra guida passo passo ti accompagna dal primo intervallo all’ascolto fluente.',
        'button' => '📖 Leggi la guida utente',
    ],

    'welcome' => [
        'subject' => 'Benvenuto su {{app_name}}, {{user_first_name}}! 🎵',
        'preheader' => 'Il tuo orecchio musicale inizia ad allenarsi oggi — ecco tutto ciò che sblocchi',
        'title' => 'Benvenuto a bordo, {{user_first_name}}!',
        'subtitle' => 'Ti sei appena unito a {{app_name}}, il modo più piacevole per allenare il tuo orecchio musicale. Ecco cosa ti aspetta:',
        'f1_t' => 'Fai il test di livello', 'f1_d' => 'Adattiamo un Percorso di Apprendimento personalizzato al tuo livello esatto.',
        'f2_t' => 'Allenati con audio reale', 'f2_d' => 'Note singole, intervalli, accordi, scale, ritmo e dettato melodico.',
        'f3_t' => 'Segui ogni sessione', 'f3_d' => 'Precisione, serie e grafici dei progressi ti fanno andare avanti.',
        'f4_t' => 'Pratica assistita dall’IA', 'f4_d' => 'Esercizi intelligenti mirati ai tuoi punti deboli (Premium).',
        'btn' => '🚀 Inizia ad allenarti', 'btn_sub' => 'Nessuna configurazione — passa subito alla tua prima sessione.',
        'ps' => 'Domande? Rispondi a questa e-mail — una persona vera legge ognuna. 💜',
    ],

    'first_exercise' => [
        'subject' => '{{user_first_name}}, il tuo primo esercizio ti aspetta 🎧',
        'preheader' => 'Allenare il tuo orecchio richiede solo 5 minuti',
        'title' => 'Pronto per la tua prima sessione?',
        'p1' => 'Hai creato il tuo account {{app_name}} un paio di giorni fa, ma non hai ancora provato un esercizio. Il primo richiede meno di cinque minuti — ed è quello che mette in moto il tuo orecchio.',
        'btn' => '🎧 Prova il tuo primo esercizio', 'btn_sub' => 'Meno di 5 minuti. La parte più difficile è iniziare.',
        'p2' => 'Non sai da dove cominciare? Il <a href="{{app_url}}/learn" style="color:#7c3aed;font-weight:600;">Percorso di Apprendimento</a> ti guida passo passo, oppure dai prima un’occhiata alla <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">guida utente</a>.',
    ],

    'learning_path' => [
        'subject' => 'Il tuo Percorso di Apprendimento ti aspetta, {{user_first_name}} 🎼',
        'preheader' => 'Riprendi esattamente da dove eri rimasto',
        'title' => 'Riprendi da dove eri rimasto',
        'p1' => 'Il tuo orecchio stava diventando più fine — non lasciare svanire questi progressi. Il tuo Percorso di Apprendimento è esattamente dove l’hai lasciato, serie comprese, pronto quando lo sei tu.',
        'btn' => '🎼 Continua il Percorso di Apprendimento', 'btn_sub' => 'Anche una breve sessione mantiene lo slancio.',
        'p2' => '🔥 La costanza batte l’intensità. Cinque minuti concentrati oggi sono già una vittoria.',
    ],

    'weekly_progress' => [
        'subject' => 'La tua settimana su {{app_name}}: {{weekly_sessions}} sessioni 📈',
        'preheader' => "Il tuo riepilogo settimanale dell'allenamento dell'orecchio",
        'title' => 'La tua settimana in sintesi',
        'subtitle' => 'Bel lavoro questa settimana, {{user_first_name}}. Ecco il riepilogo:',
        'sessions' => 'Sessioni', 'accuracy' => 'Precisione', 'minutes' => 'Minuti',
        'btn' => '📈 Continua così', 'btn_sub' => 'Piccole settimane costruiscono un orecchio allenato.',
    ],

    're_engagement' => [
        'subject' => 'Abbiamo salvato i tuoi progressi, {{user_first_name}} 🎹',
        'preheader' => 'I tuoi progressi sono al sicuro — torna quando vuoi',
        'title' => 'I tuoi progressi sono al sicuro con noi',
        'p1' => "È passato un po' dall'ultima sessione di pratica su {{app_name}}. Buone notizie: le tue statistiche, le serie e i progressi del Percorso di Apprendimento sono salvati esattamente dove li hai lasciati.",
        'btn' => '🎹 Riprendi l’allenamento', 'btn_sub' => 'Cinque minuti oggi valgono più di un’ora un giorno qualsiasi.',
    ],

    'premium_intro' => [
        'subject' => 'Scopri {{app_name}} Premium, {{user_first_name}} ⭐',
        'preheader' => 'Pratica illimitata, coaching con IA e altro — scopri cosa aggiunge Premium',
        'badge' => '✦ PREMIUM',
        'title' => 'Porta il tuo allenamento più lontano',
        'subtitle' => 'Ciao {{user_first_name}} — esplori {{app_name}} da qualche giorno. Ecco cosa sblocca <strong style="color:#7c3aed;">Premium</strong> quando vuoi:',
        'f1_t' => 'Esercizi giornalieri illimitati', 'f1_d' => 'Niente più limite di 3 al giorno — allenati quanto il tuo orecchio desidera.',
        'f2_t' => 'Pratica assistita dall’IA', 'f2_d' => 'Esercizi generati intorno ai tuoi punti deboli personali.',
        'f3_t' => 'Modelli salvati illimitati', 'f3_d' => 'Tieni ogni esercizio preferito a portata di tocco.',
        'f4_t' => 'Dettato melodico completo', 'f4_d' => 'Il motore di dettato completo, con ritmo e melodie tonali.',
        'btn' => '⭐ Esplora Premium', 'btn_sub' => 'Passa a Premium quando vuoi. Annulla quando vuoi.',
        'p2' => 'Curioso di vedere come si incastra tutto? La <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">guida utente</a> mostra ogni funzione in azione.',
    ],

    'premium_upsell' => [
        'subject' => 'Hai superato il piano gratuito, {{user_first_name}} ⭐',
        'preheader' => 'Esercizi illimitati, modalità IA e altro con Premium',
        'title' => 'Ti stai impegnando',
        'subtitle' => "Ciao {{user_first_name}} — ti alleni con costanza. È esattamente così che si allena l'orecchio. Ecco cosa ti porterebbe più lontano:",
        'f1_t' => 'Esercizi giornalieri illimitati', 'f1_d' => 'Continui a sbattere contro il limite gratuito di 3 al giorno — rimuovilo del tutto.',
        'f2_t' => 'Pratica assistita dall’IA', 'f2_d' => 'Su misura per gli intervalli e gli accordi che sbagli di più.',
        'f3_t' => 'Modelli salvati illimitati', 'f3_d' => 'Salva ogni esercizio della tua routine.',
        'btn' => '⭐ Vedi i piani Premium', 'btn_sub' => 'Ti sei guadagnato l’upgrade.',
    ],

    'trial_ending' => [
        'subject' => 'La tua prova Premium finisce tra {{trial_days_left}} giorni',
        'preheader' => 'Mantieni la tua pratica illimitata',
        'title' => 'La tua prova sta per finire',
        'p1' => 'La tua prova gratuita <strong>{{app_name}} Premium</strong> finisce il <strong>{{trial_ends_on}}</strong> — cioè tra {{trial_days_left}} giorni.',
        'p2' => 'Non verrà addebitato nulla: non abbiamo mai preso i dati della tua carta. Quando la prova finisce, il tuo account torna semplicemente al piano gratuito e tutta la tua cronologia di pratica resta esattamente com’è.',
        'p3' => 'Vuoi mantenere esercizi illimitati, pratica assistita dall’IA e tutto il resto che Premium sblocca? Puoi abbonarti in qualsiasi momento.',
        'btn' => '💳 Gestisci il mio piano', 'btn_sub' => 'Mantieni la pratica illimitata senza perdere il ritmo.',
    ],

    'trial_ended' => [
        'subject' => 'La tua prova Premium è terminata, {{user_first_name}}',
        'preheader' => 'Sei tornato al piano gratuito — i tuoi progressi sono al sicuro',
        'title' => 'Grazie per aver provato Premium',
        'p1' => 'La tua prova gratuita è terminata e il tuo account è tornato al <strong>piano gratuito</strong>. Non ti è stato addebitato nulla — non abbiamo mai chiesto una carta.',
        'p2' => 'Tutto ciò che hai praticato durante la prova è salvato: le tue statistiche, le serie e i progressi del Percorso di Apprendimento sono ancora lì.',
        'btn' => '⭐ Vedi i piani Premium', 'btn_sub' => 'Riprendi Premium con un clic.',
    ],

    // --- Insegnanti ---
    'welcome_teacher' => [
        'subject' => 'Benvenuto su {{app_name}} per insegnanti, {{user_first_name}}! 🎓',
        'preheader' => 'Configura il tuo profilo, fatti scoprire e inizia a insegnare',
        'badge' => '🎓 PER GLI INSEGNANTI',
        'title' => 'Benvenuto a bordo, {{user_first_name}}!',
        'subtitle' => 'Il tuo account insegnante <strong style="color:#7c3aed;">{{app_name}}</strong> è pronto. Ecco come configurarlo e iniziare a raggiungere gli studenti:',
        'f1_t' => 'Completa il tuo profilo pubblico', 'f1_d' => 'Aggiungi biografia, strumenti ed esperienza, poi invialo per l’approvazione.',
        'f2_t' => 'Imposta la tua disponibilità', 'f2_d' => 'Apri il tuo calendario così gli studenti possono prenotare lezioni direttamente.',
        'f3_t' => 'Connettiti con gli studenti', 'f3_d' => 'Invita i tuoi studenti o fatti scoprire nella directory.',
        'f4_t' => 'Pubblica contenuti', 'f4_d' => 'Condividi articoli e lezioni per costruire la tua reputazione.',
        'btn' => '🎓 Apri la dashboard insegnante', 'btn_sub' => 'Il tuo centro didattico — profilo, calendario, studenti e messaggi.',
        'promo_t' => 'Nuovo nell’insegnamento su {{app_name}}?', 'promo_s' => '“Allena l’orecchio, fai crescere il tuo studio.” Scopri come funzionano profili, prenotazioni e pagamenti.', 'promo_btn' => '📖 Come funziona insegnare',
        'ps' => 'Domande sul tuo account insegnante? Rispondi — saremo felici di aiutarti. 💜',
    ],

    'premium_intro_teacher' => [
        'subject' => 'Fai crescere il tuo insegnamento con {{app_name}} Premium, {{user_first_name}} ⭐',
        'preheader' => 'Prenotazioni, link di pagamento, pubblicazione di contenuti e profilo in evidenza',
        'badge' => '✦ PREMIUM INSEGNANTE',
        'title' => 'Fai crescere il tuo studio didattico',
        'subtitle' => 'Ciao {{user_first_name}} — sei su {{app_name}} da qualche giorno. Ecco cosa sblocca <strong style="color:#7c3aed;">Premium</strong> per gli insegnanti:',
        'f1_t' => 'Accetta prenotazioni e pagamenti', 'f1_d' => 'Lascia che gli studenti prenotino e paghino le lezioni con i tuoi link di pagamento.',
        'f2_t' => 'Pubblica contenuti illimitati', 'f2_d' => 'Articoli, lezioni e contenuti per mostrare la tua competenza.',
        'f3_t' => 'Profilo in evidenza e prioritario', 'f3_d' => 'Distinguiti nella directory degli insegnanti e fatti scoprire più in fretta.',
        'f4_t' => 'Strumenti di gestione studenti', 'f4_d' => 'Compiti, monitoraggio dei progressi e messaggistica in un unico posto.',
        'btn' => '⭐ Scopri Premium Insegnante', 'btn_sub' => 'Trasforma il tuo insegnamento in uno studio fiorente.',
        'p2' => 'Vuoi prima il quadro completo? <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">Scopri come funziona insegnare</a>.',
    ],

    'trial_ending_teacher' => [
        'subject' => 'La tua prova Premium Insegnante finisce tra {{trial_days_left}} giorni',
        'preheader' => 'Mantieni prenotazioni, pagamenti e pubblicazione di contenuti',
        'title' => 'La tua prova insegnante sta per finire',
        'p1' => 'La tua prova gratuita <strong>{{app_name}} Premium Insegnante</strong> finisce il <strong>{{trial_ends_on}}</strong> — tra {{trial_days_left}} giorni.',
        'p2' => 'Non verrà addebitato nulla: non abbiamo mai preso i dati della tua carta. Quando la prova finisce, il tuo account insegnante torna a <strong>Basic</strong> e funzioni come prenotazioni, link di pagamento e pubblicazione di contenuti si mettono in pausa — ma il tuo profilo e i tuoi studenti restano esattamente com’erano.',
        'btn' => '💳 Mantieni Premium Insegnante', 'btn_sub' => 'Non perdere prenotazioni e contenuti.',
    ],

    'trial_ended_teacher' => [
        'subject' => 'La tua prova Premium Insegnante è terminata, {{user_first_name}}',
        'preheader' => 'Il tuo profilo da insegnante è al sicuro — sei tornato a Basic',
        'title' => 'La tua prova insegnante è terminata',
        'p1' => 'La tua prova gratuita è terminata e il tuo account insegnante è tornato a <strong>Basic</strong>. Non ti è stato addebitato nulla — non abbiamo mai chiesto una carta.',
        'p2' => 'Il tuo profilo pubblico, i tuoi studenti e i tuoi messaggi sono al sicuro. Prenotazioni, link di pagamento e pubblicazione di contenuti sono pronti a riattivarsi non appena passi a un piano superiore.',
        'btn' => '⭐ Scopri Premium Insegnante', 'btn_sub' => 'Riprendi i tuoi strumenti di studio con un clic.',
    ],

    // --- Scuole ---
    'welcome_school' => [
        'subject' => 'Benvenuto su {{app_name}} per le scuole, {{user_first_name}}! 🏫',
        'preheader' => 'Configura la tua scuola, aggiungi i tuoi insegnanti e gestisci tutto in un unico posto',
        'badge' => '🏫 PER LE SCUOLE',
        'title' => 'Benvenuto a bordo, {{user_first_name}}!',
        'subtitle' => 'Il tuo account scuola <strong style="color:#7c3aed;">{{app_name}}</strong> è pronto. Ecco come configurarlo e portare a bordo i tuoi insegnanti:',
        'f1_t' => 'Configura il profilo della scuola', 'f1_d' => 'Aggiungi i dati e l’identità della tua scuola, poi invialo per l’approvazione.',
        'f2_t' => 'Aggiungi i tuoi insegnanti', 'f2_d' => 'Invita o collega insegnanti membri e gestiscili in un unico pannello.',
        'f3_t' => 'Gestisci le iscrizioni', 'f3_d' => 'Gestisci i rapporti con gli insegnanti, gli inviti e gli accessi in modo centralizzato.',
        'f4_t' => 'Fatti scoprire', 'f4_d' => 'Metti in mostra la tua scuola nella directory pubblica.',
        'btn' => '🏫 Apri il pannello scuola', 'btn_sub' => 'Il tuo centro scuola — profilo, insegnanti e iscrizioni.',
        'promo_t' => 'Nuovo su {{app_name}} per le scuole?', 'promo_s' => '“Un’unica casa per tutta la tua scuola di musica.” Scopri come collaborano scuole e insegnanti.', 'promo_btn' => '📖 Come funzionano le scuole',
        'ps' => 'Ti serve una mano per configurare la tua scuola? Rispondi — ti aiutiamo a iniziare. 💜',
    ],

    'premium_intro_school' => [
        'subject' => 'Sblocca {{app_name}} Premium per la tua scuola, {{user_first_name}} ⭐',
        'preheader' => 'Insegnanti illimitati, identità della scuola e visibilità prioritaria',
        'badge' => '✦ PREMIUM SCUOLA',
        'title' => 'Tutto ciò di cui la tua scuola ha bisogno',
        'subtitle' => 'Ciao {{user_first_name}} — ecco cosa sblocca <strong style="color:#7c3aed;">Premium</strong> per la tua scuola su {{app_name}}:',
        'f1_t' => 'Insegnanti membri illimitati', 'f1_d' => 'Aggiungi alla tua scuola tutti gli insegnanti che ti servono.',
        'f2_t' => 'Identità della scuola', 'f2_d' => 'Presenta la tua scuola con la tua identità in tutto Harmoniva.',
        'f3_t' => 'Visibilità prioritaria', 'f3_d' => 'Appari più in alto e fatti scoprire nella directory.',
        'f4_t' => 'Supervisione e strumenti', 'f4_d' => 'Gestisci insegnanti, iscrizioni e attività da un unico pannello.',
        'btn' => '⭐ Scopri Premium Scuola', 'btn_sub' => 'Tutto ciò che serve alla tua scuola di musica per crescere.',
        'p2' => 'Vuoi prima dare un’occhiata? <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">Come funzionano le scuole su {{app_name}}</a>.',
    ],

    'trial_ending_school' => [
        'subject' => 'La tua prova Premium Scuola finisce tra {{trial_days_left}} giorni',
        'preheader' => 'Mantieni insegnanti illimitati e l’identità della tua scuola',
        'title' => 'La tua prova scuola sta per finire',
        'p1' => 'La tua prova gratuita <strong>{{app_name}} Premium Scuola</strong> finisce il <strong>{{trial_ends_on}}</strong> — tra {{trial_days_left}} giorni.',
        'p2' => 'Non verrà addebitato nulla: non abbiamo mai preso i dati della tua carta. Quando la prova finisce, il tuo account scuola torna a <strong>Basic</strong> — ma il profilo della tua scuola, i tuoi insegnanti e le tue iscrizioni restano esattamente com’erano.',
        'btn' => '💳 Mantieni Premium Scuola', 'btn_sub' => 'Conserva i tuoi insegnanti e la tua identità.',
    ],

    'trial_ended_school' => [
        'subject' => 'La tua prova Premium Scuola è terminata, {{user_first_name}}',
        'preheader' => 'Il profilo della tua scuola è al sicuro — sei tornato a Basic',
        'title' => 'La tua prova scuola è terminata',
        'p1' => 'La tua prova gratuita è terminata e il tuo account scuola è tornato a <strong>Basic</strong>. Non ti è stato addebitato nulla — non abbiamo mai chiesto una carta.',
        'p2' => 'Il profilo della tua scuola, i tuoi insegnanti e le tue iscrizioni sono al sicuro. Le funzioni Premium della scuola sono pronte a riattivarsi non appena passi a un piano superiore.',
        'btn' => '⭐ Scopri Premium Scuola', 'btn_sub' => 'Riattiva gli strumenti della tua scuola con un clic.',
    ],

];
