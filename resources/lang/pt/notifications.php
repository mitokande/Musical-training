<?php

return [

    'appointment' => [
        'status_subject' => 'Atualização do agendamento — :when',
        'status' => [
            'confirmed' => 'Sua aula em :when está confirmada.',
            'rejected' => 'A solicitação de agendamento para :when foi recusada.',
            'cancelled_teacher' => 'A aula em :when foi cancelada pelo professor.',
            'cancelled_student' => 'A aula em :when foi cancelada pelo aluno.',
            'reschedule' => 'Um novo horário foi solicitado para a aula em :when.',
            'completed' => 'A aula em :when foi marcada como concluída.',
            'no_show' => 'A aula em :when foi marcada como ausência.',
            'default' => 'O status do seu agendamento mudou.',
        ],
        'lesson_link' => 'Link da aula: :url',
        'view' => 'Ver o agendamento',
        'request_subject' => 'Nova solicitação de agendamento de :name',
        'request_line' => ':name solicitou uma aula em :when.',
        'topic' => 'Tema: :topic',
        'review' => 'Revisar a solicitação',
    ],

    'verify' => [
        'subject' => 'Verifique seu endereço de e-mail',
        'line1' => 'Confirme seu endereço de e-mail para ativar sua conta do :app.',
        'action' => 'Verificar e-mail',
        'line2' => 'Se você não criou uma conta, nenhuma ação é necessária.',
    ],

    'invite' => [
        'teacher_subject' => ':name convidou você para o Harmoniva',
        'school_subject' => ':name convidou você para entrar na escola dele no Harmoniva',
        'heading' => 'Você foi convidado para o Harmoniva 🎵',
        'teacher_intro' => '**:name** convidou você para entrar no Harmoniva como aluno.',
        'school_intro' => '**:name** convidou você para entrar na escola de música dele no Harmoniva como professor.',
        'teacher_body' => 'O Harmoniva é uma plataforma de educação musical com treino de ouvido, prática de teoria musical e trilhas de aprendizado guiadas. Depois de conectados, seu professor pode passar tarefas e acompanhar seu progresso.',
        'school_body' => 'O Harmoniva é uma plataforma de educação musical com treino de ouvido, prática de teoria musical e trilhas de aprendizado guiadas. Como professor membro, você tem o conjunto completo de ferramentas de ensino — alunos, turmas, tarefas, mensagens e uma agenda de reservas — e sua escola pode apoiar você na gestão dos seus alunos.',
        'accept' => 'Aceitar o convite',
        'expires' => 'Este convite expira em :date.',
        'ignore' => 'Se você não esperava este convite, pode ignorar este e-mail com tranquilidade.',
        'thanks' => 'Obrigado,',
    ],

];
