<?php

return [

    'appointment' => [
        'status_subject' => 'Actualización de la cita — :when',
        'status' => [
            'confirmed' => 'Tu clase del :when está confirmada.',
            'rejected' => 'La solicitud de cita para el :when fue rechazada.',
            'cancelled_teacher' => 'La clase del :when fue cancelada por el profesor.',
            'cancelled_student' => 'La clase del :when fue cancelada por el alumno.',
            'reschedule' => 'Se solicitó un nuevo horario para la clase del :when.',
            'completed' => 'La clase del :when se marcó como completada.',
            'no_show' => 'La clase del :when se marcó como ausencia.',
            'default' => 'El estado de tu cita ha cambiado.',
        ],
        'lesson_link' => 'Enlace de la clase: :url',
        'view' => 'Ver la cita',
        'request_subject' => 'Nueva solicitud de cita de :name',
        'request_line' => ':name solicitó una clase el :when.',
        'topic' => 'Tema: :topic',
        'review' => 'Revisar la solicitud',
    ],

    'verify' => [
        'preheader' => 'Un clic y tu cuenta de Harmoniva estará activa',
        'title' => 'Confirma tu correo',
        'btn_sub' => 'El enlace es válido durante 60 minutos.',
        'fallback' => '¿El botón no funciona? Copia este enlace en tu navegador:',
        'subject' => 'Verifica tu dirección de correo',
        'line1' => 'Confirma tu dirección de correo para activar tu cuenta de :app.',
        'action' => 'Verificar correo',
        'line2' => 'Si no creaste una cuenta, no es necesario que hagas nada.',
    ],

    'invite' => [
        'teacher_subject' => ':name te invitó a Harmoniva',
        'school_subject' => ':name te invitó a unirte a su escuela en Harmoniva',
        'heading' => 'Estás invitado a Harmoniva 🎵',
        'teacher_intro' => '**:name** te invitó a unirte a Harmoniva como su alumno.',
        'school_intro' => '**:name** te invitó a unirte a su escuela de música en Harmoniva como profesor.',
        'teacher_body' => 'Harmoniva es una plataforma de educación musical con entrenamiento auditivo, práctica de teoría musical y rutas de aprendizaje guiadas. Una vez conectados, tu profesor puede asignarte tareas y seguir tu progreso.',
        'school_body' => 'Harmoniva es una plataforma de educación musical con entrenamiento auditivo, práctica de teoría musical y rutas de aprendizaje guiadas. Como profesor miembro obtienes el conjunto completo de herramientas docentes —alumnos, clases, tareas, mensajería y un calendario de reservas— y tu escuela puede apoyarte en la gestión de tus alumnos.',
        'accept' => 'Aceptar la invitación',
        'expires' => 'Esta invitación caduca el :date.',
        'ignore' => 'Si no esperabas esta invitación, puedes ignorar este correo con tranquilidad.',
        'thanks' => 'Gracias,',
    ],

];
