<?php

return [

    'appointment' => [
        'status_subject' => 'Aggiornamento appuntamento — :when',
        'status' => [
            'confirmed' => 'La tua lezione del :when è confermata.',
            'rejected' => 'La richiesta di appuntamento per il :when è stata rifiutata.',
            'cancelled_teacher' => "La lezione del :when è stata annullata dall'insegnante.",
            'cancelled_student' => 'La lezione del :when è stata annullata dallo studente.',
            'reschedule' => 'È stato richiesto un nuovo orario per la lezione del :when.',
            'completed' => 'La lezione del :when è stata contrassegnata come completata.',
            'no_show' => 'La lezione del :when è stata contrassegnata come assenza.',
            'default' => 'Lo stato del tuo appuntamento è cambiato.',
        ],
        'lesson_link' => 'Link della lezione: :url',
        'view' => 'Visualizza appuntamento',
        'request_subject' => 'Nuova richiesta di appuntamento da :name',
        'request_line' => ':name ha richiesto una lezione il :when.',
        'topic' => 'Argomento: :topic',
        'review' => 'Esamina la richiesta',
    ],

    'verify' => [
        'preheader' => 'Un clic e il tuo account Harmoniva è attivo',
        'title' => 'Conferma la tua e-mail',
        'btn_sub' => 'Il link è valido per 60 minuti.',
        'fallback' => 'Il pulsante non funziona? Copia questo link nel browser:',
        'subject' => 'Verifica il tuo indirizzo e-mail',
        'line1' => 'Conferma il tuo indirizzo e-mail per attivare il tuo account :app.',
        'action' => 'Verifica e-mail',
        'line2' => 'Se non hai creato un account, non devi fare nulla.',
    ],

    'invite' => [
        'teacher_subject' => ':name ti ha invitato su Harmoniva',
        'school_subject' => ':name ti ha invitato a unirti alla sua scuola su Harmoniva',
        'heading' => 'Sei invitato su Harmoniva 🎵',
        'teacher_intro' => '**:name** ti ha invitato a unirti a Harmoniva come suo studente.',
        'school_intro' => '**:name** ti ha invitato a unirti alla sua scuola di musica su Harmoniva come insegnante.',
        'teacher_body' => "Harmoniva è una piattaforma di educazione musicale con allenamento dell'orecchio, pratica di teoria musicale e percorsi di apprendimento guidati. Una volta collegati, il tuo insegnante può assegnarti compiti e seguire i tuoi progressi.",
        'school_body' => "Harmoniva è una piattaforma di educazione musicale con allenamento dell'orecchio, pratica di teoria musicale e percorsi di apprendimento guidati. Come insegnante membro ottieni l'intero set di strumenti per insegnare — studenti, classi, compiti, messaggistica e un calendario di prenotazione — e la tua scuola può aiutarti nella gestione dei tuoi studenti.",
        'accept' => "Accetta l'invito",
        'expires' => 'Questo invito scade il :date.',
        'ignore' => 'Se non ti aspettavi questo invito, puoi ignorare questa e-mail senza problemi.',
        'thanks' => 'Grazie,',
    ],

];
