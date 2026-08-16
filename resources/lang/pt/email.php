<?php

/**
 * Textos dos e-mails do sistema (modelos do Email Center). Os campos
 * {{placeholder}} são preenchidos pelo TemplateRenderer para cada
 * destinatário: mantenha-os intactos. Mantenha também o HTML embutido
 * (<strong>, <a href>).
 */
return [

    'footer' => [
        'manage_prefs' => 'Gerenciar preferências de e-mail',
        'unsubscribe' => 'Cancelar inscrição',
    ],

    'hi' => 'Olá {{user_first_name}},',
    'guide_block' => [
        'title' => 'Novo no treino de ouvido? Comece por aqui.',
        'slogan' => '“Treine com inteligência, não com esforço.” Nosso guia passo a passo leva você do primeiro intervalo à escuta fluente.',
        'button' => '📖 Ler o guia do usuário',
    ],

    'welcome' => [
        'subject' => 'Bem-vindo ao {{app_name}}, {{user_first_name}}! 🎵',
        'preheader' => 'Seu ouvido musical começa a treinar hoje — veja tudo o que você desbloqueia',
        'title' => 'Bem-vindo a bordo, {{user_first_name}}!',
        'subtitle' => 'Você acabou de entrar no {{app_name}} — a forma mais agradável de treinar seu ouvido musical. Veja o que espera por você:',
        'f1_t' => 'Faça o teste de nível', 'f1_d' => 'Adaptamos uma Trilha de Aprendizado personalizada ao seu nível exato.',
        'f2_t' => 'Treine com áudio real', 'f2_d' => 'Notas isoladas, intervalos, acordes, escalas, ritmo e ditado melódico.',
        'f3_t' => 'Acompanhe cada sessão', 'f3_d' => 'Precisão, sequências e gráficos de progresso mantêm você avançando.',
        'f4_t' => 'Prática assistida por IA', 'f4_d' => 'Exercícios inteligentes focados nos seus pontos fracos (Premium).',
        'btn' => '🚀 Começar a treinar', 'btn_sub' => 'Sem configuração — vá direto para sua primeira sessão.',
        'ps' => 'Dúvidas? É só responder a este e-mail — uma pessoa de verdade lê cada um. 💜',
    ],

    'first_exercise' => [
        'subject' => '{{user_first_name}}, seu primeiro exercício está esperando 🎧',
        'preheader' => 'Treinar seu ouvido leva apenas 5 minutos',
        'title' => 'Pronto para sua primeira sessão?',
        'p1' => 'Você criou sua conta no {{app_name}} há alguns dias, mas ainda não experimentou nenhum exercício. O primeiro leva menos de cinco minutos — e é o que coloca seu ouvido em movimento.',
        'btn' => '🎧 Experimente seu primeiro exercício', 'btn_sub' => 'Menos de 5 minutos. O mais difícil é começar.',
        'p2' => 'Não sabe por onde começar? A <a href="{{app_url}}/learn" style="color:#7c3aed;font-weight:600;">Trilha de Aprendizado</a> guia você passo a passo, ou dê uma olhada primeiro no <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">guia do usuário</a>.',
    ],

    'learning_path' => [
        'subject' => 'Sua Trilha de Aprendizado sente sua falta, {{user_first_name}} 🎼',
        'preheader' => 'Retome exatamente de onde parou',
        'title' => 'Retome de onde parou',
        'p1' => 'Seu ouvido estava ficando mais afiado — não deixe esse progresso se apagar. Sua Trilha de Aprendizado está exatamente onde você deixou, com sequências e tudo, pronta quando você estiver.',
        'btn' => '🎼 Continuar a Trilha de Aprendizado', 'btn_sub' => 'Até uma sessão curta mantém o ritmo.',
        'p2' => '🔥 Constância vence intensidade. Cinco minutos concentrados hoje já são uma vitória.',
    ],

    'weekly_progress' => [
        'subject' => 'Sua semana no {{app_name}}: {{weekly_sessions}} sessões 📈',
        'preheader' => 'Seu resumo semanal de treino de ouvido',
        'title' => 'Sua semana em resumo',
        'subtitle' => 'Bom trabalho nesta semana, {{user_first_name}}. Aqui está o resumo:',
        'sessions' => 'Sessões', 'accuracy' => 'Precisão', 'minutes' => 'Minutos',
        'btn' => '📈 Continue assim', 'btn_sub' => 'Semanas pequenas somam um ouvido treinado.',
    ],

    're_engagement' => [
        'subject' => 'Guardamos seu progresso, {{user_first_name}} 🎹',
        'preheader' => 'Seu progresso de treino de ouvido está seguro — volte quando quiser',
        'title' => 'Seu progresso está seguro com a gente',
        'p1' => 'Já faz um tempo desde sua última sessão de prática no {{app_name}}. Boa notícia: suas estatísticas, sequências e o progresso na Trilha de Aprendizado estão salvos exatamente onde você parou.',
        'btn' => '🎹 Retomar o treino', 'btn_sub' => 'Cinco minutos hoje valem mais que uma hora algum dia.',
    ],

    'premium_intro' => [
        'subject' => 'Conheça o {{app_name}} Premium, {{user_first_name}} ⭐',
        'preheader' => 'Prática ilimitada, coaching com IA e mais — veja o que o Premium adiciona',
        'badge' => '✦ PREMIUM',
        'title' => 'Leve seu treino além',
        'subtitle' => 'Olá {{user_first_name}} — você teve alguns dias para explorar o {{app_name}}. Veja o que o <strong style="color:#7c3aed;">Premium</strong> desbloqueia quando você quiser:',
        'f1_t' => 'Exercícios diários ilimitados', 'f1_d' => 'Sem o limite de 3 por dia — pratique o quanto seu ouvido quiser.',
        'f2_t' => 'Prática assistida por IA', 'f2_d' => 'Exercícios gerados em torno dos seus pontos fracos pessoais.',
        'f3_t' => 'Modelos salvos ilimitados', 'f3_d' => 'Tenha cada exercício favorito a um toque de distância.',
        'f4_t' => 'Ditado melódico completo', 'f4_d' => 'O motor de ditado completo, com ritmo e melodias tonais.',
        'btn' => '⭐ Explorar o Premium', 'btn_sub' => 'Faça upgrade quando quiser. Cancele quando quiser.',
        'p2' => 'Curioso para ver como tudo se encaixa? O <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">guia do usuário</a> mostra cada recurso em ação.',
    ],

    'premium_upsell' => [
        'subject' => 'Você superou o plano gratuito, {{user_first_name}} ⭐',
        'preheader' => 'Exercícios ilimitados, modo IA e mais com o Premium',
        'title' => 'Você está se dedicando',
        'subtitle' => 'Olá {{user_first_name}} — você tem praticado com constância. É exatamente assim que o ouvido se treina. Veja o que levaria você além:',
        'f1_t' => 'Exercícios diários ilimitados', 'f1_d' => 'Você vive esbarrando no limite gratuito de 3 por dia — remova-o por completo.',
        'f2_t' => 'Prática assistida por IA', 'f2_d' => 'Ajustada exatamente aos intervalos e acordes que você mais erra.',
        'f3_t' => 'Modelos salvos ilimitados', 'f3_d' => 'Salve todos os exercícios da sua rotina.',
        'btn' => '⭐ Ver planos Premium', 'btn_sub' => 'Você conquistou o upgrade.',
    ],

    'trial_ending' => [
        'subject' => 'Seu teste Premium termina em {{trial_days_left}} dias',
        'preheader' => 'Mantenha sua prática ilimitada',
        'title' => 'Seu teste está quase acabando',
        'p1' => 'Seu teste gratuito do <strong>{{app_name}} Premium</strong> termina em <strong>{{trial_ends_on}}</strong> — ou seja, daqui a {{trial_days_left}} dias.',
        'p2' => 'Nada será cobrado: nunca pegamos os dados do seu cartão. Quando o teste terminar, sua conta simplesmente volta ao plano gratuito, e todo o seu histórico de prática permanece exatamente como está.',
        'p3' => 'Quer manter os exercícios ilimitados, a prática assistida por IA e tudo o mais que o Premium desbloqueia? Você pode assinar quando quiser.',
        'btn' => '💳 Gerenciar meu plano', 'btn_sub' => 'Mantenha a prática ilimitada sem perder o compasso.',
    ],

    'trial_ended' => [
        'subject' => 'Seu teste Premium terminou, {{user_first_name}}',
        'preheader' => 'Você voltou ao plano gratuito — seu progresso está seguro',
        'title' => 'Obrigado por testar o Premium',
        'p1' => 'Seu teste gratuito terminou e sua conta voltou ao <strong>plano gratuito</strong>. Você não foi cobrado — nunca pedimos um cartão.',
        'p2' => 'Tudo o que você praticou durante o teste está salvo: suas estatísticas, sequências e o progresso na Trilha de Aprendizado continuam lá.',
        'btn' => '⭐ Ver planos Premium', 'btn_sub' => 'Retome o Premium com um clique.',
    ],

    // --- Professores ---
    'welcome_teacher' => [
        'subject' => 'Bem-vindo ao {{app_name}} para professores, {{user_first_name}}! 🎓',
        'preheader' => 'Configure seu perfil, seja descoberto e comece a ensinar',
        'badge' => '🎓 PARA PROFESSORES',
        'title' => 'Bem-vindo a bordo, {{user_first_name}}!',
        'subtitle' => 'Sua conta de professor do <strong style="color:#7c3aed;">{{app_name}}</strong> está pronta. Veja como configurá-la e começar a alcançar alunos:',
        'f1_t' => 'Complete seu perfil público', 'f1_d' => 'Adicione sua bio, seus instrumentos e sua experiência e envie para aprovação.',
        'f2_t' => 'Defina sua disponibilidade', 'f2_d' => 'Abra sua agenda para que os alunos agendem aulas diretamente.',
        'f3_t' => 'Conecte-se com alunos', 'f3_d' => 'Convide seus próprios alunos ou seja descoberto no diretório.',
        'f4_t' => 'Publique conteúdo', 'f4_d' => 'Compartilhe artigos e lições para construir sua reputação.',
        'btn' => '🎓 Abrir o painel do professor', 'btn_sub' => 'Sua central de ensino — perfil, agenda, alunos e mensagens.',
        'promo_t' => 'Novo por aqui ensinando no {{app_name}}?', 'promo_s' => '“Treine o ouvido, faça seu estúdio crescer.” Veja como funcionam perfis, agendamentos e pagamentos.', 'promo_btn' => '📖 Como funciona ensinar',
        'ps' => 'Dúvidas sobre sua conta de professor? É só responder — teremos prazer em ajudar. 💜',
    ],

    'premium_intro_teacher' => [
        'subject' => 'Faça seu ensino crescer com o {{app_name}} Premium, {{user_first_name}} ⭐',
        'preheader' => 'Agendamentos, links de pagamento, publicação de conteúdo e perfil em destaque',
        'badge' => '✦ PREMIUM PROFESSOR',
        'title' => 'Faça seu estúdio de ensino crescer',
        'subtitle' => 'Olá {{user_first_name}} — você está no {{app_name}} há alguns dias. Veja o que o <strong style="color:#7c3aed;">Premium</strong> desbloqueia para professores:',
        'f1_t' => 'Aceite agendamentos e pagamentos', 'f1_d' => 'Deixe os alunos agendarem e pagarem aulas com seus próprios links de pagamento.',
        'f2_t' => 'Publique conteúdo ilimitado', 'f2_d' => 'Artigos, lições e mídia para mostrar sua experiência.',
        'f3_t' => 'Perfil em destaque e prioritário', 'f3_d' => 'Destaque-se no diretório de professores e seja descoberto mais rápido.',
        'f4_t' => 'Ferramentas de gestão de alunos', 'f4_d' => 'Tarefas, acompanhamento de progresso e mensagens em um só lugar.',
        'btn' => '⭐ Ver Premium Professor', 'btn_sub' => 'Transforme seu ensino em um estúdio próspero.',
        'p2' => 'Quer ver o quadro completo primeiro? <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">Veja como funciona ensinar</a>.',
    ],

    'trial_ending_teacher' => [
        'subject' => 'Seu teste Premium Professor termina em {{trial_days_left}} dias',
        'preheader' => 'Mantenha agendamentos, pagamentos e publicação de conteúdo',
        'title' => 'Seu teste de professor está quase acabando',
        'p1' => 'Seu teste gratuito do <strong>{{app_name}} Premium Professor</strong> termina em <strong>{{trial_ends_on}}</strong> — daqui a {{trial_days_left}} dias.',
        'p2' => 'Nada será cobrado: nunca pegamos os dados do seu cartão. Quando o teste terminar, sua conta de professor volta ao <strong>Basic</strong>, e recursos como agendamentos, links de pagamento e publicação de conteúdo pausam — mas seu perfil e seus alunos permanecem exatamente como estão.',
        'btn' => '💳 Manter Premium Professor', 'btn_sub' => 'Não perca seus agendamentos e seu conteúdo.',
    ],

    'trial_ended_teacher' => [
        'subject' => 'Seu teste Premium Professor terminou, {{user_first_name}}',
        'preheader' => 'Seu perfil de professor está seguro — você voltou ao Basic',
        'title' => 'Seu teste de professor terminou',
        'p1' => 'Seu teste gratuito terminou e sua conta de professor voltou ao <strong>Basic</strong>. Você não foi cobrado — nunca pedimos um cartão.',
        'p2' => 'Seu perfil público, seus alunos e suas mensagens estão seguros. Agendamentos, links de pagamento e publicação de conteúdo estão prontos para reativar assim que você fizer upgrade.',
        'btn' => '⭐ Ver Premium Professor', 'btn_sub' => 'Retome suas ferramentas de estúdio com um clique.',
    ],

    // --- Escolas ---
    'welcome_school' => [
        'subject' => 'Bem-vindo ao {{app_name}} para escolas, {{user_first_name}}! 🏫',
        'preheader' => 'Configure sua escola, adicione seus professores e gerencie tudo em um só lugar',
        'badge' => '🏫 PARA ESCOLAS',
        'title' => 'Bem-vindo a bordo, {{user_first_name}}!',
        'subtitle' => 'Sua conta de escola do <strong style="color:#7c3aed;">{{app_name}}</strong> está pronta. Veja como configurá-la e trazer seus professores:',
        'f1_t' => 'Configure o perfil da sua escola', 'f1_d' => 'Adicione os dados e a identidade da sua escola e envie para aprovação.',
        'f2_t' => 'Adicione seus professores', 'f2_d' => 'Convide ou conecte professores membros e gerencie-os em um só painel.',
        'f3_t' => 'Gerencie as associações', 'f3_d' => 'Cuide dos vínculos com professores, dos convites e do acesso de forma centralizada.',
        'f4_t' => 'Seja descoberto', 'f4_d' => 'Mostre sua escola no diretório público.',
        'btn' => '🏫 Abrir o painel da escola', 'btn_sub' => 'Sua central da escola — perfil, professores e associações.',
        'promo_t' => 'Novo no {{app_name}} para escolas?', 'promo_s' => '“Um só lar para toda a sua escola de música.” Veja como escolas e professores trabalham juntos.', 'promo_btn' => '📖 Como funcionam as escolas',
        'ps' => 'Precisa de ajuda para configurar sua escola? É só responder — ajudamos você a começar. 💜',
    ],

    'premium_intro_school' => [
        'subject' => 'Desbloqueie o {{app_name}} Premium para sua escola, {{user_first_name}} ⭐',
        'preheader' => 'Professores ilimitados, identidade da escola e visibilidade prioritária',
        'badge' => '✦ PREMIUM ESCOLA',
        'title' => 'Tudo o que sua escola precisa',
        'subtitle' => 'Olá {{user_first_name}} — veja o que o <strong style="color:#7c3aed;">Premium</strong> desbloqueia para sua escola no {{app_name}}:',
        'f1_t' => 'Professores membros ilimitados', 'f1_d' => 'Adicione à sua escola quantos professores precisar.',
        'f2_t' => 'Identidade da escola', 'f2_d' => 'Apresente sua escola com sua própria identidade em todo o Harmoniva.',
        'f3_t' => 'Visibilidade prioritária', 'f3_d' => 'Apareça mais acima e seja descoberto no diretório.',
        'f4_t' => 'Supervisão e ferramentas', 'f4_d' => 'Gerencie professores, associações e atividade em um só painel.',
        'btn' => '⭐ Ver Premium Escola', 'btn_sub' => 'Tudo o que sua escola de música precisa para crescer.',
        'p2' => 'Quer ver primeiro? <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">Como funcionam as escolas no {{app_name}}</a>.',
    ],

    'trial_ending_school' => [
        'subject' => 'Seu teste Premium Escola termina em {{trial_days_left}} dias',
        'preheader' => 'Mantenha professores ilimitados e a identidade da sua escola',
        'title' => 'Seu teste de escola está quase acabando',
        'p1' => 'Seu teste gratuito do <strong>{{app_name}} Premium Escola</strong> termina em <strong>{{trial_ends_on}}</strong> — daqui a {{trial_days_left}} dias.',
        'p2' => 'Nada será cobrado: nunca pegamos os dados do seu cartão. Quando o teste terminar, sua conta de escola volta ao <strong>Basic</strong> — mas o perfil da sua escola, seus professores e suas associações permanecem exatamente como estão.',
        'btn' => '💳 Manter Premium Escola', 'btn_sub' => 'Preserve seus professores e sua identidade.',
    ],

    'trial_ended_school' => [
        'subject' => 'Seu teste Premium Escola terminou, {{user_first_name}}',
        'preheader' => 'O perfil da sua escola está seguro — você voltou ao Basic',
        'title' => 'Seu teste de escola terminou',
        'p1' => 'Seu teste gratuito terminou e sua conta de escola voltou ao <strong>Basic</strong>. Você não foi cobrado — nunca pedimos um cartão.',
        'p2' => 'O perfil da sua escola, seus professores e suas associações estão seguros. Os recursos Premium da escola estão prontos para reativar assim que você fizer upgrade.',
        'btn' => '⭐ Ver Premium Escola', 'btn_sub' => 'Reative as ferramentas da sua escola com um clique.',
    ],

    'first_exercise_teacher' => [
        'subject' => '{{user_first_name}}, adicione seu primeiro aluno 👥',
        'preheader' => 'Seu painel de professor está pronto — só faltam os alunos',
        'title' => 'Pronto para o seu primeiro aluno?',
        'p1' => 'Você abriu sua conta de professor no {{app_name}} há alguns dias, mas nenhum aluno entrou ainda. Convidar leva um minuto: envie o link e a prática, as tarefas e o progresso dele chegam direto ao seu painel.',
        'btn' => '👥 Convidar seu primeiro aluno',
        'btn_sub' => 'Um link. O progresso aparece automaticamente.',
        'p2' => 'Já dá aulas em outro lugar? Traga seus alunos atuais: eles continuam praticando e você fica com os dados.',
    ],

    'first_exercise_school' => [
        'subject' => '{{user_first_name}}, adicione seu primeiro professor 🏫',
        'preheader' => 'Seu painel de escola está pronto — só faltam os professores',
        'title' => 'Pronto para o seu primeiro professor?',
        'p1' => 'Você abriu sua conta de escola no {{app_name}} há alguns dias, mas ainda não há professores no quadro. Convide um e os alunos, as aulas e o progresso dele passam automaticamente para o painel da sua escola.',
        'btn' => '🏫 Convidar seu primeiro professor',
        'btn_sub' => 'Um link. O estúdio dele se conecta à sua escola.',
        'p2' => 'Cada professor que você adiciona traz os alunos junto — toda a sua escola em uma única visão.',
    ],

    'learning_path_teacher' => [
        'subject' => 'Seus alunos estão esperando, {{user_first_name}} 📋',
        'preheader' => 'Dê uma olhada no seu estúdio — o progresso está se acumulando',
        'title' => 'Seu estúdio seguiu sem você',
        'p1' => 'Foi uma semana tranquila do seu lado, mas seus alunos continuaram praticando. As sessões, a precisão e as sequências deles esperam no seu painel — e muitas vezes uma única tarefa basta para tirar alguém de um platô.',
        'btn' => '📋 Ver seus alunos',
        'btn_sub' => 'Veja quem está evoluindo e quem travou.',
        'p2' => '🎯 Uma tarefa curta enviada hoje vale mais que uma longa enviada no mês que vem.',
    ],

    'learning_path_school' => [
        'subject' => 'Sua escola seguiu em frente, {{user_first_name}} 📋',
        'preheader' => 'Seus professores e alunos estiveram ativos',
        'title' => 'Sua escola seguiu sem você',
        'p1' => 'Faz uma semana que você não entra, mas seus professores e os alunos deles continuaram praticando. A atividade das aulas, o progresso dos alunos e o quadro de professores estão atualizados no painel da sua escola.',
        'btn' => '📋 Abrir painel da escola',
        'btn_sub' => 'Cada professor, cada aluno, uma única visão.',
        'p2' => '🎯 Uma olhada semanal costuma bastar para perceber quem precisa de apoio.',
    ],

    'weekly_progress_teacher' => [
        'subject' => 'Seu estúdio nesta semana: {{weekly_sessions}} sessões 📈',
        'preheader' => 'Como seus alunos praticaram nesta semana',
        'title' => 'Seu estúdio nesta semana',
        'subtitle' => 'Foi assim que seus alunos praticaram nesta semana, {{user_first_name}}. Você enviou {{weekly_assignments}} novas tarefas.',
        'm1' => 'Alunos',
        'm2' => 'Sessões',
        'm3' => 'Precisão',
        'btn' => '📈 Abrir painel do professor',
        'btn_sub' => 'Veja quem melhorou e quem precisa de um empurrão.',
    ],

    'weekly_progress_school' => [
        'subject' => 'Sua escola nesta semana: {{weekly_sessions}} sessões 📈',
        'preheader' => 'Como sua escola praticou nesta semana',
        'title' => 'Sua escola nesta semana',
        'subtitle' => 'Este é o resumo da semana na sua escola, {{user_first_name}} — precisão média {{weekly_accuracy}}.',
        'm1' => 'Professores',
        'm2' => 'Alunos',
        'm3' => 'Sessões',
        'btn' => '📈 Abrir painel da escola',
        'btn_sub' => 'Detalhamento por professor e por aluno lá dentro.',
    ],

    're_engagement_teacher' => [
        'subject' => 'Sua conta de professor continua aqui, {{user_first_name}} 🎓',
        'preheader' => 'Seus alunos, tarefas e perfil estão guardados',
        'title' => 'Está tudo onde você deixou',
        'p1' => 'Faz um tempo que você não abre seu painel de professor no {{app_name}}. Sua lista de alunos, suas tarefas, seu perfil e suas avaliações estão exatamente como você deixou — nada expirou, nada se perdeu.',
        'btn' => '🎓 Voltar a ensinar',
        'btn_sub' => 'Retome seu estúdio com um clique.',
    ],

    're_engagement_school' => [
        'subject' => 'Sua conta de escola continua aqui, {{user_first_name}} 🏫',
        'preheader' => 'Seus professores, alunos e configurações estão guardados',
        'title' => 'Está tudo onde você deixou',
        'p1' => 'Faz um tempo que você não abre o painel da sua escola no {{app_name}}. Seu quadro de professores, os registros dos alunos e o perfil da escola estão exatamente como você deixou — nada expirou, nada se perdeu.',
        'btn' => '🏫 Voltar para sua escola',
        'btn_sub' => 'Retome de onde sua escola parou.',
    ],

    'premium_upsell_teacher' => [
        'subject' => 'Seu estúdio superou o plano gratuito, {{user_first_name}} ⭐',
        'preheader' => 'Agendamentos, pagamentos e tarefas ilimitadas com o Premium',
        'badge' => '⭐ PREMIUM PROFESSOR',
        'title' => 'Você está construindo um estúdio de verdade',
        'subtitle' => 'Olá {{user_first_name}} — você tem alunos praticando com regularidade. Veja o que o Premium acrescenta ao estúdio que você já construiu:',
        'f1_t' => 'Agenda de reservas online',
        'f1_d' => 'Os alunos marcam aulas nos seus horários livres. Sem troca de mensagens.',
        'f2_t' => 'Tarefas ilimitadas',
        'f2_d' => 'Envie quantos exercícios personalizados seus alunos precisarem, sem limite diário.',
        'f3_t' => 'Perfil público em destaque',
        'f3_d' => 'Suba no diretório de professores e receba pagamentos pelo seu perfil.',
        'btn' => '⭐ Ver planos para professores',
        'btn_sub' => 'Feito para professores que já dão aula.',
        'p2' => 'Ainda não é a hora? Sua conta gratuita continua funcionando exatamente como hoje.',
    ],

    'premium_upsell_school' => [
        'subject' => 'Sua escola superou o plano gratuito, {{user_first_name}} ⭐',
        'preheader' => 'Professores ilimitados, marca própria e relatórios com o Premium',
        'badge' => '⭐ PREMIUM ESCOLA',
        'title' => 'Sua escola está crescendo',
        'subtitle' => 'Olá {{user_first_name}} — seus professores estão ativos e os alunos deles praticando. Veja o que o Premium acrescenta:',
        'f1_t' => 'Professores ilimitados no quadro',
        'f1_d' => 'Adicione todo o seu corpo docente sem limite por vaga.',
        'f2_t' => 'Relatórios de toda a escola',
        'f2_d' => 'Compare professores e turmas e exporte o progresso para as famílias.',
        'f3_t' => 'Perfil de escola com sua marca',
        'f3_d' => 'Seu logo, sua página e melhor posição no diretório de escolas.',
        'btn' => '⭐ Ver planos para escolas',
        'btn_sub' => 'Feito para escolas com um quadro de verdade.',
        'p2' => 'Ainda não é a hora? Sua conta gratuita continua funcionando exatamente como hoje.',
    ],

];
