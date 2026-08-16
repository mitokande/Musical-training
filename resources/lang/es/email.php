<?php

/**
 * Textos de los correos del sistema (plantillas del Email Center). Los campos
 * {{placeholder}} los completa TemplateRenderer para cada destinatario:
 * consérvalos intactos. Conserva también el HTML en línea (<strong>, <a href>).
 */
return [

    'footer' => [
        'manage_prefs' => 'Gestionar preferencias de correo',
        'unsubscribe' => 'Darse de baja',
    ],

    'hi' => 'Hola {{user_first_name}}:',
    'guide_block' => [
        'title' => '¿Nuevo en el entrenamiento auditivo? Empieza aquí.',
        'slogan' => '«Entrena con inteligencia, no con esfuerzo.» Nuestra guía paso a paso te lleva desde tu primer intervalo hasta una escucha fluida.',
        'button' => '📖 Leer la guía del usuario',
    ],

    'welcome' => [
        'subject' => '¡Bienvenido a {{app_name}}, {{user_first_name}}! 🎵',
        'preheader' => 'Tu oído musical empieza a entrenarse hoy: esto es todo lo que desbloqueas',
        'title' => '¡Bienvenido a bordo, {{user_first_name}}!',
        'subtitle' => 'Acabas de unirte a {{app_name}}, la forma más amena de entrenar tu oído musical. Esto es lo que te espera:',
        'f1_t' => 'Haz la prueba de nivel', 'f1_d' => 'Adaptamos una Ruta de Aprendizaje personalizada a tu nivel exacto.',
        'f2_t' => 'Entrena con audio real', 'f2_d' => 'Notas sueltas, intervalos, acordes, escalas, ritmo y dictado melódico.',
        'f3_t' => 'Sigue cada sesión', 'f3_d' => 'La precisión, las rachas y los gráficos de progreso te impulsan.',
        'f4_t' => 'Práctica asistida por IA', 'f4_d' => 'Ejercicios inteligentes centrados en tus puntos débiles (Premium).',
        'btn' => '🚀 Empezar a entrenar', 'btn_sub' => 'Sin configuración: salta directo a tu primera sesión.',
        'ps' => '¿Preguntas? Responde a este correo: una persona real lee cada uno. 💜',
    ],

    'first_exercise' => [
        'subject' => '{{user_first_name}}, tu primer ejercicio te espera 🎧',
        'preheader' => 'Entrenar tu oído solo lleva 5 minutos',
        'title' => '¿Listo para tu primera sesión?',
        'p1' => 'Creaste tu cuenta de {{app_name}} hace un par de días, pero aún no has probado ningún ejercicio. El primero lleva menos de cinco minutos, y es el que pone tu oído en marcha.',
        'btn' => '🎧 Prueba tu primer ejercicio', 'btn_sub' => 'Menos de 5 minutos. Lo más difícil es empezar.',
        'p2' => '¿No sabes por dónde empezar? La <a href="{{app_url}}/learn" style="color:#7c3aed;font-weight:600;">Ruta de Aprendizaje</a> te guía paso a paso, o echa un vistazo primero a la <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">guía del usuario</a>.',
    ],

    'learning_path' => [
        'subject' => 'Tu Ruta de Aprendizaje te echa de menos, {{user_first_name}} 🎼',
        'preheader' => 'Retoma justo donde lo dejaste',
        'title' => 'Retoma donde lo dejaste',
        'p1' => 'Tu oído se estaba afinando; no dejes que ese progreso se apague. Tu Ruta de Aprendizaje está tal cual la dejaste, rachas incluidas, lista cuando tú lo estés.',
        'btn' => '🎼 Continuar la Ruta de Aprendizaje', 'btn_sub' => 'Incluso una sesión corta mantiene el impulso.',
        'p2' => '🔥 La constancia supera a la intensidad. Cinco minutos hoy ya son una victoria.',
    ],

    'weekly_progress' => [
        'subject' => 'Tu semana en {{app_name}}: {{weekly_sessions}} sesiones 📈',
        'preheader' => 'Tu resumen semanal de entrenamiento auditivo',
        'title' => 'Tu semana en resumen',
        'subtitle' => 'Buen trabajo esta semana, {{user_first_name}}. Aquí tienes el resumen:',
        'sessions' => 'Sesiones', 'accuracy' => 'Precisión', 'minutes' => 'Minutos',
        'btn' => '📈 Sigue así', 'btn_sub' => 'Las semanas pequeñas suman un oído entrenado.',
    ],

    're_engagement' => [
        'subject' => 'Guardamos tu progreso, {{user_first_name}} 🎹',
        'preheader' => 'Tu progreso de entrenamiento auditivo está a salvo: vuelve cuando quieras',
        'title' => 'Tu progreso está a salvo con nosotros',
        'p1' => 'Ha pasado un tiempo desde tu última sesión de práctica en {{app_name}}. La buena noticia: tus estadísticas, rachas y progreso en la Ruta de Aprendizaje están guardados justo donde los dejaste.',
        'btn' => '🎹 Reanudar el entrenamiento', 'btn_sub' => 'Cinco minutos hoy valen más que una hora algún día.',
    ],

    'premium_intro' => [
        'subject' => 'Descubre {{app_name}} Premium, {{user_first_name}} ⭐',
        'preheader' => 'Práctica ilimitada, coaching con IA y más: mira lo que añade Premium',
        'badge' => '✦ PREMIUM',
        'title' => 'Lleva tu entrenamiento más lejos',
        'subtitle' => 'Hola {{user_first_name}}: llevas unos días explorando {{app_name}}. Esto es lo que desbloquea <strong style="color:#7c3aed;">Premium</strong> cuando quieras:',
        'f1_t' => 'Ejercicios diarios ilimitados', 'f1_d' => 'Sin el límite de 3 al día: practica todo lo que tu oído quiera.',
        'f2_t' => 'Práctica asistida por IA', 'f2_d' => 'Ejercicios generados en torno a tus puntos débiles personales.',
        'f3_t' => 'Plantillas guardadas ilimitadas', 'f3_d' => 'Ten cada ejercicio favorito a un toque de distancia.',
        'f4_t' => 'Dictado melódico completo', 'f4_d' => 'El motor de dictado completo con ritmo y melodías tonales.',
        'btn' => '⭐ Explorar Premium', 'btn_sub' => 'Mejora cuando quieras. Cancela cuando quieras.',
        'p2' => '¿Con curiosidad por cómo encaja todo? La <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">guía del usuario</a> muestra cada función en acción.',
    ],

    'premium_upsell' => [
        'subject' => 'Se te quedó pequeño el plan gratuito, {{user_first_name}} ⭐',
        'preheader' => 'Ejercicios ilimitados, modo IA y más con Premium',
        'title' => 'Estás poniendo de tu parte',
        'subtitle' => 'Hola {{user_first_name}}: has estado practicando con constancia. Así es exactamente como se entrena el oído. Esto te llevaría más lejos:',
        'f1_t' => 'Ejercicios diarios ilimitados', 'f1_d' => 'Sigues topando con el límite gratuito de 3 al día: quítalo por completo.',
        'f2_t' => 'Práctica asistida por IA', 'f2_d' => 'Adaptada a los intervalos y acordes que más fallas.',
        'f3_t' => 'Plantillas guardadas ilimitadas', 'f3_d' => 'Guarda todos los ejercicios de tu rutina.',
        'btn' => '⭐ Ver planes Premium', 'btn_sub' => 'Te has ganado la mejora.',
    ],

    'trial_ending' => [
        'subject' => 'Tu prueba Premium termina en {{trial_days_left}} días',
        'preheader' => 'Mantén tu práctica ilimitada',
        'title' => 'Tu prueba está a punto de terminar',
        'p1' => 'Tu prueba gratuita de <strong>{{app_name}} Premium</strong> termina el <strong>{{trial_ends_on}}</strong>, es decir, dentro de {{trial_days_left}} días.',
        'p2' => 'No se cobrará nada: nunca tomamos los datos de tu tarjeta. Cuando la prueba termine, tu cuenta simplemente vuelve al plan gratuito y todo tu historial de práctica queda tal cual.',
        'p3' => '¿Quieres mantener los ejercicios ilimitados, la práctica asistida por IA y todo lo demás que desbloquea Premium? Puedes suscribirte cuando quieras.',
        'btn' => '💳 Gestionar mi plan', 'btn_sub' => 'Mantén la práctica ilimitada sin perder el ritmo.',
    ],

    'trial_ended' => [
        'subject' => 'Tu prueba Premium ha terminado, {{user_first_name}}',
        'preheader' => 'Vuelves al plan gratuito: tu progreso está a salvo',
        'title' => 'Gracias por probar Premium',
        'p1' => 'Tu prueba gratuita ha terminado y tu cuenta vuelve al <strong>plan gratuito</strong>. No se te cobró: nunca pedimos una tarjeta.',
        'p2' => 'Todo lo que practicaste durante la prueba está guardado: tus estadísticas, rachas y progreso en la Ruta de Aprendizaje siguen ahí.',
        'btn' => '⭐ Ver planes Premium', 'btn_sub' => 'Retoma Premium con un solo clic.',
    ],

    // --- Profesores ---
    'welcome_teacher' => [
        'subject' => '¡Bienvenido a {{app_name}} para profesores, {{user_first_name}}! 🎓',
        'preheader' => 'Configura tu perfil, hazte visible y empieza a enseñar',
        'badge' => '🎓 PARA PROFESORES',
        'title' => '¡Bienvenido a bordo, {{user_first_name}}!',
        'subtitle' => 'Tu cuenta de profesor de <strong style="color:#7c3aed;">{{app_name}}</strong> está lista. Así puedes configurarla y empezar a llegar a los alumnos:',
        'f1_t' => 'Completa tu perfil público', 'f1_d' => 'Añade tu biografía, instrumentos y experiencia, y envíalo para su aprobación.',
        'f2_t' => 'Define tu disponibilidad', 'f2_d' => 'Abre tu calendario para que los alumnos reserven clases directamente.',
        'f3_t' => 'Conecta con alumnos', 'f3_d' => 'Invita a tus propios alumnos o hazte visible en el directorio.',
        'f4_t' => 'Publica contenido', 'f4_d' => 'Comparte artículos y lecciones para construir tu reputación.',
        'btn' => '🎓 Abrir el panel de profesor', 'btn_sub' => 'Tu centro de enseñanza: perfil, calendario, alumnos y mensajes.',
        'promo_t' => '¿Nuevo enseñando en {{app_name}}?', 'promo_s' => '«Entrena el oído, haz crecer tu estudio.» Mira cómo funcionan los perfiles, las reservas y los pagos.', 'promo_btn' => '📖 Cómo funciona enseñar',
        'ps' => '¿Preguntas sobre tu cuenta de profesor? Responde: estaremos encantados de ayudar. 💜',
    ],

    'premium_intro_teacher' => [
        'subject' => 'Haz crecer tu enseñanza con {{app_name}} Premium, {{user_first_name}} ⭐',
        'preheader' => 'Reservas, enlaces de pago, publicación de contenido y perfil destacado',
        'badge' => '✦ PREMIUM PROFESOR',
        'title' => 'Haz crecer tu estudio de enseñanza',
        'subtitle' => 'Hola {{user_first_name}}: llevas unos días en {{app_name}}. Esto es lo que <strong style="color:#7c3aed;">Premium</strong> desbloquea para los profesores:',
        'f1_t' => 'Acepta reservas y pagos', 'f1_d' => 'Deja que los alumnos reserven y paguen clases con tus propios enlaces de pago.',
        'f2_t' => 'Publica contenido ilimitado', 'f2_d' => 'Artículos, lecciones y contenido para mostrar tu experiencia.',
        'f3_t' => 'Perfil destacado y prioritario', 'f3_d' => 'Destaca en el directorio de profesores y hazte visible más rápido.',
        'f4_t' => 'Herramientas de gestión de alumnos', 'f4_d' => 'Tareas, seguimiento del progreso y mensajería en un solo lugar.',
        'btn' => '⭐ Ver Premium Profesor', 'btn_sub' => 'Convierte tu enseñanza en un estudio próspero.',
        'p2' => '¿Quieres ver primero el panorama completo? <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">Mira cómo funciona enseñar</a>.',
    ],

    'trial_ending_teacher' => [
        'subject' => 'Tu prueba Premium Profesor termina en {{trial_days_left}} días',
        'preheader' => 'Mantén las reservas, los pagos y la publicación de contenido',
        'title' => 'Tu prueba de profesor está por terminar',
        'p1' => 'Tu prueba gratuita de <strong>{{app_name}} Premium Profesor</strong> termina el <strong>{{trial_ends_on}}</strong>, dentro de {{trial_days_left}} días.',
        'p2' => 'No se cobrará nada: nunca tomamos los datos de tu tarjeta. Cuando la prueba termine, tu cuenta de profesor vuelve a <strong>Basic</strong> y funciones como reservas, enlaces de pago y publicación de contenido se pausan, pero tu perfil y tus alumnos quedan tal cual.',
        'btn' => '💳 Mantener Premium Profesor', 'btn_sub' => 'No pierdas tus reservas ni tu contenido.',
    ],

    'trial_ended_teacher' => [
        'subject' => 'Tu prueba Premium Profesor ha terminado, {{user_first_name}}',
        'preheader' => 'Tu perfil docente está a salvo: vuelves a Basic',
        'title' => 'Tu prueba de profesor ha terminado',
        'p1' => 'Tu prueba gratuita ha terminado y tu cuenta de profesor vuelve a <strong>Basic</strong>. No se te cobró: nunca pedimos una tarjeta.',
        'p2' => 'Tu perfil público, tus alumnos y tus mensajes están a salvo. Las reservas, los enlaces de pago y la publicación de contenido están listos para reactivarse cuando mejores tu plan.',
        'btn' => '⭐ Ver Premium Profesor', 'btn_sub' => 'Recupera tus herramientas de estudio con un clic.',
    ],

    // --- Escuelas ---
    'welcome_school' => [
        'subject' => '¡Bienvenido a {{app_name}} para escuelas, {{user_first_name}}! 🏫',
        'preheader' => 'Configura tu escuela, añade a tus profesores y gestiónalo todo en un solo lugar',
        'badge' => '🏫 PARA ESCUELAS',
        'title' => '¡Bienvenido a bordo, {{user_first_name}}!',
        'subtitle' => 'Tu cuenta de escuela de <strong style="color:#7c3aed;">{{app_name}}</strong> está lista. Así puedes configurarla e incorporar a tus profesores:',
        'f1_t' => 'Configura el perfil de tu escuela', 'f1_d' => 'Añade los datos y la identidad de tu escuela, y envíalo para su aprobación.',
        'f2_t' => 'Añade a tus profesores', 'f2_d' => 'Invita o conecta a profesores miembros y gestiónalos en un solo panel.',
        'f3_t' => 'Gestiona las membresías', 'f3_d' => 'Administra las relaciones con profesores, las invitaciones y el acceso de forma centralizada.',
        'f4_t' => 'Hazte visible', 'f4_d' => 'Muestra tu escuela en el directorio público.',
        'btn' => '🏫 Abrir el panel de escuela', 'btn_sub' => 'Tu centro escolar: perfil, profesores y membresías.',
        'promo_t' => '¿Nuevo en {{app_name}} para escuelas?', 'promo_s' => '«Un solo hogar para toda tu escuela de música.» Mira cómo colaboran escuelas y profesores.', 'promo_btn' => '📖 Cómo funcionan las escuelas',
        'ps' => '¿Necesitas ayuda para configurar tu escuela? Responde: te ayudamos a empezar. 💜',
    ],

    'premium_intro_school' => [
        'subject' => 'Desbloquea {{app_name}} Premium para tu escuela, {{user_first_name}} ⭐',
        'preheader' => 'Profesores ilimitados, identidad de escuela y visibilidad prioritaria',
        'badge' => '✦ PREMIUM ESCUELA',
        'title' => 'Todo lo que tu escuela necesita',
        'subtitle' => 'Hola {{user_first_name}}: esto es lo que <strong style="color:#7c3aed;">Premium</strong> desbloquea para tu escuela en {{app_name}}:',
        'f1_t' => 'Profesores miembros ilimitados', 'f1_d' => 'Añade a tu escuela tantos profesores como necesites.',
        'f2_t' => 'Identidad de escuela', 'f2_d' => 'Presenta tu escuela con tu propia identidad en todo Harmoniva.',
        'f3_t' => 'Visibilidad prioritaria', 'f3_d' => 'Aparece más arriba y hazte visible en el directorio.',
        'f4_t' => 'Supervisión y herramientas', 'f4_d' => 'Gestiona profesores, membresías y actividad desde un solo panel.',
        'btn' => '⭐ Ver Premium Escuela', 'btn_sub' => 'Todo lo que tu escuela de música necesita para crecer.',
        'p2' => '¿Quieres verlo primero? <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">Cómo funcionan las escuelas en {{app_name}}</a>.',
    ],

    'trial_ending_school' => [
        'subject' => 'Tu prueba Premium Escuela termina en {{trial_days_left}} días',
        'preheader' => 'Mantén los profesores ilimitados y la identidad de tu escuela',
        'title' => 'Tu prueba de escuela está por terminar',
        'p1' => 'Tu prueba gratuita de <strong>{{app_name}} Premium Escuela</strong> termina el <strong>{{trial_ends_on}}</strong>, dentro de {{trial_days_left}} días.',
        'p2' => 'No se cobrará nada: nunca tomamos los datos de tu tarjeta. Cuando la prueba termine, tu cuenta de escuela vuelve a <strong>Basic</strong>, pero el perfil de tu escuela, tus profesores y tus membresías quedan tal cual.',
        'btn' => '💳 Mantener Premium Escuela', 'btn_sub' => 'Conserva tus profesores y tu identidad.',
    ],

    'trial_ended_school' => [
        'subject' => 'Tu prueba Premium Escuela ha terminado, {{user_first_name}}',
        'preheader' => 'El perfil de tu escuela está a salvo: vuelves a Basic',
        'title' => 'Tu prueba de escuela ha terminado',
        'p1' => 'Tu prueba gratuita ha terminado y tu cuenta de escuela vuelve a <strong>Basic</strong>. No se te cobró: nunca pedimos una tarjeta.',
        'p2' => 'El perfil de tu escuela, tus profesores y tus membresías están a salvo. Las funciones Premium de escuela están listas para reactivarse cuando mejores tu plan.',
        'btn' => '⭐ Ver Premium Escuela', 'btn_sub' => 'Reactiva las herramientas de tu escuela con un clic.',
    ],

    'first_exercise_teacher' => [
        'subject' => '{{user_first_name}}, añade tu primer alumno 👥',
        'preheader' => 'Tu panel de profesor está listo — solo faltan alumnos',
        'title' => '¿Listo para tu primer alumno?',
        'p1' => 'Abriste tu cuenta de profesor en {{app_name}} hace unos días, pero todavía no se ha unido ningún alumno. Invitar a uno lleva un minuto: envía el enlace y su práctica, sus tareas y su progreso llegarán directamente a tu panel.',
        'btn' => '👥 Invita a tu primer alumno',
        'btn_sub' => 'Un enlace. Su progreso aparece automáticamente.',
        'p2' => '¿Ya das clases en otro sitio? Trae a tus alumnos actuales: ellos siguen practicando y tú obtienes los datos.',
    ],

    'first_exercise_school' => [
        'subject' => '{{user_first_name}}, añade tu primer profesor 🏫',
        'preheader' => 'Tu panel de escuela está listo — solo faltan profesores',
        'title' => '¿Listo para tu primer profesor?',
        'p1' => 'Abriste tu cuenta de escuela en {{app_name}} hace unos días, pero aún no hay profesores en la plantilla. Invita a uno y sus alumnos, sus clases y su progreso se integrarán automáticamente en el panel de tu escuela.',
        'btn' => '🏫 Invita a tu primer profesor',
        'btn_sub' => 'Un enlace. Su estudio se conecta a tu escuela.',
        'p2' => 'Cada profesor que añadas trae consigo a sus alumnos: toda tu escuela en una sola vista.',
    ],

    'learning_path_teacher' => [
        'subject' => 'Tus alumnos te esperan, {{user_first_name}} 📋',
        'preheader' => 'Echa un vistazo a tu estudio — el progreso se acumula',
        'title' => 'Tu estudio siguió sin ti',
        'p1' => 'Ha sido una semana tranquila por tu parte, pero tus alumnos han seguido practicando. Sus sesiones, su precisión y sus rachas te esperan en el panel, y muchas veces basta con una sola tarea para que alguien supere un estancamiento.',
        'btn' => '📋 Revisa a tus alumnos',
        'btn_sub' => 'Mira quién avanza y quién se ha atascado.',
        'p2' => '🎯 Una tarea corta enviada hoy vale más que una larga enviada el mes que viene.',
    ],

    'learning_path_school' => [
        'subject' => 'Tu escuela siguió adelante, {{user_first_name}} 📋',
        'preheader' => 'Tus profesores y alumnos han estado activos',
        'title' => 'Tu escuela siguió sin ti',
        'p1' => 'Hace una semana que no entras, pero tus profesores y sus alumnos han seguido practicando. La actividad de las clases, el progreso de los alumnos y la plantilla de profesores están al día en el panel de tu escuela.',
        'btn' => '📋 Abrir panel de la escuela',
        'btn_sub' => 'Cada profesor, cada alumno, una sola vista.',
        'p2' => '🎯 Un vistazo semanal suele bastar para detectar quién necesita apoyo.',
    ],

    'weekly_progress_teacher' => [
        'subject' => 'Tu estudio esta semana: {{weekly_sessions}} sesiones 📈',
        'preheader' => 'Cómo practicaron tus alumnos esta semana',
        'title' => 'Tu estudio esta semana',
        'subtitle' => 'Así practicaron tus alumnos esta semana, {{user_first_name}}. Tú enviaste {{weekly_assignments}} tareas nuevas.',
        'm1' => 'Alumnos',
        'm2' => 'Sesiones',
        'm3' => 'Precisión',
        'btn' => '📈 Abrir panel de profesor',
        'btn_sub' => 'Analiza quién mejoró y quién necesita un empujón.',
    ],

    'weekly_progress_school' => [
        'subject' => 'Tu escuela esta semana: {{weekly_sessions}} sesiones 📈',
        'preheader' => 'Cómo practicó tu escuela esta semana',
        'title' => 'Tu escuela esta semana',
        'subtitle' => 'Este es el resumen semanal de tu escuela, {{user_first_name}}: precisión media {{weekly_accuracy}}.',
        'm1' => 'Profesores',
        'm2' => 'Alumnos',
        'm3' => 'Sesiones',
        'btn' => '📈 Abrir panel de la escuela',
        'btn_sub' => 'Desglose por profesor y por alumno dentro.',
    ],

    're_engagement_teacher' => [
        'subject' => 'Tu cuenta de profesor sigue aquí, {{user_first_name}} 🎓',
        'preheader' => 'Tus alumnos, tareas y perfil están guardados',
        'title' => 'Todo está donde lo dejaste',
        'p1' => 'Hace tiempo que no abres tu panel de profesor en {{app_name}}. Tu lista de alumnos, tus tareas, tu perfil y tus reseñas están exactamente como los dejaste: nada ha caducado, nada se ha perdido.',
        'btn' => '🎓 Volver a enseñar',
        'btn_sub' => 'Retoma tu estudio con un clic.',
    ],

    're_engagement_school' => [
        'subject' => 'Tu cuenta de escuela sigue aquí, {{user_first_name}} 🏫',
        'preheader' => 'Tus profesores, alumnos y ajustes están guardados',
        'title' => 'Todo está donde lo dejaste',
        'p1' => 'Hace tiempo que no abres el panel de tu escuela en {{app_name}}. Tu plantilla de profesores, los registros de alumnos y el perfil de la escuela están exactamente como los dejaste: nada ha caducado, nada se ha perdido.',
        'btn' => '🏫 Volver a tu escuela',
        'btn_sub' => 'Retoma tu escuela donde la dejaste.',
    ],

    'premium_upsell_teacher' => [
        'subject' => 'Tu estudio superó el plan gratuito, {{user_first_name}} ⭐',
        'preheader' => 'Reservas, pagos y tareas ilimitadas con Premium',
        'badge' => '⭐ PREMIUM PROFESOR',
        'title' => 'Estás construyendo un estudio de verdad',
        'subtitle' => 'Hola {{user_first_name}}: tienes alumnos practicando con regularidad. Esto es lo que Premium añade al estudio que ya has construido:',
        'f1_t' => 'Calendario de reservas online',
        'f1_d' => 'Los alumnos reservan clases en tus huecos libres. Sin mensajes de ida y vuelta.',
        'f2_t' => 'Tareas ilimitadas',
        'f2_d' => 'Envía tantos ejercicios personalizados como necesiten tus alumnos, sin límite diario.',
        'f3_t' => 'Perfil público destacado',
        'f3_d' => 'Sube posiciones en el directorio de profesores y cobra desde tu perfil.',
        'btn' => '⭐ Ver planes para profesores',
        'btn_sub' => 'Pensado para profesores que ya dan clase.',
        'p2' => '¿Aún no es el momento? Tu cuenta gratuita seguirá funcionando exactamente igual que hoy.',
    ],

    'premium_upsell_school' => [
        'subject' => 'Tu escuela superó el plan gratuito, {{user_first_name}} ⭐',
        'preheader' => 'Profesores ilimitados, marca propia e informes con Premium',
        'badge' => '⭐ PREMIUM ESCUELA',
        'title' => 'Tu escuela está creciendo',
        'subtitle' => 'Hola {{user_first_name}}: tus profesores están activos y sus alumnos practican. Esto es lo que añade Premium:',
        'f1_t' => 'Profesores ilimitados en la plantilla',
        'f1_d' => 'Añade a todo tu claustro sin límite por plaza.',
        'f2_t' => 'Informes de toda la escuela',
        'f2_d' => 'Compara profesores y grupos, y exporta el progreso para las familias.',
        'f3_t' => 'Perfil de escuela con tu marca',
        'f3_d' => 'Tu logo, tu página y mejor posición en el directorio de escuelas.',
        'btn' => '⭐ Ver planes para escuelas',
        'btn_sub' => 'Pensado para escuelas con una plantilla real.',
        'p2' => '¿Aún no es el momento? Tu cuenta gratuita seguirá funcionando exactamente igual que hoy.',
    ],

];
