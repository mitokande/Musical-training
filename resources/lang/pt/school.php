<?php

return [

    'nav' => [
        'role_teacher' => 'Escola de Música',
        'teachers' => 'Professores',
        'view_as_student' => 'Ver perfil público',
    ],

    'dashboard' => [
        'title' => 'Painel da Escola',
        'subtitle' => 'Gerencie sua escola, professores e alunos em um só lugar.',
        'stat_pending_students' => 'Aguardando aprovação',
        'stat_new_students_month' => 'Novos este mês',
        'teacher_stats' => 'Estatísticas de professores',
        'stat_active_teachers' => 'Professores ativos',
        'stat_pending_teachers' => 'Aguardando aprovação',
        'stat_member_students' => 'Seus alunos',
        'stat_member_classes' => 'Suas turmas',
        'stat_member_assignments' => 'Suas tarefas',
        'stat_member_avg_score' => 'Pontuação média',
    ],

    'profile' => [
        'title' => 'Perfil da Escola',
        'subtitle' => 'Gerencie o perfil público da sua escola.',
    ],

    'public' => [
        'school_badge' => 'Escola de Música',
        'message_teacher' => 'Enviar mensagem para a escola',
    ],

    'admin' => [
        'entity_school' => 'Escola de Música',
    ],

    'teachers' => [
        'title' => 'Professores',
        'subtitle' => 'Adicione professores à sua escola e gerencie-os junto com seus alunos.',
        'add_teacher' => 'Adicionar professor',
        'no_teachers' => 'Ainda não há professores. Convide seu primeiro professor para começar.',
        'active_since' => 'Membro desde :date',
        'student_count' => ':count alunos',
        'pending_approval' => 'Aguardando a aprovação do professor',
        'view_profile' => 'Ver',
        'remove_teacher' => 'Remover professor',
        'remove_confirm' => 'Remover este professor da sua escola? A conta e os alunos dele permanecem intactos.',
        'back_to_list' => 'Todos os professores',
        'public_profile' => 'Perfil público',
        'stat_students' => 'Alunos ativos',
        'stat_classes' => 'Turmas',
        'stat_assignments' => 'Tarefas',
        'their_students' => 'Alunos deste professor',
        'no_students' => 'Este professor ainda não tem alunos ativos.',
        'view_student' => 'Ver',
        'pending_invitations' => 'Convites pendentes',
        'no_invitations' => 'Nenhum convite pendente.',
        'search_users' => 'Buscar usuários',
        'invite_by_email' => 'Convidar por e-mail',
        'share_link' => 'Compartilhar link',
        'search_placeholder' => 'Nome, sobrenome ou e-mail exato',
        'send_request' => 'Enviar solicitação',
        'invite_name' => 'Nome (opcional)',
        'invite_email' => 'Endereço de e-mail',
        'send_invitation' => 'Enviar convite',
        'link_expires' => 'Expira em',
        'create_link' => 'Criar link',
        'copy_link' => 'Copiar link',
        'revoke' => 'Revogar',
        'status_relationship-requested' => 'Solicitação de associação enviada.',
        'status_invitation-sent' => 'E-mail de convite enviado.',
        'status_invitation-link-created' => 'Link de convite criado.',
        'status_invitation-revoked' => 'Convite revogado.',
        'status_relationship-revoked' => 'Professor removido da sua escola.',
        'error_self' => 'Você não pode adicionar sua própria conta como professor.',
        'error_target_school' => 'Contas de escola não podem ser adicionadas como professores.',
        'error_already_related' => 'Este usuário já é membro ou tem uma solicitação pendente.',
        'error_duplicate_invitation' => 'Já existe um convite pendente para este e-mail.',
        'error_limit_reached' => 'Seu plano permite até :limit professores. Faça upgrade para adicionar mais.',
    ],

    'my_schools' => [
        'title' => 'Minhas Escolas',
        'subtitle' => 'Escolas de música onde você ensina e solicitações de associação pendentes.',
        'no_schools' => 'Você ainda não é membro de nenhuma escola.',
        'pending' => 'Solicitação de associação — aguardando sua aprovação',
        'since' => 'Membro desde :date',
        'view_public_profile' => 'Ver perfil da escola',
        'approve' => 'Aceitar',
        'decline' => 'Recusar',
        'leave' => 'Sair da escola',
        'leave_confirm' => 'Sair desta escola? Você manterá sua conta e seus alunos.',
        'status_school-approved' => 'Você entrou na escola.',
        'status_school-declined' => 'Solicitação recusada.',
        'status_school-left' => 'Você saiu da escola.',
        'status_school-joined' => 'Você entrou na escola. Seu painel de professor está pronto.',
    ],

    'invitations' => [
        'title' => 'Convite da escola',
        'invited_you' => ':school convidou você para se juntar à escola de música como professor.',
        'accept' => 'Aceitar o convite',
        'decline_hint' => 'Se você não conhece esta escola, pode simplesmente ignorar este convite.',
        'unusable' => 'Este convite não é mais válido.',
    ],

];
