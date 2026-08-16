<?php

/**
 * System-E-Mail-Texte (Email-Center-Vorlagen). Die {{placeholder}}-Felder
 * werden vom TemplateRenderer je Empfänger ersetzt — in Übersetzungen
 * unverändert lassen. Auch Inline-HTML (<strong>, <a href>) beibehalten.
 */
return [

    'footer' => [
        'manage_prefs' => 'E-Mail-Einstellungen verwalten',
        'unsubscribe' => 'Abmelden',
    ],

    'hi' => 'Hallo {{user_first_name}},',
    'guide_block' => [
        'title' => 'Neu im Gehörtraining? Fang hier an.',
        'slogan' => '„Trainiere klüger, nicht härter.“ Unser Schritt-für-Schritt-Leitfaden begleitet dich vom ersten Intervall bis zum flüssigen Hören.',
        'button' => '📖 Nutzerleitfaden lesen',
    ],

    'welcome' => [
        'subject' => 'Willkommen bei {{app_name}}, {{user_first_name}}! 🎵',
        'preheader' => 'Dein musikalisches Gehör startet heute ins Training – das alles schaltest du frei',
        'title' => 'Willkommen an Bord, {{user_first_name}}!',
        'subtitle' => 'Du bist gerade {{app_name}} beigetreten – dem angenehmsten Weg, dein musikalisches Gehör zu trainieren. Das erwartet dich:',
        'f1_t' => 'Mach den Einstufungstest', 'f1_d' => 'Wir passen einen persönlichen Lernpfad genau an dein Niveau an.',
        'f2_t' => 'Trainiere mit echtem Audio', 'f2_d' => 'Einzeltöne, Intervalle, Akkorde, Tonleitern, Rhythmus und melodisches Diktat.',
        'f3_t' => 'Verfolge jede Sitzung', 'f3_d' => 'Genauigkeit, Serien und Fortschrittsdiagramme bringen dich voran.',
        'f4_t' => 'KI-gestütztes Üben', 'f4_d' => 'Clevere Übungen, die deine Schwachstellen gezielt angehen (Premium).',
        'btn' => '🚀 Training starten', 'btn_sub' => 'Keine Einrichtung nötig – starte direkt deine erste Sitzung.',
        'ps' => 'Fragen? Antworte einfach auf diese E-Mail – ein echter Mensch liest jede. 💜',
    ],

    'first_exercise' => [
        'subject' => '{{user_first_name}}, deine erste Übung wartet 🎧',
        'preheader' => 'Dein Gehör zu trainieren dauert nur 5 Minuten',
        'title' => 'Bereit für deine erste Sitzung?',
        'p1' => 'Du hast dein {{app_name}}-Konto vor ein paar Tagen erstellt, aber noch keine Übung ausprobiert. Die allererste dauert weniger als fünf Minuten – und sie bringt dein Gehör in Schwung.',
        'btn' => '🎧 Erste Übung ausprobieren', 'btn_sub' => 'Weniger als 5 Minuten. Das Schwerste ist der Anfang.',
        'p2' => 'Nicht sicher, wo du anfangen sollst? Der <a href="{{app_url}}/learn" style="color:#7c3aed;font-weight:600;">Lernpfad</a> führt dich Schritt für Schritt, oder wirf zuerst einen Blick in den <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">Nutzerleitfaden</a>.',
    ],

    'learning_path' => [
        'subject' => 'Dein Lernpfad vermisst dich, {{user_first_name}} 🎼',
        'preheader' => 'Mach genau dort weiter, wo du aufgehört hast',
        'title' => 'Mach dort weiter, wo du aufgehört hast',
        'p1' => 'Dein Gehör wurde schärfer – lass diesen Fortschritt nicht verblassen. Dein Lernpfad ist genau da, wo du ihn verlassen hast, Serien inklusive, bereit, wann immer du es bist.',
        'btn' => '🎼 Lernpfad fortsetzen', 'btn_sub' => 'Schon eine kurze Sitzung hält den Schwung.',
        'p2' => '🔥 Beständigkeit schlägt Intensität. Fünf konzentrierte Minuten heute sind ein Gewinn.',
    ],

    'weekly_progress' => [
        'subject' => 'Deine Woche bei {{app_name}}: {{weekly_sessions}} Sitzungen 📈',
        'preheader' => 'Dein wöchentlicher Gehörtraining-Rückblick',
        'title' => 'Deine Woche im Rückblick',
        'subtitle' => 'Gute Arbeit diese Woche, {{user_first_name}}. Hier der Rückblick:',
        'sessions' => 'Sitzungen', 'accuracy' => 'Genauigkeit', 'minutes' => 'Minuten',
        'btn' => '📈 Bleib dran', 'btn_sub' => 'Kleine Wochen summieren sich zu einem trainierten Gehör.',
    ],

    're_engagement' => [
        'subject' => 'Wir haben deinen Fortschritt gespeichert, {{user_first_name}} 🎹',
        'preheader' => 'Dein Gehörtraining-Fortschritt ist sicher – komm jederzeit zurück',
        'title' => 'Dein Fortschritt ist bei uns sicher',
        'p1' => 'Deine letzte Übungssitzung bei {{app_name}} ist eine Weile her. Die gute Nachricht: Deine Statistiken, Serien und dein Lernpfad-Fortschritt sind genau dort gespeichert, wo du sie verlassen hast.',
        'btn' => '🎹 Training fortsetzen', 'btn_sub' => 'Fünf Minuten heute sind mehr wert als eine Stunde irgendwann.',
    ],

    'premium_intro' => [
        'subject' => 'Lerne {{app_name}} Premium kennen, {{user_first_name}} ⭐',
        'preheader' => 'Unbegrenztes Üben, KI-Coaching und mehr – sieh, was Premium bringt',
        'badge' => '✦ PREMIUM',
        'title' => 'Bring dein Training weiter',
        'subtitle' => 'Hallo {{user_first_name}} – du hast {{app_name}} ein paar Tage lang erkundet. Das schaltet <strong style="color:#7c3aed;">Premium</strong> frei, wann immer du bereit bist:',
        'f1_t' => 'Unbegrenzte tägliche Übungen', 'f1_d' => 'Kein 3-pro-Tag-Limit mehr – übe so viel, wie dein Gehör will.',
        'f2_t' => 'KI-gestütztes Üben', 'f2_d' => 'Übungen rund um deine persönlichen Schwachstellen.',
        'f3_t' => 'Unbegrenzt gespeicherte Vorlagen', 'f3_d' => 'Halte jede Lieblingsübung nur einen Fingertipp entfernt.',
        'f4_t' => 'Vollständiges melodisches Diktat', 'f4_d' => 'Die komplette Diktat-Engine mit Rhythmus und tonalen Melodien.',
        'btn' => '⭐ Premium entdecken', 'btn_sub' => 'Jederzeit upgraden. Jederzeit kündigen.',
        'p2' => 'Neugierig, wie alles zusammenpasst? Der <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">Nutzerleitfaden</a> zeigt jede Funktion in Aktion.',
    ],

    'premium_upsell' => [
        'subject' => 'Du bist dem Gratis-Tarif entwachsen, {{user_first_name}} ⭐',
        'preheader' => 'Unbegrenzte Übungen, KI-Modus und mehr mit Premium',
        'title' => 'Du gibst dir Mühe',
        'subtitle' => 'Hallo {{user_first_name}} – du übst beständig. Genau so trainiert man das Gehör. Das würde dich weiterbringen:',
        'f1_t' => 'Unbegrenzte tägliche Übungen', 'f1_d' => 'Du stößt ständig ans Gratis-Limit von 3 pro Tag – heb es ganz auf.',
        'f2_t' => 'KI-gestütztes Üben', 'f2_d' => 'Zugeschnitten auf genau die Intervalle und Akkorde, die du am häufigsten verfehlst.',
        'f3_t' => 'Unbegrenzt gespeicherte Vorlagen', 'f3_d' => 'Speichere jede Übung deiner Routine.',
        'btn' => '⭐ Premium-Tarife ansehen', 'btn_sub' => 'Du hast dir das Upgrade verdient.',
    ],

    'trial_ending' => [
        'subject' => 'Deine Premium-Testphase endet in {{trial_days_left}} Tagen',
        'preheader' => 'Behalte dein unbegrenztes Üben',
        'title' => 'Deine Testphase ist fast vorbei',
        'p1' => 'Deine kostenlose <strong>{{app_name}} Premium</strong>-Testphase endet am <strong>{{trial_ends_on}}</strong> – also in {{trial_days_left}} Tagen.',
        'p2' => 'Es wird nichts berechnet: Wir haben nie deine Kartendaten erfasst. Wenn die Testphase endet, kehrt dein Konto einfach zum Gratis-Tarif zurück, und dein gesamter Übungsverlauf bleibt genau erhalten.',
        'p3' => 'Möchtest du unbegrenzte Übungen, KI-gestütztes Üben und alles andere behalten, was Premium freischaltet? Du kannst jederzeit abonnieren.',
        'btn' => '💳 Meinen Tarif verwalten', 'btn_sub' => 'Behalte unbegrenztes Üben ohne Unterbrechung.',
    ],

    'trial_ended' => [
        'subject' => 'Deine Premium-Testphase ist beendet, {{user_first_name}}',
        'preheader' => 'Du bist zurück im Gratis-Tarif – dein Fortschritt ist sicher',
        'title' => 'Danke, dass du Premium getestet hast',
        'p1' => 'Deine kostenlose Testphase ist beendet und dein Konto ist zurück im <strong>Gratis-Tarif</strong>. Es wurde nichts berechnet – wir haben nie nach einer Karte gefragt.',
        'p2' => 'Alles, was du während der Testphase geübt hast, ist gespeichert: Deine Statistiken, Serien und dein Lernpfad-Fortschritt sind weiterhin da.',
        'btn' => '⭐ Premium-Tarife ansehen', 'btn_sub' => 'Nimm Premium mit einem Klick wieder auf.',
    ],

    // --- Lehrkräfte ---
    'welcome_teacher' => [
        'subject' => 'Willkommen bei {{app_name}} für Lehrkräfte, {{user_first_name}}! 🎓',
        'preheader' => 'Richte dein Profil ein, werde entdeckt und beginne zu unterrichten',
        'badge' => '🎓 FÜR LEHRKRÄFTE',
        'title' => 'Willkommen an Bord, {{user_first_name}}!',
        'subtitle' => 'Dein <strong style="color:#7c3aed;">{{app_name}}</strong>-Lehrkonto ist bereit. So richtest du es ein und erreichst Schülerinnen und Schüler:',
        'f1_t' => 'Vervollständige dein öffentliches Profil', 'f1_d' => 'Füge Biografie, Instrumente und Erfahrung hinzu und reiche es zur Freigabe ein.',
        'f2_t' => 'Lege deine Verfügbarkeit fest', 'f2_d' => 'Öffne deinen Kalender, damit Schüler direkt Stunden buchen können.',
        'f3_t' => 'Vernetze dich mit Schülern', 'f3_d' => 'Lade eigene Schüler ein oder werde im Verzeichnis entdeckt.',
        'f4_t' => 'Veröffentliche Inhalte', 'f4_d' => 'Teile Artikel und Lektionen, um deinen Ruf aufzubauen.',
        'btn' => '🎓 Lehrkraft-Dashboard öffnen', 'btn_sub' => 'Deine Unterrichtszentrale – Profil, Kalender, Schüler und Nachrichten.',
        'promo_t' => 'Neu im Unterrichten bei {{app_name}}?', 'promo_s' => '„Trainiere das Gehör, lass dein Studio wachsen.“ Sieh, wie Profile, Buchungen und Zahlungen funktionieren.', 'promo_btn' => '📖 So funktioniert Unterrichten',
        'ps' => 'Fragen zu deinem Lehrkonto? Antworte einfach – wir helfen gern. 💜',
    ],

    'premium_intro_teacher' => [
        'subject' => 'Lass deinen Unterricht mit {{app_name}} Premium wachsen, {{user_first_name}} ⭐',
        'preheader' => 'Buchungen, Zahlungslinks, Inhaltsveröffentlichung und ein hervorgehobenes Profil',
        'badge' => '✦ LEHRKRAFT-PREMIUM',
        'title' => 'Lass dein Unterrichtsstudio wachsen',
        'subtitle' => 'Hallo {{user_first_name}} – du bist seit ein paar Tagen bei {{app_name}}. Das schaltet <strong style="color:#7c3aed;">Premium</strong> für Lehrkräfte frei:',
        'f1_t' => 'Buchungen & Zahlungen annehmen', 'f1_d' => 'Lass Schüler Stunden mit deinen eigenen Zahlungslinks buchen und bezahlen.',
        'f2_t' => 'Unbegrenzt Inhalte veröffentlichen', 'f2_d' => 'Artikel, Lektionen und Medien, um deine Kompetenz zu zeigen.',
        'f3_t' => 'Hervorgehobenes, priorisiertes Profil', 'f3_d' => 'Stich im Lehrkräfte-Verzeichnis heraus und werde schneller entdeckt.',
        'f4_t' => 'Werkzeuge zur Schülerverwaltung', 'f4_d' => 'Aufgaben, Fortschrittsverfolgung und Nachrichten an einem Ort.',
        'btn' => '⭐ Lehrkraft-Premium ansehen', 'btn_sub' => 'Mach aus deinem Unterricht ein blühendes Studio.',
        'p2' => 'Willst du zuerst das ganze Bild sehen? <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">Sieh, wie Unterrichten funktioniert</a>.',
    ],

    'trial_ending_teacher' => [
        'subject' => 'Deine Lehrkraft-Premium-Testphase endet in {{trial_days_left}} Tagen',
        'preheader' => 'Behalte Buchungen, Zahlungen und Inhaltsveröffentlichung',
        'title' => 'Deine Lehrkraft-Testphase ist fast vorbei',
        'p1' => 'Deine kostenlose <strong>{{app_name}} Lehrkraft-Premium</strong>-Testphase endet am <strong>{{trial_ends_on}}</strong> – in {{trial_days_left}} Tagen.',
        'p2' => 'Es wird nichts berechnet: Wir haben nie deine Kartendaten erfasst. Wenn die Testphase endet, kehrt dein Lehrkonto zu <strong>Basic</strong> zurück, und Funktionen wie Buchungen, Zahlungslinks und Inhaltsveröffentlichung pausieren – aber dein Profil und deine Schüler bleiben genau erhalten.',
        'btn' => '💳 Lehrkraft-Premium behalten', 'btn_sub' => 'Verliere nicht deine Buchungen und Inhalte.',
    ],

    'trial_ended_teacher' => [
        'subject' => 'Deine Lehrkraft-Premium-Testphase ist beendet, {{user_first_name}}',
        'preheader' => 'Dein Lehrprofil ist sicher – du bist zurück bei Basic',
        'title' => 'Deine Lehrkraft-Testphase ist beendet',
        'p1' => 'Deine kostenlose Testphase ist beendet und dein Lehrkonto ist zurück bei <strong>Basic</strong>. Es wurde nichts berechnet – wir haben nie nach einer Karte gefragt.',
        'p2' => 'Dein öffentliches Profil, deine Schüler und deine Nachrichten sind sicher. Buchungen, Zahlungslinks und Inhaltsveröffentlichung lassen sich jederzeit wieder aktivieren, sobald du upgradest.',
        'btn' => '⭐ Lehrkraft-Premium ansehen', 'btn_sub' => 'Hol dir deine Studio-Werkzeuge mit einem Klick zurück.',
    ],

    // --- Schulen ---
    'welcome_school' => [
        'subject' => 'Willkommen bei {{app_name}} für Schulen, {{user_first_name}}! 🏫',
        'preheader' => 'Richte deine Schule ein, füge deine Lehrkräfte hinzu und verwalte alles an einem Ort',
        'badge' => '🏫 FÜR SCHULEN',
        'title' => 'Willkommen an Bord, {{user_first_name}}!',
        'subtitle' => 'Dein <strong style="color:#7c3aed;">{{app_name}}</strong>-Schulkonto ist bereit. So richtest du es ein und holst deine Lehrkräfte an Bord:',
        'f1_t' => 'Richte dein Schulprofil ein', 'f1_d' => 'Füge die Angaben und das Branding deiner Schule hinzu und reiche es zur Freigabe ein.',
        'f2_t' => 'Füge deine Lehrkräfte hinzu', 'f2_d' => 'Lade Mitglieds-Lehrkräfte ein oder verbinde sie und verwalte sie in einem Panel.',
        'f3_t' => 'Verwalte Mitgliedschaften', 'f3_d' => 'Steuere Lehrkraft-Beziehungen, Einladungen und Zugriff zentral.',
        'f4_t' => 'Werde entdeckt', 'f4_d' => 'Präsentiere deine Schule im öffentlichen Verzeichnis.',
        'btn' => '🏫 Schul-Panel öffnen', 'btn_sub' => 'Deine Schulzentrale – Profil, Lehrkräfte und Mitgliedschaften.',
        'promo_t' => 'Neu bei {{app_name}} für Schulen?', 'promo_s' => '„Ein Zuhause für deine ganze Musikschule.“ Sieh, wie Schulen und Lehrkräfte zusammenarbeiten.', 'promo_btn' => '📖 So funktionieren Schulen',
        'ps' => 'Brauchst du Hilfe beim Einrichten deiner Schule? Antworte einfach – wir helfen dir beim Start. 💜',
    ],

    'premium_intro_school' => [
        'subject' => 'Schalte {{app_name}} Premium für deine Schule frei, {{user_first_name}} ⭐',
        'preheader' => 'Unbegrenzte Lehrkräfte, Schul-Branding und bevorzugte Sichtbarkeit',
        'badge' => '✦ SCHUL-PREMIUM',
        'title' => 'Alles, was deine Schule braucht',
        'subtitle' => 'Hallo {{user_first_name}} – das schaltet <strong style="color:#7c3aed;">Premium</strong> für deine Schule bei {{app_name}} frei:',
        'f1_t' => 'Unbegrenzte Mitglieds-Lehrkräfte', 'f1_d' => 'Füge deiner Schule so viele Lehrkräfte hinzu, wie du brauchst.',
        'f2_t' => 'Schul-Branding', 'f2_d' => 'Präsentiere deine Schule mit deiner eigenen Identität in ganz Harmoniva.',
        'f3_t' => 'Bevorzugte Sichtbarkeit', 'f3_d' => 'Erscheine weiter oben und werde im Verzeichnis entdeckt.',
        'f4_t' => 'Überblick & Werkzeuge', 'f4_d' => 'Verwalte Lehrkräfte, Mitgliedschaften und Aktivität von einem Panel aus.',
        'btn' => '⭐ Schul-Premium ansehen', 'btn_sub' => 'Alles, was deine Musikschule zum Wachsen braucht.',
        'p2' => 'Willst du es zuerst sehen? <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">So funktionieren Schulen bei {{app_name}}</a>.',
    ],

    'trial_ending_school' => [
        'subject' => 'Deine Schul-Premium-Testphase endet in {{trial_days_left}} Tagen',
        'preheader' => 'Behalte unbegrenzte Lehrkräfte und Schul-Branding',
        'title' => 'Deine Schul-Testphase ist fast vorbei',
        'p1' => 'Deine kostenlose <strong>{{app_name}} Schul-Premium</strong>-Testphase endet am <strong>{{trial_ends_on}}</strong> – in {{trial_days_left}} Tagen.',
        'p2' => 'Es wird nichts berechnet: Wir haben nie deine Kartendaten erfasst. Wenn die Testphase endet, kehrt dein Schulkonto zu <strong>Basic</strong> zurück – aber dein Schulprofil, deine Lehrkräfte und deine Mitgliedschaften bleiben genau erhalten.',
        'btn' => '💳 Schul-Premium behalten', 'btn_sub' => 'Behalte deine Lehrkräfte und dein Branding.',
    ],

    'trial_ended_school' => [
        'subject' => 'Deine Schul-Premium-Testphase ist beendet, {{user_first_name}}',
        'preheader' => 'Dein Schulprofil ist sicher – du bist zurück bei Basic',
        'title' => 'Deine Schul-Testphase ist beendet',
        'p1' => 'Deine kostenlose Testphase ist beendet und dein Schulkonto ist zurück bei <strong>Basic</strong>. Es wurde nichts berechnet – wir haben nie nach einer Karte gefragt.',
        'p2' => 'Dein Schulprofil, deine Lehrkräfte und deine Mitgliedschaften sind sicher. Die Schul-Premium-Funktionen lassen sich jederzeit wieder aktivieren, sobald du upgradest.',
        'btn' => '⭐ Schul-Premium ansehen', 'btn_sub' => 'Reaktiviere die Werkzeuge deiner Schule mit einem Klick.',
    ],

    'first_exercise_teacher' => [
        'subject' => '{{user_first_name}}, füge deinen ersten Schüler hinzu 👥',
        'preheader' => 'Dein Lehrer-Dashboard ist bereit — es fehlen nur die Schüler',
        'title' => 'Bereit für deinen ersten Schüler?',
        'p1' => 'Du hast dein {{app_name}}-Lehrerkonto vor ein paar Tagen eröffnet, aber bisher ist noch kein Schüler dazugekommen. Eine Einladung dauert eine Minute: Schick den Link, und Übungen, Aufgaben und Fortschritt landen direkt in deinem Dashboard.',
        'btn' => '👥 Ersten Schüler einladen',
        'btn_sub' => 'Ein Link. Der Fortschritt erscheint automatisch.',
        'p2' => 'Unterrichtest du bereits anderswo? Hol deine bestehenden Schüler herüber — sie üben weiter, du bekommst die Daten.',
    ],

    'first_exercise_school' => [
        'subject' => '{{user_first_name}}, füge deine erste Lehrkraft hinzu 🏫',
        'preheader' => 'Dein Schul-Dashboard ist bereit — es fehlen nur die Lehrkräfte',
        'title' => 'Bereit für deine erste Lehrkraft?',
        'p1' => 'Du hast dein {{app_name}}-Schulkonto vor ein paar Tagen eröffnet, aber im Kollegium ist noch niemand eingetragen. Lade jemanden ein, und dessen Schüler, Unterricht und Fortschritt fließen automatisch in dein Schul-Dashboard.',
        'btn' => '🏫 Erste Lehrkraft einladen',
        'btn_sub' => 'Ein Link. Das Studio verbindet sich mit deiner Schule.',
        'p2' => 'Jede Lehrkraft bringt ihre Schüler mit — deine ganze Schule in einer Ansicht.',
    ],

    'learning_path_teacher' => [
        'subject' => 'Deine Schüler warten, {{user_first_name}} 📋',
        'preheader' => 'Schau in dein Studio — der Fortschritt sammelt sich',
        'title' => 'Dein Studio lief auch ohne dich weiter',
        'p1' => 'Bei dir war es eine ruhige Woche, aber deine Schüler haben weiter geübt. Ihre Einheiten, ihre Trefferquote und ihre Serien warten in deinem Dashboard — und oft reicht eine einzige Aufgabe, um jemanden über einen Stillstand hinwegzubringen.',
        'btn' => '📋 Schüler ansehen',
        'btn_sub' => 'Sieh, wer vorankommt und wer feststeckt.',
        'p2' => '🎯 Eine kurze Aufgabe heute ist mehr wert als eine lange nächsten Monat.',
    ],

    'learning_path_school' => [
        'subject' => 'Deine Schule lief weiter, {{user_first_name}} 📋',
        'preheader' => 'Deine Lehrkräfte und Schüler waren aktiv',
        'title' => 'Deine Schule lief auch ohne dich weiter',
        'p1' => 'Du warst eine Woche nicht da, aber deine Lehrkräfte und ihre Schüler haben weiter geübt. Unterrichtsaktivität, Schülerfortschritt und Kollegium sind in deinem Schul-Dashboard auf dem neuesten Stand.',
        'btn' => '📋 Schul-Dashboard öffnen',
        'btn_sub' => 'Jede Lehrkraft, jeder Schüler, eine Ansicht.',
        'p2' => '🎯 Ein kurzer wöchentlicher Blick genügt meist, um zu sehen, wer Unterstützung braucht.',
    ],

    'weekly_progress_teacher' => [
        'subject' => 'Dein Studio diese Woche: {{weekly_sessions}} Einheiten 📈',
        'preheader' => 'Wie deine Schüler diese Woche geübt haben',
        'title' => 'Dein Studio diese Woche',
        'subtitle' => 'So haben deine Schüler diese Woche geübt, {{user_first_name}}. Du hast {{weekly_assignments}} neue Aufgaben verschickt.',
        'm1' => 'Schüler',
        'm2' => 'Einheiten',
        'm3' => 'Trefferquote',
        'btn' => '📈 Lehrer-Dashboard öffnen',
        'btn_sub' => 'Schau nach, wer sich verbessert hat und wer einen Anstoß braucht.',
    ],

    'weekly_progress_school' => [
        'subject' => 'Deine Schule diese Woche: {{weekly_sessions}} Einheiten 📈',
        'preheader' => 'Wie deine Schule diese Woche geübt hat',
        'title' => 'Deine Schule diese Woche',
        'subtitle' => 'Die Woche in deiner Schule, {{user_first_name}} — durchschnittliche Trefferquote {{weekly_accuracy}}.',
        'm1' => 'Lehrkräfte',
        'm2' => 'Schüler',
        'm3' => 'Einheiten',
        'btn' => '📈 Schul-Dashboard öffnen',
        'btn_sub' => 'Auswertungen pro Lehrkraft und pro Schüler im Inneren.',
    ],

    're_engagement_teacher' => [
        'subject' => 'Dein Lehrerkonto ist noch da, {{user_first_name}} 🎓',
        'preheader' => 'Deine Schüler, Aufgaben und dein Profil sind gespeichert',
        'title' => 'Alles ist da, wo du es gelassen hast',
        'p1' => 'Es ist eine Weile her, dass du dein {{app_name}}-Lehrer-Dashboard geöffnet hast. Deine Schülerliste, deine Aufgaben, dein Profil und deine Bewertungen sind genau so, wie du sie verlassen hast — nichts abgelaufen, nichts verloren.',
        'btn' => '🎓 Zurück zum Unterrichten',
        'btn_sub' => 'Mach mit deinem Studio in einem Klick weiter.',
    ],

    're_engagement_school' => [
        'subject' => 'Dein Schulkonto ist noch da, {{user_first_name}} 🏫',
        'preheader' => 'Deine Lehrkräfte, Schüler und Einstellungen sind gespeichert',
        'title' => 'Alles ist da, wo du es gelassen hast',
        'p1' => 'Es ist eine Weile her, dass du dein {{app_name}}-Schul-Dashboard geöffnet hast. Dein Kollegium, deine Schülerdaten und dein Schulprofil sind genau so, wie du sie verlassen hast — nichts abgelaufen, nichts verloren.',
        'btn' => '🏫 Zurück zu deiner Schule',
        'btn_sub' => 'Mach dort weiter, wo deine Schule aufgehört hat.',
    ],

    'premium_upsell_teacher' => [
        'subject' => 'Dein Studio ist dem Gratis-Tarif entwachsen, {{user_first_name}} ⭐',
        'preheader' => 'Buchungen, Zahlungen und unbegrenzte Aufgaben mit Premium',
        'badge' => '⭐ LEHRER-PREMIUM',
        'title' => 'Du baust ein echtes Studio auf',
        'subtitle' => 'Hallo {{user_first_name}} — deine Schüler üben regelmäßig. Das fügt Premium dem Studio hinzu, das du bereits aufgebaut hast:',
        'f1_t' => 'Online-Buchungskalender',
        'f1_d' => 'Schüler buchen Unterricht in deinen freien Zeiten. Kein Hin und Her per Nachricht.',
        'f2_t' => 'Unbegrenzte Aufgaben',
        'f2_d' => 'Verschick so viele eigene Übungen, wie deine Schüler brauchen — ohne Tageslimit.',
        'f3_t' => 'Hervorgehobenes öffentliches Profil',
        'f3_d' => 'Steig im Lehrerverzeichnis nach oben und nimm Zahlungen über dein Profil entgegen.',
        'btn' => '⭐ Lehrer-Tarife ansehen',
        'btn_sub' => 'Gemacht für Lehrkräfte, die bereits unterrichten.',
        'p2' => 'Noch nicht so weit? Dein kostenloses Konto funktioniert weiter genau wie heute.',
    ],

    'premium_upsell_school' => [
        'subject' => 'Deine Schule ist dem Gratis-Tarif entwachsen, {{user_first_name}} ⭐',
        'preheader' => 'Unbegrenzte Lehrkräfte, eigenes Branding und Auswertungen mit Premium',
        'badge' => '⭐ SCHUL-PREMIUM',
        'title' => 'Deine Schule wächst',
        'subtitle' => 'Hallo {{user_first_name}} — deine Lehrkräfte sind aktiv und ihre Schüler üben. Das fügt Premium hinzu:',
        'f1_t' => 'Unbegrenzt viele Lehrkräfte',
        'f1_d' => 'Nimm dein ganzes Kollegium auf, ohne Limit pro Platz.',
        'f2_t' => 'Auswertungen für die ganze Schule',
        'f2_d' => 'Vergleiche Lehrkräfte und Gruppen und exportiere den Fortschritt für Eltern.',
        'f3_t' => 'Schulprofil mit eigenem Branding',
        'f3_d' => 'Dein Logo, deine Seite und ein besserer Platz im Schulverzeichnis.',
        'btn' => '⭐ Schul-Tarife ansehen',
        'btn_sub' => 'Gemacht für Schulen mit einem echten Kollegium.',
        'p2' => 'Noch nicht so weit? Dein kostenloses Konto funktioniert weiter genau wie heute.',
    ],

];
