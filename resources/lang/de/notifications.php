<?php

return [

    'appointment' => [
        'status_subject' => 'Termin-Update — :when',
        'status' => [
            'confirmed' => 'Deine Stunde am :when ist bestätigt.',
            'rejected' => 'Die Terminanfrage für den :when wurde abgelehnt.',
            'cancelled_teacher' => 'Die Stunde am :when wurde von der Lehrkraft abgesagt.',
            'cancelled_student' => 'Die Stunde am :when wurde vom Schüler abgesagt.',
            'reschedule' => 'Für die Stunde am :when wurde eine neue Zeit angefragt.',
            'completed' => 'Die Stunde am :when wurde als abgeschlossen markiert.',
            'no_show' => 'Die Stunde am :when wurde als Nichterscheinen markiert.',
            'default' => 'Der Status deines Termins hat sich geändert.',
        ],
        'lesson_link' => 'Stunden-Link: :url',
        'view' => 'Termin ansehen',
        'request_subject' => 'Neue Terminanfrage von :name',
        'request_line' => ':name hat eine Stunde am :when angefragt.',
        'topic' => 'Thema: :topic',
        'review' => 'Anfrage prüfen',
    ],

    'verify' => [
        'subject' => 'Bestätige deine E-Mail-Adresse',
        'line1' => 'Bitte bestätige deine E-Mail-Adresse, um dein :app-Konto zu aktivieren.',
        'action' => 'E-Mail-Adresse bestätigen',
        'line2' => 'Wenn du kein Konto erstellt hast, ist nichts weiter zu tun.',
    ],

    'invite' => [
        'teacher_subject' => ':name hat dich zu Harmoniva eingeladen',
        'school_subject' => ':name hat dich eingeladen, seiner Schule auf Harmoniva beizutreten',
        'heading' => 'Du bist zu Harmoniva eingeladen 🎵',
        'teacher_intro' => '**:name** hat dich eingeladen, Harmoniva als Schüler beizutreten.',
        'school_intro' => '**:name** hat dich eingeladen, seiner Musikschule auf Harmoniva als Lehrkraft beizutreten.',
        'teacher_body' => 'Harmoniva ist eine Plattform für Musikbildung mit Gehörtraining, Musiktheorie-Übungen und geführten Lernpfaden. Sobald ihr verbunden seid, kann deine Lehrkraft dir Aufgaben zuweisen und deinen Fortschritt verfolgen.',
        'school_body' => 'Harmoniva ist eine Plattform für Musikbildung mit Gehörtraining, Musiktheorie-Übungen und geführten Lernpfaden. Als Mitglieds-Lehrkraft erhältst du das komplette Lehrkraft-Toolset – Schüler, Kurse, Aufgaben, Nachrichten und einen Buchungskalender – und deine Schule kann dich bei der Verwaltung deiner Schüler unterstützen.',
        'accept' => 'Einladung annehmen',
        'expires' => 'Diese Einladung läuft am :date ab.',
        'ignore' => 'Falls du diese Einladung nicht erwartet hast, kannst du diese E-Mail einfach ignorieren.',
        'thanks' => 'Danke,',
    ],

];
