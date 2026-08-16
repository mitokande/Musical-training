<?php

return [

    'appointment' => [
        'status_subject' => 'Randevu güncellemesi — :when',
        'status' => [
            'confirmed' => ':when tarihindeki dersin onaylandı.',
            'rejected' => ':when tarihindeki randevu talebi reddedildi.',
            'cancelled_teacher' => ':when tarihindeki ders öğretmen tarafından iptal edildi.',
            'cancelled_student' => ':when tarihindeki ders öğrenci tarafından iptal edildi.',
            'reschedule' => ':when tarihindeki ders için yeni bir saat talep edildi.',
            'completed' => ':when tarihindeki ders tamamlandı olarak işaretlendi.',
            'no_show' => ':when tarihindeki ders katılım yok olarak işaretlendi.',
            'default' => 'Randevu durumun değişti.',
        ],
        'lesson_link' => 'Ders bağlantısı: :url',
        'view' => 'Randevuyu görüntüle',
        'request_subject' => ':name adlı kişiden yeni randevu talebi',
        'request_line' => ':name, :when tarihinde bir ders talep etti.',
        'topic' => 'Konu: :topic',
        'review' => 'Talebi incele',
    ],

    'verify' => [
        'preheader' => 'Tek tıkla Harmoniva hesabın aktif',
        'title' => 'E-posta adresini onayla',
        'btn_sub' => 'Bağlantı 60 dakika geçerlidir.',
        'fallback' => 'Buton çalışmıyor mu? Bu bağlantıyı tarayıcına kopyala:',
        'subject' => 'E-posta adresini doğrula',
        'line1' => ':app hesabını etkinleştirmek için lütfen e-posta adresini onayla.',
        'action' => 'E-posta adresini doğrula',
        'line2' => 'Bir hesap oluşturmadıysan başka bir işlem yapmana gerek yok.',
    ],

    'invite' => [
        'teacher_subject' => ':name seni Harmoniva\'ya davet etti',
        'school_subject' => ':name seni Harmoniva\'daki okuluna davet etti',
        'heading' => 'Harmoniva\'ya davetlisin 🎵',
        'teacher_intro' => '**:name** seni Harmoniva\'ya öğrencisi olarak davet etti.',
        'school_intro' => '**:name** seni Harmoniva\'daki müzik okuluna öğretmen olarak davet etti.',
        'teacher_body' => 'Harmoniva; kulak eğitimi, müzik teorisi pratiği ve rehberli öğrenme yolları sunan bir müzik eğitimi platformudur. Bağlandıktan sonra öğretmenin sana ödev verebilir ve ilerlemeni takip edebilir.',
        'school_body' => 'Harmoniva; kulak eğitimi, müzik teorisi pratiği ve rehberli öğrenme yolları sunan bir müzik eğitimi platformudur. Üye öğretmen olarak tüm öğretmen araç setine sahip olursun — öğrenciler, sınıflar, ödevler, mesajlaşma ve rezervasyon takvimi — ve okulun öğrencilerini yönetmende sana destek olabilir.',
        'accept' => 'Daveti kabul et',
        'expires' => 'Bu davet :date tarihinde sona eriyor.',
        'ignore' => 'Bu daveti beklemiyorsan bu e-postayı güvenle yok sayabilirsin.',
        'thanks' => 'Teşekkürler,',
    ],

];
