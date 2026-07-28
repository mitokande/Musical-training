<?php

/**
 * Sistem e-posta metinleri (Email Center şablonları). {{placeholder}} alanları
 * her alıcı için TemplateRenderer tarafından doldurulur — çevirilerde aynen
 * korunmalı. Satır içi HTML etiketleri (<strong>, <a href>) de korunmalı.
 */
return [

    'footer' => [
        'manage_prefs' => 'E-posta tercihlerini yönet',
        'unsubscribe' => 'Abonelikten çık',
    ],

    'hi' => 'Merhaba {{user_first_name}},',
    'guide_block' => [
        'title' => 'Kulak eğitimine yeni mi başladın? Buradan başla.',
        'slogan' => '“Daha çok değil, daha akıllı çalış.” Adım adım rehberimiz ilk aralığından akıcı dinlemeye kadar sana eşlik eder.',
        'button' => '📖 Kullanıcı Rehberini Oku',
    ],

    'welcome' => [
        'subject' => "{{app_name}}'ya hoş geldin, {{user_first_name}}! 🎵",
        'preheader' => 'Müzik kulağın bugün eğitilmeye başlıyor — açtığın her şey burada',
        'title' => 'Aramıza hoş geldin, {{user_first_name}}!',
        'subtitle' => 'Az önce {{app_name}}\'ya katıldın — müzik kulağını eğitmenin en keyifli yolu. Seni bekleyenler:',
        'f1_t' => 'Seviye testini çöz', 'f1_d' => 'Sana özel bir Öğrenme Yolu\'nu tam seviyene göre hazırlıyoruz.',
        'f2_t' => 'Gerçek seslerle çalış', 'f2_d' => 'Tek nota, aralık, akor, gam, ritim ve melodik dikte.',
        'f3_t' => 'Her oturumu takip et', 'f3_d' => 'Doğruluk, seriler ve ilerleme grafikleri seni ileri taşır.',
        'f4_t' => 'Yapay zekâ destekli pratik', 'f4_d' => 'Zayıf noktalarına odaklanan akıllı egzersizler (Premium).',
        'btn' => '🚀 Çalışmaya Başla', 'btn_sub' => 'Kurulum yok — doğrudan ilk oturumuna atla.',
        'ps' => 'Sorun mu var? Bu e-postayı yanıtla — her birini gerçek bir insan okuyor. 💜',
    ],

    'first_exercise' => [
        'subject' => '{{user_first_name}}, ilk egzersizin seni bekliyor 🎧',
        'preheader' => 'Kulağını eğitmek yalnızca 5 dakika sürüyor',
        'title' => 'İlk oturumuna hazır mısın?',
        'p1' => '{{app_name}} hesabını birkaç gün önce açtın ama henüz bir egzersiz denemedin. İlk egzersiz beş dakikadan kısa sürüyor — ve kulağını harekete geçiren tam da bu.',
        'btn' => '🎧 İlk Egzersizini Dene', 'btn_sub' => '5 dakikadan az. En zor kısmı başlamak.',
        'p2' => 'Nereden başlayacağından emin değil misin? <a href="{{app_url}}/learn" style="color:#7c3aed;font-weight:600;">Öğrenme Yolu</a> seni adım adım yönlendirir; ya da önce <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">Kullanıcı Rehberi</a>\'ne göz at.',
    ],

    'learning_path' => [
        'subject' => 'Öğrenme Yolun seni özledi, {{user_first_name}} 🎼',
        'preheader' => 'Kaldığın yerden hemen devam et',
        'title' => 'Kaldığın yerden devam et',
        'p1' => 'Kulağın keskinleşiyordu — bu ilerlemenin sönmesine izin verme. Öğrenme Yolun serilerinle birlikte tam bıraktığın yerde, hazır olduğunda seni bekliyor.',
        'btn' => '🎼 Öğrenme Yoluna Devam Et', 'btn_sub' => 'Kısa bir oturum bile ivmeyi korur.',
        'p2' => '🔥 İstikrar yoğunluğu yener. Bugün 5 dakika bile bir kazanç.',
    ],

    'weekly_progress' => [
        'subject' => '{{app_name}}\'da haftan: {{weekly_sessions}} oturum 📈',
        'preheader' => 'Haftalık kulak eğitimi özetin',
        'title' => 'Haftanın özeti',
        'subtitle' => 'Bu hafta güzel iş çıkardın, {{user_first_name}}. İşte özet:',
        'sessions' => 'Oturum', 'accuracy' => 'Doğruluk', 'minutes' => 'Dakika',
        'btn' => '📈 Böyle Devam', 'btn_sub' => 'Küçük haftalar eğitimli bir kulakta birikir.',
    ],

    're_engagement' => [
        'subject' => 'İlerlemeni sakladık, {{user_first_name}} 🎹',
        'preheader' => 'Kulak eğitimi ilerlemen güvende — istediğin zaman dön',
        'title' => 'İlerlemen bizde güvende',
        'p1' => '{{app_name}}\'daki son pratik oturumunun üzerinden bir süre geçti. İyi haber: istatistiklerin, serilerin ve Öğrenme Yolu ilerlemen tam bıraktığın yerde saklı.',
        'btn' => '🎹 Çalışmaya Dön', 'btn_sub' => 'Bugünkü beş dakika, bir gün sonraki bir saatten iyidir.',
    ],

    'premium_intro' => [
        'subject' => '{{app_name}} Premium ile tanış, {{user_first_name}} ⭐',
        'preheader' => 'Sınırsız pratik, yapay zekâ koçluğu ve daha fazlası — Premium ne katıyor gör',
        'badge' => '✦ PREMIUM',
        'title' => 'Çalışmanı bir üst seviyeye taşı',
        'subtitle' => 'Merhaba {{user_first_name}} — birkaç gündür {{app_name}}\'yı keşfediyorsun. Hazır olduğunda <strong style="color:#7c3aed;">Premium</strong>\'un açtıkları:',
        'f1_t' => 'Sınırsız günlük egzersiz', 'f1_d' => 'Günde 3 sınırı yok — kulağın ne isterse o kadar çalış.',
        'f2_t' => 'Yapay zekâ destekli pratik', 'f2_d' => 'Kişisel zayıf noktalarına göre üretilen egzersizler.',
        'f3_t' => 'Sınırsız kayıtlı şablon', 'f3_d' => 'Sevdiğin her özel alıştırmayı tek dokunuşla sakla.',
        'f4_t' => 'Tam melodik dikte', 'f4_d' => 'Ritim ve tonal melodilerle eksiksiz dikte motoru.',
        'btn' => '⭐ Premium\'u Keşfet', 'btn_sub' => 'İstediğin zaman yükselt. İstediğin zaman iptal et.',
        'p2' => 'Hepsi nasıl bir araya geliyor merak mı ettin? <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">Kullanıcı Rehberi</a> her özelliği iş başında gösteriyor.',
    ],

    'premium_upsell' => [
        'subject' => 'Ücretsiz planı aştın, {{user_first_name}} ⭐',
        'preheader' => 'Premium ile sınırsız egzersiz, yapay zekâ modu ve daha fazlası',
        'title' => 'Emeğini ortaya koyuyorsun',
        'subtitle' => 'Merhaba {{user_first_name}} — düzenli olarak çalışıyorsun. Kulaklar tam da böyle eğitilir. Seni ileri taşıyacak olanlar:',
        'f1_t' => 'Sınırsız günlük egzersiz', 'f1_d' => 'Günde 3 sınırına takılıp duruyorsun — tamamen kaldır.',
        'f2_t' => 'Yapay zekâ destekli pratik', 'f2_d' => 'En çok kaçırdığın aralık ve akorlara göre uyarlanır.',
        'f3_t' => 'Sınırsız kayıtlı şablon', 'f3_d' => 'Rutinindeki her alıştırmayı sakla.',
        'btn' => '⭐ Premium Planları Gör', 'btn_sub' => 'Bu yükseltmeyi hak ettin.',
    ],

    'trial_ending' => [
        'subject' => 'Premium denemen {{trial_days_left}} gün sonra bitiyor',
        'preheader' => 'Sınırsız pratiğin devam etsin',
        'title' => 'Denemen neredeyse doldu',
        'p1' => 'Ücretsiz <strong>{{app_name}} Premium</strong> denemen <strong>{{trial_ends_on}}</strong> tarihinde bitiyor — yani bundan {{trial_days_left}} gün sonra.',
        'p2' => 'Hiçbir ücret alınmayacak: kart bilgilerini hiç almadık. Deneme bitince hesabın basitçe ücretsiz plana döner ve tüm pratik geçmişin olduğu gibi kalır.',
        'p3' => 'Sınırsız egzersiz, yapay zekâ destekli pratik ve Premium\'un açtığı her şeyi sürdürmek ister misin? İstediğin zaman abone olabilirsin.',
        'btn' => '💳 Planımı Yönet', 'btn_sub' => 'Sınırsız pratiği hiç aksatmadan sürdür.',
    ],

    'trial_ended' => [
        'subject' => 'Premium denemen sona erdi, {{user_first_name}}',
        'preheader' => 'Ücretsiz plandasın — ilerlemen güvende',
        'title' => 'Premium\'u denediğin için teşekkürler',
        'p1' => 'Ücretsiz denemen sona erdi ve hesabın yeniden <strong>ücretsiz plana</strong> döndü. Ücret alınmadı — kart bilgisi hiç istemedik.',
        'p2' => 'Deneme boyunca çalıştığın her şey kayıtlı: istatistiklerin, serilerin ve Öğrenme Yolu ilerlemen hâlâ orada.',
        'btn' => '⭐ Premium Planları Gör', 'btn_sub' => 'Premium\'a tek tıkla geri dön.',
    ],

    // --- Öğretmen ---
    'welcome_teacher' => [
        'subject' => 'Öğretmenler için {{app_name}}\'ya hoş geldin, {{user_first_name}}! 🎓',
        'preheader' => 'Profilini kur, keşfedil ve öğretmeye başla',
        'badge' => '🎓 ÖĞRETMENLER İÇİN',
        'title' => 'Aramıza hoş geldin, {{user_first_name}}!',
        'subtitle' => '<strong style="color:#7c3aed;">{{app_name}}</strong> öğretmen hesabın hazır. Kurulumu tamamlayıp öğrencilere ulaşman için:',
        'f1_t' => 'Herkese açık profilini tamamla', 'f1_d' => 'Biyografini, çalgılarını ve deneyimini ekle, sonra onaya gönder.',
        'f2_t' => 'Uygunluğunu belirle', 'f2_d' => 'Takvimini aç ki öğrenciler doğrudan ders alabilsin.',
        'f3_t' => 'Öğrencilerle bağlan', 'f3_d' => 'Kendi öğrencilerini davet et ya da dizinde keşfedil.',
        'f4_t' => 'İçerik yayınla', 'f4_d' => 'İtibarını güçlendirmek için makale ve dersler paylaş.',
        'btn' => '🎓 Öğretmen Panelini Aç', 'btn_sub' => 'Öğretim merkezin — profil, takvim, öğrenciler ve mesajlar.',
        'promo_t' => '{{app_name}}\'da öğretmenliğe yeni mi başladın?', 'promo_s' => '“Kulağı eğit, stüdyonu büyüt.” Profil, rezervasyon ve ödemelerin nasıl çalıştığını gör.', 'promo_btn' => '📖 Öğretmenlik nasıl işler',
        'ps' => 'Öğretmen hesabınla ilgili soruların mı var? Yanıtla — memnuniyetle yardımcı oluruz. 💜',
    ],

    'premium_intro_teacher' => [
        'subject' => '{{app_name}} Premium ile öğretmenliğini büyüt, {{user_first_name}} ⭐',
        'preheader' => 'Rezervasyon, ödeme bağlantıları, içerik yayını ve öne çıkan profil',
        'badge' => '✦ ÖĞRETMEN PREMIUM',
        'title' => 'Öğretim stüdyonu büyüt',
        'subtitle' => 'Merhaba {{user_first_name}} — birkaç gündür {{app_name}}\'dasın. <strong style="color:#7c3aed;">Premium</strong>\'un öğretmenlere açtıkları:',
        'f1_t' => 'Rezervasyon ve ödeme al', 'f1_d' => 'Öğrenciler kendi ödeme bağlantılarınla ders alıp ödeyebilsin.',
        'f2_t' => 'Sınırsız içerik yayınla', 'f2_d' => 'Uzmanlığını sergilemek için makale, ders ve medya.',
        'f3_t' => 'Öne çıkan, öncelikli profil', 'f3_d' => 'Öğretmen dizininde öne çık ve daha hızlı keşfedil.',
        'f4_t' => 'Öğrenci yönetim araçları', 'f4_d' => 'Ödevler, ilerleme takibi ve mesajlaşma tek yerde.',
        'btn' => '⭐ Öğretmen Premium\'u Gör', 'btn_sub' => 'Öğretmenliğini gelişen bir stüdyoya dönüştür.',
        'p2' => 'Önce bütün resmi görmek ister misin? <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">Öğretmenlik nasıl işler bak</a>.',
    ],

    'trial_ending_teacher' => [
        'subject' => 'Öğretmen Premium denemen {{trial_days_left}} gün sonra bitiyor',
        'preheader' => 'Rezervasyon, ödeme ve içerik yayınını sürdür',
        'title' => 'Öğretmen denemen neredeyse doldu',
        'p1' => 'Ücretsiz <strong>{{app_name}} öğretmen Premium</strong> denemen <strong>{{trial_ends_on}}</strong> tarihinde bitiyor — yani {{trial_days_left}} gün sonra.',
        'p2' => 'Hiçbir ücret alınmayacak: kart bilgilerini hiç almadık. Deneme bitince öğretmen hesabın <strong>Basic</strong>\'e döner; rezervasyon, ödeme bağlantıları ve içerik yayını duraklar — ama profilin ve öğrencilerin olduğu gibi kalır.',
        'btn' => '💳 Öğretmen Premium\'u Sürdür', 'btn_sub' => 'Rezervasyonlarını ve içeriğini kaybetme.',
    ],

    'trial_ended_teacher' => [
        'subject' => 'Öğretmen Premium denemen sona erdi, {{user_first_name}}',
        'preheader' => 'Öğretim profilin güvende — Basic\'e döndün',
        'title' => 'Öğretmen denemen sona erdi',
        'p1' => 'Ücretsiz denemen sona erdi ve öğretmen hesabın yeniden <strong>Basic</strong>\'te. Ücret alınmadı — kart bilgisi hiç istemedik.',
        'p2' => 'Herkese açık profilin, öğrencilerin ve mesajların güvende. Rezervasyon, ödeme bağlantıları ve içerik yayını, yükselttiğin an yeniden açılmaya hazır.',
        'btn' => '⭐ Öğretmen Premium\'u Gör', 'btn_sub' => 'Stüdyo araçlarına tek tıkla geri dön.',
    ],

    // --- Okul ---
    'welcome_school' => [
        'subject' => 'Okullar için {{app_name}}\'ya hoş geldin, {{user_first_name}}! 🏫',
        'preheader' => 'Okulunu kur, öğretmenlerini ekle ve her şeyi tek yerden yönet',
        'badge' => '🏫 OKULLAR İÇİN',
        'title' => 'Aramıza hoş geldin, {{user_first_name}}!',
        'subtitle' => '<strong style="color:#7c3aed;">{{app_name}}</strong> okul hesabın hazır. Kurulumu yapıp öğretmenlerini eklemen için:',
        'f1_t' => 'Okul profilini kur', 'f1_d' => 'Okul bilgilerini ve markanı ekle, sonra onaya gönder.',
        'f2_t' => 'Öğretmenlerini ekle', 'f2_d' => 'Üye öğretmenleri davet et ya da bağla ve tek panelden yönet.',
        'f3_t' => 'Üyelikleri yönet', 'f3_d' => 'Öğretmen ilişkilerini, davetleri ve erişimi merkezi olarak yönet.',
        'f4_t' => 'Keşfedil', 'f4_d' => 'Okulunu herkese açık dizinde sergile.',
        'btn' => '🏫 Okul Panelini Aç', 'btn_sub' => 'Okul merkezin — profil, öğretmenler ve üyelikler.',
        'promo_t' => 'Okullar için {{app_name}}\'ya yeni misin?', 'promo_s' => '“Tüm müzik okulun için tek çatı.” Okullar ve öğretmenlerin nasıl birlikte çalıştığını gör.', 'promo_btn' => '📖 Okullar nasıl işler',
        'ps' => 'Okulunu kurmak için yardıma mı ihtiyacın var? Yanıtla — başlamana yardımcı oluruz. 💜',
    ],

    'premium_intro_school' => [
        'subject' => 'Okulun için {{app_name}} Premium\'un kilidini aç, {{user_first_name}} ⭐',
        'preheader' => 'Sınırsız öğretmen, okul markası ve öncelikli görünürlük',
        'badge' => '✦ OKUL PREMIUM',
        'title' => 'Okulunun ihtiyacı olan her şey',
        'subtitle' => 'Merhaba {{user_first_name}} — <strong style="color:#7c3aed;">Premium</strong>\'un {{app_name}}\'da okulun için açtıkları:',
        'f1_t' => 'Sınırsız üye öğretmen', 'f1_d' => 'Okuluna ihtiyacın kadar öğretmen ekle.',
        'f2_t' => 'Okul markası', 'f2_d' => 'Harmoniva genelinde okulunu kendi kimliğinle sun.',
        'f3_t' => 'Öncelikli görünürlük', 'f3_d' => 'Dizinde üst sıralarda yer al ve keşfedil.',
        'f4_t' => 'Denetim ve araçlar', 'f4_d' => 'Öğretmenleri, üyelikleri ve etkinliği tek panelden yönet.',
        'btn' => '⭐ Okul Premium\'u Gör', 'btn_sub' => 'Müzik okulunun büyümesi için gereken her şey.',
        'p2' => 'Önce görmek ister misin? <a href="{{guide_url}}" style="color:#7c3aed;font-weight:600;">{{app_name}}\'da okullar nasıl işler</a>.',
    ],

    'trial_ending_school' => [
        'subject' => 'Okul Premium denemen {{trial_days_left}} gün sonra bitiyor',
        'preheader' => 'Sınırsız öğretmen ve okul markanı sürdür',
        'title' => 'Okul denemen neredeyse doldu',
        'p1' => 'Ücretsiz <strong>{{app_name}} okul Premium</strong> denemen <strong>{{trial_ends_on}}</strong> tarihinde bitiyor — yani {{trial_days_left}} gün sonra.',
        'p2' => 'Hiçbir ücret alınmayacak: kart bilgilerini hiç almadık. Deneme bitince okul hesabın <strong>Basic</strong>\'e döner — ama okul profilin, öğretmenlerin ve üyeliklerin olduğu gibi kalır.',
        'btn' => '💳 Okul Premium\'u Sürdür', 'btn_sub' => 'Öğretmenlerini ve markanı koru.',
    ],

    'trial_ended_school' => [
        'subject' => 'Okul Premium denemen sona erdi, {{user_first_name}}',
        'preheader' => 'Okul profilin güvende — Basic\'e döndün',
        'title' => 'Okul denemen sona erdi',
        'p1' => 'Ücretsiz denemen sona erdi ve okul hesabın yeniden <strong>Basic</strong>\'te. Ücret alınmadı — kart bilgisi hiç istemedik.',
        'p2' => 'Okul profilin, öğretmenlerin ve üyeliklerin güvende. Okul Premium özellikleri, yükselttiğin an yeniden açılmaya hazır.',
        'btn' => '⭐ Okul Premium\'u Gör', 'btn_sub' => 'Okul araçlarını tek tıkla yeniden etkinleştir.',
    ],

];
