@extends('layouts.standalone')

@section('title', 'Harmoniva Nasıl Çalışır? — Kullanım Rehberi')
@section('description', 'Harmoniva\'nın bölüm bölüm eksiksiz kullanım rehberi — başlangıç, Yapılandırılmış Öğrenme Yolu, 10 pratik egzersizi, Egzersiz Kurulum Stüdyosu, yapay zekâ araçları, Piyano Stüdyosu, müzik oyunları, ilerleme takibi, öğretmenler, okullar ve planlar.')

@section('head')
<style>[x-cloak]{display:none!important}</style>
@endsection

@section('content')

{{-- ============ HERO ============ --}}
<section class="bg-gradient-to-br from-purple-700 via-purple-600 to-purple-800 text-white py-20 px-4 relative overflow-hidden">
    <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 20% 30%, #fff 0, transparent 40%), radial-gradient(circle at 80% 70%, #f97316 0, transparent 40%);"></div>
    <div class="max-w-3xl mx-auto text-center reveal relative">
        <div class="hero-badge inline-flex items-center gap-2 bg-white/10 text-white text-sm font-medium px-4 py-2 rounded-full mb-6">
            <i data-lucide="compass" class="w-4 h-4"></i>
            Nasıl Çalışır?
        </div>
        <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold mb-5">Harmoniva Kullanım Rehberi</h1>
        <p class="text-purple-200 text-lg sm:text-xl max-w-2xl mx-auto leading-relaxed">Bu rehberde Harmoniva’nın özelliklerini kullanmak için bilmeniz gereken tüm detayları bulabilirsiniz. Her egzersizin ne işe yaradığını, nasıl kullanıldığını ve müzik kulağınızı geliştirmeye nasıl katkı sağladığını adım adım keşfedin. Yalnızca birkaç dakika ayırmanız yeterli...</p>
        <div class="flex flex-wrap justify-center gap-4 mt-8 text-sm text-purple-200">
            <span class="flex items-center gap-1.5"><i data-lucide="clock" class="w-4 h-4"></i> 12 dk okuma</span>
            <span class="flex items-center gap-1.5"><i data-lucide="layout-grid" class="w-4 h-4"></i> 15 bölüm</span>
            <span class="flex items-center gap-1.5"><i data-lucide="graduation-cap" class="w-4 h-4"></i> Tüm seviyeler</span>
        </div>
    </div>
</section>

{{-- ============ TABLE OF CONTENTS ============ --}}
<section class="bg-white border-b border-gray-100 py-8 px-4">
    <div class="max-w-4xl mx-auto reveal">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">Bu sayfada</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @php
            $toc = [
                ['n'=>1,'t'=>'Başlarken','ic'=>'rocket'],
                ['n'=>2,'t'=>'Paneliniz','ic'=>'layout-dashboard'],
                ['n'=>3,'t'=>'Yapılandırılmış Öğrenme Yolu','ic'=>'route'],
                ['n'=>4,'t'=>'Pratik Egzersizleri','ic'=>'music'],
                ['n'=>5,'t'=>'Egzersiz Kurulum Stüdyosu','ic'=>'sliders-horizontal'],
                ['n'=>6,'t'=>'Yapay Zekâ Destekli Antrenman','ic'=>'sparkles'],
                ['n'=>7,'t'=>'Sanal Piyano Stüdyosu','ic'=>'piano'],
                ['n'=>8,'t'=>'Müzik Oyunları','ic'=>'gamepad-2'],
                ['n'=>9,'t'=>'İlerleme ve Başarımlar','ic'=>'trending-up'],
                ['n'=>10,'t'=>'Öğretmen Bul','ic'=>'users'],
                ['n'=>11,'t'=>'Topluluk Akışı','ic'=>'rss'],
                ['n'=>12,'t'=>'Ödevler, Mesajlar ve Dersler','ic'=>'clipboard-list'],
                ['n'=>13,'t'=>'Öğretmenler için Harmoniva','ic'=>'briefcase'],
                ['n'=>14,'t'=>'Müzik Okulları için Harmoniva','ic'=>'building-2'],
                ['n'=>15,'t'=>'Planlar ve Erişim','ic'=>'tag'],
            ];
            @endphp
            @foreach($toc as $item)
            <a href="#section-{{ $item['n'] }}" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gray-50 hover:bg-purple-50 hover:text-purple-700 transition-colors group">
                <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="{{ $item['ic'] }}" class="w-4 h-4"></i>
                </span>
                <span class="text-sm font-medium text-gray-700 group-hover:text-purple-700 leading-tight">{{ $item['n'] }}. {{ $item['t'] }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>

{{-- ============ SECTIONS ============ --}}
<div class="bg-[#FAF7F2] py-16 px-4">
    <div class="max-w-3xl mx-auto space-y-16 sm:space-y-20">

        @php
        if (!function_exists('guideBtn')) {
        function guideBtn($url, $label, $icon = 'arrow-right') {
            return '<a href="'.$url.'" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl text-white font-semibold text-sm shadow-lg shadow-purple-500/25 hover:-translate-y-0.5 transition-all w-full sm:w-auto" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">'.$label.' <i data-lucide="'.$icon.'" class="w-4 h-4"></i></a>';
        }
        }
        if (!function_exists('guideBadge')) {
        function guideBadge($label, $tone = 'free') {
            $tones = [
                'free' => 'bg-green-100 text-green-700',
                'premium' => 'bg-purple-100 text-purple-700',
                'teacher' => 'bg-blue-100 text-blue-700',
                'school' => 'bg-amber-100 text-amber-700',
                'guest' => 'bg-gray-100 text-gray-600',
            ];
            return '<span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold uppercase tracking-wider '.($tones[$tone] ?? $tones['free']).'">'.$label.'</span>';
        }
        }
        $faqLabel = 'Sık sorulan sorular';
        // Erişim sayıları merkezi plan yapılandırmasından gelir; rehber gerçek limitlerden asla sapmaz.
        $freePlan   = config('plans.user.free');
        $teacherFree = config('plans.teacher.free');
        $schoolFree  = config('plans.school.free');
        $guestPlaysPerGame = (int) config('plans.guest.games_daily_plays_per_type', 1);
        $guestPlaysTotal   = $guestPlaysPerGame * count(\App\Http\Controllers\GameController::GAMES);
        // Her bölüm başlığı anlattığı özelliğin sayfasına doğrudan bağlanır.
        $sectionLinks = [
            1 => route('register'),
            2 => route('dashboard'),
            3 => route('learn'),
            4 => route('exercise-setup.index'),
            5 => route('exercise-setup.index'),
            6 => route('ai.exercises'),
            7 => route('piano.studio'),
            8 => route('games.index'),
            9 => route('progress'),
            10 => route('teachers.directory'),
            11 => route('feed'),
            12 => route('assignments.index'),
            13 => route('page.teachers-solution'),
            14 => route('page.schools'),
            15 => route('pricing.index'),
        ];
        @endphp

        {{-- ═══════════ 1. BAŞLARKEN ═══════════ --}}
        <article id="section-1" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[1] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">1</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Başlarken</h2>
                    <p class="text-gray-500 text-sm">Harmoniva nedir, hesap türleri neler ve ilk beş dakikanız nasıl geçmeli?</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-4">Harmoniva, her seviyeden müzisyen için bir kulak eğitimi platformudur — kendi başına çalışan öğrenciler, stüdyosunu yöneten öğretmenler ve sınıflarını takip eden müzik okulları. Dinlersiniz, cevaplarsınız ve anında geri bildirim alırsınız; sorular gerçek porte üzerinde gösterildiği için gözünüz ve kulağınız birlikte öğrenir.</p>

            <p class="text-gray-600 leading-relaxed mb-6">Kayıt olurken (e-posta veya Google ile) üç hesap türünden birini seçersiniz — bunlar kayıt sayfasında göreceğiniz seçeneklerin aynısıdır:</p>

            {{-- account type cards --}}
            <div class="grid sm:grid-cols-3 gap-3 mb-6">
                <div class="light-card rounded-2xl p-4">
                    <div class="w-9 h-9 rounded-lg bg-green-100 text-green-600 flex items-center justify-center mb-2"><i data-lucide="user" class="w-4 h-4"></i></div>
                    <p class="font-bold text-gray-900 text-sm">Öğrenci Paneli</p>
                    <p class="text-xs text-gray-500 mt-1">Kişisel öğrenme alanınız — pratik yapın, ilerlemenizi takip edin, dilerseniz öğretmenlerle bağlantı kurun.</p>
                </div>
                <div class="light-card rounded-2xl p-4">
                    <div class="w-9 h-9 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-2"><i data-lucide="briefcase" class="w-4 h-4"></i></div>
                    <p class="font-bold text-gray-900 text-sm">Öğretmen Paneli</p>
                    <p class="text-xs text-gray-500 mt-1">Öğrencileriniz için kapsamlı yönetim araçları — öğrenciler, ödevler ve herkese açık öğretmen profili.</p>
                </div>
                <div class="light-card rounded-2xl p-4">
                    <div class="w-9 h-9 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center mb-2"><i data-lucide="building-2" class="w-4 h-4"></i></div>
                    <p class="font-bold text-gray-900 text-sm">Müzik Okulu Paneli</p>
                    <p class="text-xs text-gray-500 mt-1">Çok öğretmenli kurumlar için merkezi yönetim — öğretmenleri, öğrencileri ve okul genelindeki etkinliği yönetin.</p>
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">İlk beş dakikanız</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Ücretsiz bir hesap oluşturun ve e-postanızı doğrulayın — kredi kartı gerekmez.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Rehberli bir başlangıç için <strong>Öğrenme Yolu</strong>'nu açın veya doğrudan istediğiniz pratik egzersizine geçin.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Her soruyu duymak için çal düğmesine basın — dilediğiniz kadar tekrar dinleyebilirsiniz. Özellikle harmonik egzersizler için kulaklık önerilir.</span></li>
                </ul>
            </div>

            <p class="text-gray-600 text-sm leading-relaxed mb-6">{!! guideBadge('Misafir','guest') !!} Hesap açmadan her şeyi keşfedebilirsiniz: günde {{ config('plans.guest.learning_path_daily_sessions') }} Öğrenme Yolu ve {{ config('plans.guest.studio_daily_sessions') }} Egzersiz Stüdyosu oturumu (her biri 5 soru), her oyunu günde bir kez (1. seviye) ve Piyano Stüdyosu'nu sınırsız kullanabilirsiniz. Misafir limitleri her gün yenilenir. Puanlarınızı kaydetmek, üst seviyelerin kilidini açmak, seri oluşturmak ve ilerlemenizi takip etmek için ücretsiz bir hesap gerekir.</p>

            {!! guideBtn(route('register'), 'Ücretsiz Hesap Oluşturun', 'user-plus') !!}

            @include('pages.partials.guide-faq', ['label' => $faqLabel, 'faqs' => [
                ['q'=>'Harmoniva\'yı denemek için hesap gerekli mi?','a'=>'Hayır — misafir olarak pratik egzersizlerini açabilir, sanal piyanoyu çalabilir ve her oyunu bir kez deneyebilirsiniz. İlerlemenizi, puanlarınızı ve serinizi kaydeden şey (ücretsiz) hesabınızdır.'],
                ['q'=>'Hangi hesap türünü seçmeliyim?','a'=>'Çoğu kullanıcı Öğrenci Paneli ile başlar. Ders veriyorsanız Öğretmen veya Müzik Okulu seçin — öğrenci olarak başlasanız bile mevcut hesabınızdan daha sonra öğretmen hesabı açabilirsiniz.'],
                ['q'=>'Başlamak gerçekten ücretsiz mi?','a'=>'Evet. Ücretsiz plan; Yapılandırılmış Öğrenme Yolu\'nu, tüm pratik egzersiz türlerini (günlük limitlerle), Egzersiz Kurulum Stüdyosu\'nu, oyunları ve temel ilerleme takibini kapsar. Kredi kartı istenmez.'],
            ]])
        </article>

        {{-- ═══════════ 2. PANELİNİZ ═══════════ --}}
        <article id="section-2" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[2] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">2</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Paneliniz</h2>
                    <p class="text-gray-500 text-sm">Ana üssünüz — her şeyin tek bakışta özeti.</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">Giriş yaptığınız anda Panel; gün serinizi, bugünkü pratik hedefinizi, kaydedilen dakikalarınızı, kazandığınız deneyim puanlarını ve başarımlarınızı gösterir. <strong>Beceri Ustalığı</strong> paneli beceriler genelindeki durumunuzu özetler; <strong>Hızlı İşlemler</strong> ise sizi tek tıkla Öğrenme Yolu'na, Egzersiz Kurulumu'na, AI Egzersizleri'ne, Müzik Asistanı'na ve Piyano Stüdyosu'na götürür — kaldığınız yerden devam etme kısayolu da cabası.</p>

            {{-- visual mockup --}}
            <div class="light-card rounded-2xl p-5 mb-6">
                <div class="flex flex-wrap items-center justify-between gap-y-2 mb-4">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider">Tekrar hoş geldiniz</p>
                        <p class="font-bold text-gray-900">Kulağınızı çalıştırmaya hazır mısınız?</p>
                    </div>
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-orange-50 text-orange-600 text-sm font-bold">
                        <i data-lucide="flame" class="w-4 h-4"></i> 7 günlük seri
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    @foreach([['Öğren','route','purple'],['Stüdyo','sliders-horizontal','purple'],['AI Modu','sparkles','orange'],['Piyano','piano','purple']] as $q)
                    <div class="rounded-xl bg-gray-50 p-3 text-center">
                        <div class="w-9 h-9 mx-auto rounded-lg bg-{{ $q[2] }}-100 text-{{ $q[2] }}-600 flex items-center justify-center mb-2"><i data-lucide="{{ $q[1] }}" class="w-4 h-4"></i></div>
                        <p class="text-xs font-semibold text-gray-700">{{ $q[0] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">Nasıl kullanılır</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Düzenli kalmak için serinizi ve bugünkü pratik hedefinizi kontrol edin.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Hızlı İşlemler kutucuklarıyla uygulamanın istediğiniz bölümünü tek tıkla açın.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Sırada hangi becerinin ilgi beklediğini görmek için Beceri Ustalığı'na göz atın.</span></li>
                </ul>
            </div>

            {!! guideBtn(route('dashboard'), 'Paneli Aç') !!}

            @include('pages.partials.guide-faq', ['label' => $faqLabel, 'faqs' => [
                ['q'=>'Paneli görmek için hesap gerekli mi?','a'=>'Evet — Panel size özeldir, bu yüzden ücretsiz bir hesap gerekir. Kayıt bir dakikadan kısa sürer.'],
                ['q'=>'Seri sayacı nedir?','a'=>'Kaç gün üst üste pratik yaptığınızı takip eder. Her gün en az bir egzersiz tamamlamak seriyi canlı tutar — alışkanlık kazanmanın kanıtlanmış, basit bir yoludur.'],
                ['q'=>'Günlük pratik hedefi nedir?','a'=>'Panelde gösterilen günlük dakika hedefidir. Pratik yaptıkça çubuk dolar; böylece günün hedefine ne kadar yaklaştığınızı net olarak görürsünüz.'],
            ]])
        </article>

        {{-- ═══════════ 3. YAPILANDIRILMIŞ ÖĞRENME YOLU ═══════════ --}}
        <article id="section-3" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[3] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">3</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Yapılandırılmış Öğrenme Yolu</h2>
                    <p class="text-gray-500 text-sm">Başlangıçtan ileri seviyeye, adım adım rehberli bir müfredat. {!! guideBadge('Ücretsiz') !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-4">Nereden başlayacağınızı bilmiyorsanız buradan başlayın. Öğrenme Yolu; aralıklar, akorlar, gamlar, ritim, dikte ve daha fazlası için her beceri alanına özel, kısa ve odaklı derslerden oluşan hazır bir müfredattır. Beceriler kademeli olarak tanıtılır: her ders daha önce öğrendiğiniz kavramların üzerine kurulur ve bir dersi tamamlamak dizideki bir sonraki dersin kilidini açar.</p>

            <p class="text-gray-600 leading-relaxed mb-6"><strong>Not:</strong> Bu yapılandırılmış müfredat, <a href="#section-6" class="text-purple-600 font-semibold hover:underline">6. bölümde</a> anlatılan yapay zekâ destekli kişiselleştirilmiş antrenmandan farklıdır. Öğrenme Yolu herkes için aynı olan, uzmanlar tarafından tasarlanmış sabit bir dizidir; yapay zekâ araçları ise <em>sizin</em> sonuçlarınıza göre kişiselleştirilmiş oturumlar ve haftalık planlar oluşturur.</p>

            {{-- visual: lesson track --}}
            <div class="light-card rounded-2xl p-5 mb-6">
                <div class="space-y-3">
                    @foreach([['Tam aralıklar','done'],['Büyük ve küçük 2\'liler','done'],['Büyük ve küçük 3\'lüler','active'],['Triton ve ötesi','locked']] as $l)
                    <div class="flex items-center gap-3 p-3 rounded-xl {{ $l[1]==='active' ? 'bg-purple-50 border border-purple-200' : 'bg-gray-50' }}">
                        <span class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 {{ $l[1]==='done' ? 'bg-green-100 text-green-600' : ($l[1]==='active' ? 'bg-purple-600 text-white' : 'bg-gray-200 text-gray-400') }}">
                            <i data-lucide="{{ $l[1]==='done' ? 'check' : ($l[1]==='active' ? 'play' : 'lock') }}" class="w-4 h-4"></i>
                        </span>
                        <span class="text-sm font-medium {{ $l[1]==='locked' ? 'text-gray-400' : 'text-gray-800' }}">{{ $l[0] }}</span>
                        @if($l[1]==='active')<span class="ml-auto text-xs font-bold text-purple-600">Devam et →</span>@endif
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">Nasıl kullanılır</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span><strong>Öğren</strong> sayfasını açın ve geliştirmek istediğiniz beceri alanında kilidi açılmış bir sonraki dersi seçin.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Kısa tanıtımı okuyun, ardından <strong>Başlat</strong>'a basarak sorulara geçin.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Dersi tamamlayarak bir sonraki adımın kilidini açın — gerektiği kadar tekrar deneyebilirsiniz.</span></li>
                </ul>
            </div>

            {!! guideBtn(route('learn'), 'Öğrenme Yolunu Aç') !!}

            @include('pages.partials.guide-faq', ['label' => $faqLabel, 'faqs' => [
                ['q'=>'Dersleri sırayla mı yapmak zorundayım?','a'=>'Her beceri alanının kendi içinde evet — her ders bir sonrakinin kilidini açar; böylece zorluk, ileri düzey konular erkenden karşınıza çıkmadan kademeli olarak artar. Farklı beceri alanlarında paralel çalışabilirsiniz.'],
                ['q'=>'Bir dersi geçemezsem ne olur?','a'=>'Hiçbir şey — sadece yeniden denersiniz. Öğrenme Yolu ceza için değil pratik için tasarlandı; her dersi istediğiniz kadar tekrar deneyebilirsiniz.'],
                ['q'=>'Öğrenme Yolu ücretsiz mi?','a'=>'Evet. Öğrenme Yolu ücretsiz planın parçasıdır: günde en fazla '.$freePlan['learning_path_daily_sessions'].' oturum, her oturumda '.$freePlan['session_question_cap'].' soru. Misafirler de günde '.config('plans.guest.learning_path_daily_sessions').' oturum deneyebilir. Premium günlük limitleri kaldırır.'],
                ['q'=>'Bunun AI destekli plandan farkı ne?','a'=>'Yapılandırılmış Öğrenme Yolu her kullanıcı için aynı olan, uzman tasarımı bir dizidir. Yapay zekâ araçları (Premium) ise kendi sonuçlarınızı analiz ederek kişiselleştirilmiş oturumlar ve haftalık pratik planı üretir — Yapay Zekâ Destekli Antrenman bölümüne bakın.'],
            ]])
        </article>

        {{-- ═══════════ 4. PRATİK EGZERSİZLERİ ═══════════ --}}
        <article id="section-4" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[4] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">4</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Pratik Egzersizleri</h2>
                    <p class="text-gray-500 text-sm">On temel kulak eğitimi çalışması — her biri nedir ve neyi geliştirir?</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">Bunlar Harmoniva'nın kalbidir. Her egzersiz bir ses çalar, gerçek porte gösterimi sunar ve cevabınıza anında geri bildirim verir — doğruysa yeşil, yanlışsa doğru cevabı gösteren kırmızı. Etiketli düğmelerle veya ekrandaki piyano klavyesiyle cevap verirsiniz; başlamak için nota okumayı bilmeniz gerekmez. Aşağıdaki her kart ilgili egzersizi doğrudan açar:</p>

            {{-- exercise cards: what it is + what it trains + direct link --}}
            <div class="grid sm:grid-cols-2 gap-3 mb-6">
                @foreach([
                    ['Tek Nota','music-2','single-note-practice','Duyduğunuz tek notayı tanıyın. Perde tanıma ve perde hafızasını geliştirir — diğer tüm becerilerin üzerine kurulduğu temel.'],
                    ['Melodik Aralık','trending-up','melodic-interval-practice','İki nota art arda çalınır; aralarındaki aralığı adlandırın. Melodileri kulaktan öğrenmenin temel becerisi olan göreli işitmeyi geliştirir.'],
                    ['Harmonik Aralık','layers','harmonic-interval-practice','İki nota aynı anda çalınır; aralığı adlandırın. Armonik duymayı geliştirir — kulaklık şiddetle önerilir.'],
                    ['Aralık Yönü','arrow-up-down','interval-direction-practice','İlk notadan başlayarak iki nota art arda çalınır. İkinci notanın ilk notaya göre tiz (çıkıcı), pes (inici) veya aynı ses olup olmadığına karar verin. Melodik kontur algısını geliştirir.'],
                    ['Aralık Karşılaştırma','git-compare','interval-comparison-practice','İki aralık dinleyin ve hangisinin daha geniş olduğuna karar verin. Aralık genişliğini ince ayrımla duymayı geliştirir.'],
                    ['Aralık Kurma','wrench','interval-construction-practice','Verilen başlangıç notasından istenen aralığı oluşturarak doğru ikinci notayı bulun. Pasif tanımayı aktif aralık bilgisine dönüştürür.'],
                    ['Akorlar','grid-3x3','chord-practice','Bir akor dinleyin ve türünü belirleyin — majör ve minör üçlü akorlardan yedili akorlara ve çevrimlere kadar. Armoni tanımayı geliştirir.'],
                    ['Gamlar','list-music','scale-practice','Bir gam dinleyin ve hangisi olduğunu belirleyin — majör, minör türleri, modlar, pentatonik ve daha fazlası. Tonal ve modal duymayı geliştirir.'],
                    ['Ritim','activity','rhythm-practice','Bir ritim dinleyin ve farklı ölçüler ile nota değerleri arasından doğru yazımla eşleştirin. Ritim okumayı ve iç nabzı geliştirir.'],
                    ['Melodik Dikte','pen-line','melodic-dictation','Kısa bir melodi dinleyin ve perdeleriyle, ritmiyle yeniden oluşturun. Diğer tüm becerileri birleştiren en kapsamlı kulak antrenmanı.'],
                ] as $ex)
                <a href="{{ route('practice', $ex[2]) }}" class="light-card rounded-2xl p-4 group hover:border-purple-300 hover:shadow-md hover:-translate-y-0.5 transition-all flex flex-col">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0"><i data-lucide="{{ $ex[1] }}" class="w-4 h-4"></i></span>
                        <span class="text-sm font-bold text-gray-900 group-hover:text-purple-700 transition-colors">{{ $ex[0] }}</span>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed flex-1">{{ $ex[3] }}</p>
                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-purple-600 mt-3">Deneyin: {{ $ex[0] }} <i data-lucide="arrow-right" class="w-3 h-3 transition-transform group-hover:translate-x-0.5"></i></span>
                </a>
                @endforeach
            </div>

            <p class="text-gray-600 text-sm leading-relaxed mb-6">{!! guideBadge('Ücretsiz') !!} On egzersiz türünün tamamı ücretsiz plandadır; günde en fazla {{ $freePlan['learning_path_daily_sessions'] }} Öğrenme Yolu ve {{ $freePlan['studio_daily_sessions'] }} Stüdyo oturumu (her biri {{ $freePlan['session_question_cap'] }} soru) yapılabilir. {!! guideBadge('Premium','premium') !!} Premium günlük limitleri kaldırır.</p>

            @include('pages.partials.guide-faq', ['label' => $faqLabel, 'faqs' => [
                ['q'=>'Sesi tekrar dinleyebilir miyim?','a'=>'Elbette. Cevaplamadan önce çal düğmesine istediğiniz kadar basabilirsiniz — tekrar dinlemenin hiçbir cezası yoktur. Kulak tam da tekrarla öğrenir.'],
                ['q'=>'Harmonik ve melodik ne demek?','a'=>'Melodik, notaların art arda çalınması; harmonik ise aynı anda çalınması demektir. Harmonik egzersizler daha zorlayıcıdır, bu yüzden kulaklık önerilir.'],
                ['q'=>'Günlük bir sınır var mı?','a'=>'Ücretsiz hesaplar günde '.$freePlan['learning_path_daily_sessions'].' Öğrenme Yolu ve '.$freePlan['studio_daily_sessions'].' Stüdyo oturumu yapabilir (her biri '.$freePlan['session_question_cap'].' soru). Premium, tüm türlerde sınırsız pratik imkânı sunar.'],
                ['q'=>'Nota okumayı bilmem gerekiyor mu?','a'=>'Hayır. Etiketli düğmelerle veya piyano klavyesiyle cevap verebilirsiniz; böylece tamamen kulaktan çalışırken ekrandaki porteden nota yazısını da yavaş yavaş kavrarsınız.'],
            ]])
        </article>

        {{-- ═══════════ 5. EGZERSİZ KURULUM STÜDYOSU ═══════════ --}}
        <article id="section-5" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[5] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">5</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Egzersiz Kurulum Stüdyosu</h2>
                    <p class="text-gray-500 text-sm">Her egzersiz türü için tam istediğiniz gibi özel bir çalışma oluşturun. {!! guideBadge('Ücretsiz') !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">Stüdyo <strong>tüm</strong> pratik türlerini kapsar — aralıklar (melodik, harmonik, yön, kurma, karşılaştırma), tek notalar, akorlar, gamlar, ritim, melodik dikte ve hatta bir piyano pratiği modu. Her tür için oturumunuzda tam olarak nelerin sorulacağını siz belirlersiniz:</p>

            {{-- category options --}}
            <div class="grid sm:grid-cols-2 gap-3 mb-6">
                @foreach([
                    ['Aralıklar','trending-up','Aralık havuzunu, yönü (çıkıcı/inici/karışık), anahtarı ve ses bölgesini seçin — melodik, harmonik, yön, kurma ve karşılaştırma çalışmaları için ayrı ayrı.'],
                    ['Tek Notalar','music-2','Tam olarak hangi notaların sorulacağını, ses aralığını ve anahtarı seçin; nasıl cevap vereceğinizi de — etiketli nota adı düğmeleri veya etiketsiz klavye.'],
                    ['Akorlar','grid-3x3','Majör, minör, eksik ve artık üçlü akorlar; sus akorları; yedili akorlar (dominant, majör 7, minör 7, yarı eksik, eksik); çevrimler ve ses düzeni.'],
                    ['Gamlar','list-music','Majör; doğal/armonik/melodik minör; yedi modun tamamı; majör ve minör pentatonik; blues, kromatik ve tam ton — artı yön seçimi.'],
                    ['Ritim','activity','Ölçü birimleri, tempo (BPM), nota değerleri, esler, noktalı notalar, üçlemeler ve metronom sayımı.'],
                    ['Melodik Dikte','pen-line','Ton, majör/minör mod, anahtar, ölçü birimi ve ritim değerleri — yalnızca perdeden oluşan dizilerden ritimli tam melodilere kadar.'],
                ] as $cat)
                <div class="light-card rounded-2xl p-4">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0"><i data-lucide="{{ $cat[1] }}" class="w-4 h-4"></i></span>
                        <span class="text-sm font-bold text-gray-900">{{ $cat[0] }}</span>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed">{{ $cat[2] }}</p>
                </div>
                @endforeach
            </div>

            {{-- visual: config panel --}}
            <div class="light-card rounded-2xl p-5 mb-6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-4">Oturumunuzu yapılandırın</p>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500 mb-1.5">Dahil edilecek aralıklar</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach(['m2','M2','m3','M3','P4','P5'] as $i => $chip)
                            <span class="px-2.5 py-1 rounded-lg text-xs font-semibold {{ $i < 4 ? 'bg-purple-600 text-white' : 'bg-gray-100 text-gray-500' }}">{{ $chip }}</span>
                            @endforeach
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div><p class="text-xs text-gray-500 mb-1.5">Anahtar</p><div class="px-3 py-2 rounded-lg bg-gray-50 text-sm text-gray-700 font-medium">Sol anahtarı (G3–G5)</div></div>
                        <div><p class="text-xs text-gray-500 mb-1.5">Soru sayısı</p><div class="px-3 py-2 rounded-lg bg-gray-50 text-sm text-gray-700 font-medium">20</div></div>
                    </div>
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">Nasıl kullanılır</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Bir egzersiz türü seçin, ardından duymak istediğiniz sesleri işaretleyin.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Hedefinize uygun anahtar, ses aralığı, soru sayısı ve zorluk düzeyini ayarlayın.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Pratiğe başlamak için <strong>Başlat</strong>'a basın — veya yapılandırmayı bir sonraki sefer için şablon olarak kaydedin.</span></li>
                </ul>
            </div>

            {!! guideBtn(route('exercise-setup.index'), 'Egzersiz Kurulumunu Aç') !!}

            @include('pages.partials.guide-faq', ['label' => $faqLabel, 'faqs' => [
                ['q'=>'Neleri özelleştirebilirim?','a'=>'Karşınıza çıkacak aralıkları, akorları, gamları veya ritimleri; anahtar ve ses bölgesini; soru sayısını; yönü ve yanlış cevapların ne kadar aldatıcı olacağını — her egzersiz türü için ayrı ayrı.'],
                ['q'=>'Ayarlarımı kaydedebilir miyim?','a'=>'Evet — herhangi bir yapılandırmayı adlandırılmış bir şablon olarak kaydedin ve anında yeniden başlatın. Ücretsiz hesaplar en fazla '.$freePlan['saved_plans_limit'].' şablon saklayabilir; Premium\'da sınır yoktur.'],
                ['q'=>'Stüdyo ücretsiz planda var mı?','a'=>'Evet, Stüdyonun kendisi herkese açıktır. Ücretsiz planın günlük pratik limitleri ve '.$freePlan['saved_plans_limit'].' şablonluk üst sınırı geçerlidir; Premium her ikisini de kaldırır.'],
            ]])
        </article>

        {{-- ═══════════ 6. YAPAY ZEKÂ DESTEKLİ ANTRENMAN ═══════════ --}}
        <article id="section-6" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[6] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0" style="background:linear-gradient(135deg,#9333ea,#f97316);">6</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Yapay Zekâ Destekli Antrenman</h2>
                    <p class="text-gray-500 text-sm">Üç ayrı yapay zekâ aracı — egzersizler, koçluk ve bir müzik asistanı.</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <div class="space-y-4 mb-6">
                <div class="light-card rounded-2xl p-5">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center"><i data-lucide="sparkles" class="w-4 h-4"></i></span>
                        <span class="font-bold text-gray-900">AI Egzersizleri</span>
                        {!! guideBadge('Premium','premium') !!}
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">Yapay zekâ, pratik geçmişinizi analiz ederek size özel bir antrenman oturumu oluşturur. Kapsanmasını istediğiniz becerileri seçin; oluşturulan soru seti en çok kaçırdığınız seslere ağırlık verir — hiçbir manuel ayar gerekmez.</p>
                </div>
                <div class="light-card rounded-2xl p-5">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="w-8 h-8 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center"><i data-lucide="bot" class="w-4 h-4"></i></span>
                        <span class="font-bold text-gray-900">AI Koç</span>
                        {!! guideBadge('Ücretsiz · sınırlı') !!}
                        {!! guideBadge('Premium · tam','premium') !!}
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">AI Koç; profilinizi, anket cevaplarınızı ve yakın dönem pratik geçmişinizi kişiselleştirilmiş bir <strong>7 günlük haftalık pratik planına</strong> dönüştürür — odak alanları ve uygulanabilir ipuçlarıyla birlikte. Sabit Yapılandırılmış Öğrenme Yolu'nun kişiselleştirilmiş karşılığı budur.</p>
                </div>
                <div class="light-card rounded-2xl p-5">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center"><i data-lucide="message-circle" class="w-4 h-4"></i></span>
                        <span class="font-bold text-gray-900">Müzik Asistanı</span>
                        {!! guideBadge('Ücretsiz hesap') !!}
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">Müzik sorularınız için bir sohbet asistanı. Müzik teorisi, kulak eğitimi ve nota yazısı konularında ihtiyaç duyduğunuz an anında açıklamalar, pratik önerileri ve yardım alın — açıklamalarını seviyenize göre uyarlar.</p>
                </div>
            </div>

            {{-- visual: AI insight card --}}
            <div class="rounded-2xl p-5 mb-6 text-white relative overflow-hidden" style="background:linear-gradient(135deg,#7c3aed,#9333ea);">
                <div class="absolute -right-6 -top-6 w-28 h-28 rounded-full bg-orange-400/20"></div>
                <div class="flex items-center gap-2 mb-3 relative">
                    <i data-lucide="sparkles" class="w-4 h-4 text-orange-300"></i>
                    <span class="text-xs font-bold uppercase tracking-wider text-purple-200">Örnek AI Koç önerisi</span>
                </div>
                <p class="text-sm leading-relaxed relative">"Tam aralıklarda çok iyisiniz, ancak küçük 6'lılar sizi sık yanıltıyor. Bu haftanın planında 6'lılara odaklanan kısa günlük oturumlar var — günde 10 dakika bile fark yaratacaktır."</p>
                <div class="mt-4 flex gap-2 relative">
                    <span class="px-3 py-1.5 rounded-lg bg-white/15 text-xs font-semibold backdrop-blur-sm">Odak: Küçük 6'lı</span>
                    <span class="px-3 py-1.5 rounded-lg bg-orange-400 text-xs font-semibold text-white">Haftalık plan →</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                {!! guideBtn(route('ai.exercises'), 'AI Egzersizlerini Aç', 'sparkles') !!}
                <a href="{{ route('ai-coach.index') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">AI Koç'u Aç <i data-lucide="bot" class="w-4 h-4"></i></a>
                <a href="{{ route('ai-chat.index') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">Müzik Asistanı'nı Aç <i data-lucide="message-circle" class="w-4 h-4"></i></a>
            </div>

            <div class="mt-6">
            @include('pages.partials.guide-faq', ['label' => $faqLabel, 'faqs' => [
                ['q'=>'Yapay zekâ zayıf noktalarımı nereden biliyor?','a'=>'Cevap geçmişinizi analiz ederek en sık hangi sesleri karıştırdığınızı saptar; ardından tam olarak bunlara daha fazla zaman ayıran oturumlar ve planlar oluşturur.'],
                ['q'=>'Hangi yapay zekâ özellikleri ücretsiz?','a'=>'Müzik Asistanı giriş yapmış tüm kullanıcılara açıktır; AI Koç ücretsiz planda sınırlı erişim sunar. AI Egzersizleri ve tam AI Koç erişimi Premium özellikleridir.'],
                ['q'=>'Yapay zekâ bir öğretmenin yerini tutar mı?','a'=>'Hayır — onu tamamlar. Yapay zekâ, pratik verilerinizdeki örüntüleri saptamada ve teori sorularını yanıtlamada çok iyidir; gerçek bir öğretmen ise teknik, müzikalite ve hedefler konusunda geri bildirim getirir. Harmoniva ikisini de destekler.'],
            ]])
            </div>
        </article>

        {{-- ═══════════ 7. SANAL PİYANO STÜDYOSU ═══════════ --}}
        <article id="section-7" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[7] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">7</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Sanal Piyano Stüdyosu</h2>
                    <p class="text-gray-500 text-sm">Sesleri özgürce keşfetmek için çalınabilir, tam boy bir klavye. {!! guideBadge('Ücretsiz · hesap gerekmez') !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">Bazen bir notayı, aralığı veya akoru yalnızca <em>duymak</em> istersiniz. Piyano Stüdyosu, gerçekçi sese sahip duyarlı bir ekran klavyesidir — çaldıkça nota adı ekranda belirir. Kendinizi sınamak, kulaktan akor kurmak veya diğer egzersizlerde çalışırken referans olarak kullanmak için birebirdir. Ayrıca Egzersiz Kurulum Stüdyosu'ndan erişilebilen bir piyano pratiği modu da vardır.</p>

            {{-- visual: mini piano --}}
            <div class="light-card rounded-2xl p-5 mb-6">
                <div class="rounded-xl bg-gray-900 p-4">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-xs text-gray-400 uppercase tracking-widest flex items-center gap-1.5"><i data-lucide="piano" class="w-3.5 h-3.5"></i> Piyano Stüdyosu</span>
                        <span class="text-xs font-semibold text-purple-400">C4 · E4 · G4</span>
                    </div>
                    <div class="relative h-24 flex rounded-lg overflow-hidden">
                        @for($i=0;$i<8;$i++)
                        <div class="flex-1 bg-gradient-to-b {{ in_array($i,[0,2,4]) ? 'from-purple-100 to-purple-200' : 'from-gray-50 to-white' }} border-r border-gray-300 last:border-r-0"></div>
                        @endfor
                        @foreach([12,25,50,62,75] as $left)
                        <div class="absolute top-0 h-14 w-[9%] bg-gray-800 rounded-b" style="left:{{ $left }}%"></div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">Nasıl kullanılır</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Tuşlara tıklayın veya dokunun — çaldıkça nota adı ekranda belirir.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Aralıkları ve akorları duymak için iki veya daha fazla tuşa birlikte basın.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Mobilde daha tiz veya pes notalara ulaşmak için klavyeyi yana kaydırın.</span></li>
                </ul>
            </div>

            {!! guideBtn(route('piano.studio'), 'Piyano Stüdyosunu Aç', 'piano') !!}

            @include('pages.partials.guide-faq', ['label' => $faqLabel, 'faqs' => [
                ['q'=>'Telefonda çalışır mı?','a'=>'Evet. Klavye dokunmatik ekrana uygundur ve yana kaydırılabilir; böylece her ekran boyutunda tüm ses aralığına ulaşabilirsiniz.'],
                ['q'=>'Kulaklık gerekli mi?','a'=>'Zorunlu değil ama önerilir — kulaklık, birbirine yakın notaları ve bir akorun içindeki tek tek sesleri ayırt etmeyi çok kolaylaştırır.'],
                ['q'=>'Hesap gerekli mi?','a'=>'Hayır — Piyano Stüdyosu misafirler dahil herkese açıktır.'],
            ]])
        </article>

        {{-- ═══════════ 8. MÜZİK OYUNLARI ═══════════ --}}
        <article id="section-8" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[8] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">8</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Müzik Oyunları</h2>
                    <p class="text-gray-500 text-sm">Gerçek kulak becerilerini geliştiren altı atari tarzı oyun.</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">Pratik yapmak ödev gibi hissettirmek zorunda değil. Her oyun, pratik egzersizleriyle aynı ses motorunu ve becerileri kullanır — gerçekten antrenman yaparsınız, sadece biraz daha fazla adrenalinle. Her oyunda kişisel rekorunuz kaydedilir; Premium ise global sıralamanın (Şöhret Salonu) kilidini açar.</p>

            {{-- game cards --}}
            <div class="grid sm:grid-cols-2 gap-3 mb-6">
                @foreach([
                    ['Note Fall','arrow-down-to-line','Nota okuma ve refleks','Notalar ekranın üstünden düşer — yere ulaşmadan doğru piyano tuşuna basarak yakalayın. 5 seviye; genişleyen ses aralığı, arızalı notalar ve melodik diziler ekler.'],
                    ['Note Rush','zap','Hızlı nota tanıma','Bir nota çalınır — olabildiğince hızlı tanıyın. Seriler puan çarpanları kazandırır; süre 60 saniyedir.'],
                    ['Melody Memory','music','Melodik hafıza','Bir melodi dinleyin, sonra piyano klavyesinde tekrarlayın. Melodi her turda uzar — tek bir yanlış nota oyunu bitirir.'],
                    ['Interval Blitz','timer','Aralık tanıma','Süre dolmadan aralığı adlandırın. Seviye 1: melodik · Seviye 2: harmonik · Seviye 3: karışık. İlerlemek için seviye başına 20 soru tamamlayın; 3 can vardır, 5 soruluk seri bonus can kazandırır.'],
                    ['Note Catcher','move-horizontal','Porteden tuşa eşleme','Düşen notayı sağa sola yönlendirerek doğru piyano tuşuna indirin — klavye ok tuşları da çalışır.'],
                    ['Chord Clash','layers','Akor niteliği tanıma','Bir akor çalınır — iki akor türü arasından niteliğini belirleyin. 5 seviye sizi temel üçlü akorlardan yedili akorlara taşır.'],
                ] as $g)
                <div class="light-card rounded-2xl p-4">
                    <div class="flex items-center gap-2.5 mb-2">
                        <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0"><i data-lucide="{{ $g[1] }}" class="w-4 h-4"></i></span>
                        <div>
                            <p class="text-sm font-bold text-gray-900 leading-tight">{{ $g[0] }}</p>
                            <p class="text-[11px] text-gray-400">{{ $g[2] }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 leading-relaxed">{{ $g[3] }}</p>
                </div>
                @endforeach
            </div>

            {{-- play limits --}}
            <div class="light-card rounded-2xl p-5 mb-6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Günlük oyun hakları</p>
                <div class="space-y-2 text-sm text-gray-700">
                    <div class="flex flex-wrap items-center gap-2">{!! guideBadge('Misafir','guest') !!}<span>Oyun başına {{ $guestPlaysPerGame }} hak, toplam {{ $guestPlaysTotal }} hak (kayıt olmadan denemek için)</span></div>
                    <div class="flex flex-wrap items-center gap-2">{!! guideBadge('Ücretsiz') !!}<span>Oyun başına günde {{ $freePlan['games_daily_plays_per_type'] }} hak, toplam günde {{ $freePlan['games_daily_plays_total'] }} hak</span></div>
                    <div class="flex flex-wrap items-center gap-2">{!! guideBadge('Premium','premium') !!}<span>Sınırsız oyun + global sıralama erişimi</span></div>
                </div>
            </div>

            {!! guideBtn(route('games.index'), 'Müzik Oyunlarına Göz Atın', 'gamepad-2') !!}

            @include('pages.partials.guide-faq', ['label' => $faqLabel, 'faqs' => [
                ['q'=>'Oyunlar gerçek pratik sayılır mı?','a'=>'Evet — her oyun, standart egzersizlerle aynı ses motorunu ve becerileri kullanır. Gerçekten antrenman yapıyorsunuz; sadece biraz daha fazla adrenalinle.'],
                ['q'=>'Hesap olmadan oynayabilir miyim?','a'=>'Evet — misafirler her oyunu '.$guestPlaysPerGame.' kez (toplam '.$guestPlaysTotal.' hak) deneyebilir. Puanlarınızı kaydetmek ve ücretsiz planın günlük haklarını kullanmak için giriş yapın.'],
                ['q'=>'Skor tablosu var mı?','a'=>'Evet. Kişisel rekorlar her planda profilinize kaydedilir; global sıralama (Şöhret Salonu) ise bir Premium özelliğidir.'],
            ]])
        </article>

        {{-- ═══════════ 9. İLERLEME VE BAŞARIMLAR ═══════════ --}}
        <article id="section-9" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[9] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">9</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">İlerleme, Analizler ve Başarımlar</h2>
                    <p class="text-gray-500 text-sm">Ne kadar yol aldığınızı — ve sırada ne olduğunu — net olarak görün.</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">Verdiğiniz her cevap kaydedilir ve net geri bildirime dönüşür: egzersiz türü başına doğruluk, tamamlanan oturumlar, pratik süresi, seriniz, deneyim puanları ve başarımlar. {!! guideBadge('Ücretsiz') !!} bu temel takibi kapsar; {!! guideBadge('Premium','premium') !!} ise zaman içindeki eğilimleri görebilmeniz için ayrıntılı grafikler ve daha derin beceri kırılımları ekler.</p>

            {{-- visual: progress bars --}}
            <div class="light-card rounded-2xl p-5 mb-6">
                <div class="grid grid-cols-3 gap-3 mb-4">
                    @foreach([['Doğruluk','87%','green'],['Seri','7 gün','orange'],['Oturum','142','purple']] as $s)
                    <div class="text-center rounded-xl bg-gray-50 py-3">
                        <p class="text-lg font-extrabold text-{{ $s[2] }}-600">{{ $s[1] }}</p>
                        <p class="text-xs text-gray-400">{{ $s[0] }}</p>
                    </div>
                    @endforeach
                </div>
                <div class="space-y-2.5">
                    @foreach([['Aralıklar',92],['Akorlar',78],['Gamlar',64],['Ritim',85]] as $b)
                    <div>
                        <div class="flex justify-between text-xs mb-1"><span class="text-gray-600 font-medium">{{ $b[0] }}</span><span class="text-gray-400">{{ $b[1] }}%</span></div>
                        <div class="h-2 rounded-full bg-gray-100 overflow-hidden"><div class="h-full rounded-full bg-purple-500" style="width:{{ $b[1] }}%"></div></div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">Nasıl kullanılır</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Her becerideki doğruluk ve etkinlik durumunuzu görmek için <strong>İlerleme</strong>'yi açın.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>En düşük çubukları tespit edin — gelişmek için en verimli fırsatlar onlardır.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Egzersiz Kurulum Stüdyosu'yla hedefe yönelik bir çalışmaya geçin veya yapay zekâ araçlarının sonuçlarınıza göre bir tane hazırlamasına izin verin.</span></li>
                </ul>
            </div>

            {!! guideBtn(route('progress'), 'İlerlememi Gör', 'trending-up') !!}

            @include('pages.partials.guide-faq', ['label' => $faqLabel, 'faqs' => [
                ['q'=>'Tam olarak neler takip ediliyor?','a'=>'Egzersiz türü başına doğruluk, tamamlanan oturumlar, pratik süresi, seriniz, deneyim puanları, başarımlar ve en sık hangi sesleri kaçırdığınız.'],
                ['q'=>'Premium ne ekliyor?','a'=>'Ayrıntılı grafikler ve daha derin beceri kırılımları — böylece yalnızca bugünün fotoğrafını değil, zaman içindeki eğilimleri de izleyebilirsiniz.'],
                ['q'=>'Verilerim gizli mi?','a'=>'Evet. Pratik verileriniz size aittir ve asla satılmaz. Dilediğiniz an verilerinizin bir kopyasını talep edebilir veya hesabınızın ve kişisel verilerinizin silinmesini isteyebilirsiniz — ayrıntılar için Gizlilik Politikası\'na bakın.'],
            ]])
        </article>

        {{-- ═══════════ 10. ÖĞRETMEN BUL ═══════════ --}}
        <article id="section-10" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[10] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">10</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Öğretmen Bul</h2>
                    <p class="text-gray-500 text-sm">Doğrulanmış öğretmen ve müzik okullarına göz atın, biriyle bağlantı kurun.</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-4">Öğretmen dizini, Harmoniva'daki onaylı tüm öğretmenleri ve müzik okullarını listeler. Her herkese açık profilde özgeçmiş, enstrümanlar, sunulan hizmetler ve ders biçimleri, fotoğraf ve videolar, diller ve gerçek öğrencilerin yorumları yer alır. Profil üzerinden bağlantı isteği gönderebilir, öğretmene mesaj atabilir ve — öğretmen çevrimiçi rezervasyonu açtıysa — doğrudan ders saati ayırtabilirsiniz.</p>

            <p class="text-gray-600 leading-relaxed mb-6"><strong>Profiller yayına alınmadan önce incelenir:</strong> Öğretmen hesabını oluşturur, profilini tamamlar, incelemeye gönderir ve profil Harmoniva ekibinin onayının ardından herkese açık hâle gelir. Yani dizinde gördüğünüz her profil kontrol edilmiştir.</p>

            {{-- visual: teacher card --}}
            <div class="light-card rounded-2xl p-4 mb-6">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 rounded-full bg-gradient-to-br from-purple-400 to-orange-400 flex items-center justify-center text-white font-bold">A</div>
                    <div><p class="font-bold text-gray-900 text-sm leading-tight">Ayla K.</p><p class="text-xs text-gray-400">Piyano ve kulak eğitimi öğretmeni · ★ 4.9</p></div>
                    <span class="ml-auto inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-green-100 text-green-700 text-[11px] font-bold"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Öğrenci kabul ediyor</span>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[11px] font-semibold">Online ders</span>
                    <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[11px] font-semibold">Klasik</span>
                    <span class="px-2 py-0.5 rounded-md bg-purple-50 text-purple-700 text-[11px] font-semibold">Türkçe · İngilizce</span>
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">Nasıl kullanılır</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Dizine göz atın; enstrümanınıza ve hedeflerinize uyan profilleri açın.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Uzmanlık alanlarını, hizmetleri, dilleri ve öğrenci yorumlarını inceleyin.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Bağlantı isteği gönderin, mesaj atın veya müsait bir ders saati ayırtın.</span></li>
                </ul>
            </div>

            {!! guideBtn(route('teachers.directory'), 'Öğretmen ve Okul Bul', 'users') !!}

            @include('pages.partials.guide-faq', ['label' => $faqLabel, 'faqs' => [
                ['q'=>'Bu profiller doğrulanmış mı?','a'=>'Evet — her öğretmen ve okul profili, dizinde herkese açık hâle gelmeden önce Harmoniva ekibi tarafından incelenir.'],
                ['q'=>'Bir öğretmenle bağlantı kurmak ücretli mi?','a'=>'Harmoniva üzerinde bağlantı kurmak ücretsizdir. Ders ücretlerini her öğretmen veya okul kendisi belirler — fiyatları ve hizmetleri profillerinde bulabilirsiniz.'],
                ['q'=>'Her öğretmenden online ders saati ayırtılabilir mi?','a'=>'Çevrimiçi saat rezervasyonu, öğretmenin bu özelliği açtığı profillerde görünür. Diğer durumlarda yine bağlantı kurup mesajlaşarak ders ayarlayabilirsiniz.'],
            ]])
        </article>

        {{-- ═══════════ 11. TOPLULUK AKIŞI ═══════════ --}}
        <article id="section-11" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[11] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">11</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Topluluk Akışı</h2>
                    <p class="text-gray-500 text-sm">Diğer öğrencileri takip edin ve yolculuğu paylaşın. {!! guideBadge('Ücretsiz hesap') !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">Öğrenmek yol arkadaşlarıyla daha kolaydır. Topluluk akışında diğer öğrencileri, öğretmenleri ve okulları takip edebilir; tamamlanan dersler, seriler, başarımlar ve yeni rekorlar gibi kilometre taşlarını — kendinizinkilerle birlikte — görebilirsiniz. Motivasyonu yüksek tutmanın ve birlikte öğrenecek insanlar keşfetmenin zahmetsiz bir yoludur.</p>

            {{-- visual: feed --}}
            <div class="light-card rounded-2xl p-4 mb-6">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Topluluk akışı</p>
                <div class="space-y-2 text-xs text-gray-600">
                    <div class="flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center"><i data-lucide="award" class="w-3 h-3"></i></span> Mateo 30 günlük seriye ulaştı 🎉</div>
                    <div class="flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center"><i data-lucide="check" class="w-3 h-3"></i></span> Sara "Akorlar · Ders 12"yi tamamladı</div>
                    <div class="flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center"><i data-lucide="star" class="w-3 h-3"></i></span> Interval Blitz'te yeni rekor</div>
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">Nasıl kullanılır</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Ana menüden <strong>Akış</strong>'ı açın (giriş gerektirir).</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Takipte kalmak istediğiniz öğrencileri, öğretmenleri ve okulları takip edin.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Kendi kilometre taşlarınız — seriler, tamamlanan dersler, rekorlar — da akışta görünür.</span></li>
                </ul>
            </div>

            {!! guideBtn(route('feed'), 'Akışı Aç', 'rss') !!}

            @include('pages.partials.guide-faq', ['label' => $faqLabel, 'faqs' => [
                ['q'=>'Akışı kimler görebilir?','a'=>'Akış giriş yapmış kullanıcılar içindir. Misafirlerden önce ücretsiz bir hesap oluşturmaları istenir.'],
                ['q'=>'Öğretmenimi takip edebilir miyim?','a'=>'Evet — diğer öğrencilerin yanı sıra öğretmenleri ve okulları da takip edebilirsiniz; herkese açık etkinlikleri akışınıza düşer.'],
                ['q'=>'Akışta neler görünür?','a'=>'Sizden ve takip ettiklerinizden gelen etkinlik ve kilometre taşları: tamamlanan dersler, pratik serileri, başarımlar ve oyun rekorları.'],
            ]])
        </article>

        {{-- ═══════════ 12. ÖDEVLER, MESAJLAR VE DERSLER ═══════════ --}}
        <article id="section-12" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[12] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">12</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Ödevler, Mesajlar ve Dersler</h2>
                    <p class="text-gray-500 text-sm">Sizi öğretmeninize bağlayan her şey, tek bir akışta.</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">Bir öğretmen veya okulla bağlantı kurduktan sonra iş birliğini üç araç ayakta tutar: <strong>Ödevler</strong>, sizin için hazırlanan tüm egzersiz setlerini bir arada toplar ve sonuçlarınız otomatik olarak raporlanır; <strong>Mesajlar</strong>, her öğretmeninizle özel yazışma kutunuzdur — paylaşılan belgeler, makaleler ve videolar dahil; <strong>Randevularım</strong> ise ayırttığınız dersleri listeler — buradan iptal edebilir veya erteleme talep edebilirsiniz. Bildirimler hepsini birbirine bağlar; yeni bir ödevi, bir yanıtı veya yaklaşan bir dersi asla kaçırmazsınız.</p>

            {{-- visual: assignment + appointment --}}
            <div class="grid sm:grid-cols-2 gap-3 mb-6">
                <div class="light-card rounded-2xl p-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Ödevleriniz</p>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2.5 p-2.5 rounded-lg bg-purple-50 border border-purple-100">
                            <span class="w-7 h-7 rounded-lg bg-purple-600 text-white flex items-center justify-center flex-shrink-0"><i data-lucide="clipboard-list" class="w-3.5 h-3.5"></i></span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-800 leading-tight">Fa anahtarında üçlü akorlar · 15 soru</p>
                                <p class="text-[11px] text-gray-400">Ayla K.'dan · son tarih Cuma</p>
                            </div>
                            <span class="ml-auto text-[11px] font-bold text-purple-600 flex-shrink-0">Başlat →</span>
                        </div>
                        <div class="flex items-center gap-2.5 p-2.5 rounded-lg bg-gray-50">
                            <span class="w-7 h-7 rounded-lg bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0"><i data-lucide="check" class="w-3.5 h-3.5"></i></span>
                            <div class="min-w-0">
                                <p class="text-xs font-bold text-gray-800 leading-tight">Melodik dikte · Do Majör</p>
                                <p class="text-[11px] text-gray-400">Tamamlandı · %92</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="light-card rounded-2xl p-4">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-widest mb-3">Sıradaki ders</p>
                    <div class="rounded-xl bg-gray-50 p-3 mb-2.5">
                        <div class="flex items-center gap-2.5">
                            <span class="w-9 h-9 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center flex-shrink-0"><i data-lucide="calendar-check" class="w-4 h-4"></i></span>
                            <div>
                                <p class="text-xs font-bold text-gray-800">Ayla K. ile piyano dersi</p>
                                <p class="text-[11px] text-gray-400">Salı · 16.00 – 16.45 · Çevrimiçi</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0"><i data-lucide="message-circle" class="w-3 h-3"></i></span>
                        "6'lılarda harika ilerleme — Salı günü görüşürüz!"
                    </div>
                </div>
            </div>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">Nasıl kullanılır</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Öğretmeninizin veya okulunuzun gönderdiği tüm egzersiz setlerini görmek için <strong>Ödevler</strong>'i açın — Başlat'a basın, puanınız otomatik olarak raporlanır.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Soru sormak ve paylaşılan materyalleri — belgeler, makaleler, videolar — almak için <strong>Mesajlar</strong>'ı kullanın.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Öğretmeninizin profil takviminden ders ayırtın ve hepsini <strong>Randevularım</strong> altından yönetin.</span></li>
                </ul>
            </div>

            <div class="flex flex-wrap gap-3">
                {!! guideBtn(route('assignments.index'), 'Ödevlerim', 'clipboard-list') !!}
                <a href="{{ route('messages') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">Mesajlar <i data-lucide="mail" class="w-4 h-4"></i></a>
                <a href="{{ route('my-appointments.index') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">Randevularım <i data-lucide="calendar" class="w-4 h-4"></i></a>
            </div>

            <div class="mt-6">
            @include('pages.partials.guide-faq', ['label' => $faqLabel, 'faqs' => [
                ['q'=>'Öğretmenimin gönderdiği egzersizleri nerede bulurum?','a'=>'Ödevler altında. Her ödevde kimin gönderdiği, son tarihi ve tamamlandığında puanınız görünür. Sonuçlar otomatik olarak öğretmeninize iletilir.'],
                ['q'=>'Öğretmenime doğrudan mesaj atabilir miyim?','a'=>'Evet — bağlantılı her öğretmen veya okul için Mesajlar kutunuzda özel bir yazışma dizisi oluşur; paylaştıkları belgeler, makaleler ve videolar da buraya düşer.'],
                ['q'=>'Ders rezervasyonu nasıl çalışır?','a'=>'Rezervasyonu açık olan öğretmenler, müsait saatlerini herkese açık profillerinde gösterir. Bir saat seçip onaylayın; iptaller ve erteleme talepleri dahil her şeyi Randevularım üzerinden yönetin.'],
                ['q'=>'Yeni ödevlerden haberdar edilecek miyim?','a'=>'Evet. Yeni ödevler, mesajlar, randevu güncellemeleri ve bağlantı istekleri bildirim merkezinize düşer; hiçbir gelişme gözünüzden kaçmaz.'],
            ]])
            </div>
        </article>

        {{-- ═══════════ 13. ÖĞRETMENLER İÇİN ═══════════ --}}
        <article id="section-13" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[13] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">13</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Öğretmenler için Harmoniva</h2>
                    <p class="text-gray-500 text-sm">Platforma gömülü eksiksiz bir öğretmenlik araç seti. {!! guideBadge('Öğretmen','teacher') !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-4">Kayıtlı her kullanıcı bir öğretmen hesabı açabilir — yeniden kayıt olmanız gerekmez. Öğretmen Paneli size şunları sunar: davet sistemiyle öğrenci listesi, özel ödev oluşturucu (Egzersiz Kurulum Stüdyosu ile aynı motor), atadığınız her şey için otomatik sonuç takibi, her öğrenciyle özel mesajlaşma, paylaşılan ders materyalleri ve incelemeden geçtikten sonra yayına giren herkese açık bir öğretmen profili.</p>

            <p class="text-gray-600 leading-relaxed mb-6">{!! guideBadge('Öğretmen','teacher') !!} Temel öğretmen hesabı, ödevler ve sonuç takibiyle birlikte en fazla {{ $teacherFree['max_students'] }} öğrenciyi destekler. {!! guideBadge('Öğretmen Premium','premium') !!} ise sınırsız öğrencinin, tam CRM'in, takvim ve ders planlamanın, herkese açık profilinizde çevrimiçi rezervasyonun, içerik yayınlamanın ve ayrıntılı raporların kilidini açar.</p>

            <div class="bg-purple-50/60 border border-purple-100 rounded-xl p-5 mb-6">
                <p class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-3">Nasıl çalışır</p>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">1.</span><span>Hesabınızda öğretmen araçlarını etkinleştirin ve öğrencilerinizi davet edin (veya isteklerini kabul edin).</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">2.</span><span>Ödev oluşturucuyla ödevler hazırlayın — türü, sesleri, anahtarı ve soru sayısını öğrenci bazında seçin.</span></li>
                    <li class="flex gap-2"><span class="text-purple-600 font-bold shrink-0">3.</span><span>Sonuçların otomatik geldiğini izleyin, öğrencilerle mesajlaşın ve onay sonrası öğretmen dizininde yer almak için herkese açık profilinizi tamamlayın.</span></li>
                </ul>
            </div>

            <div class="flex flex-wrap gap-3">
                {!! guideBtn(route('page.teachers-solution'), 'Öğretmenler için Harmoniva', 'briefcase') !!}
                <a href="{{ route('pricing.teachers') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">Öğretmen ve Okul Fiyatları <i data-lucide="tag" class="w-4 h-4"></i></a>
            </div>

            <div class="mt-6">
            @include('pages.partials.guide-faq', ['label' => $faqLabel, 'faqs' => [
                ['q'=>'Harmoniva\'da nasıl öğretmen olurum?','a'=>'Kayıt sırasında Öğretmen Paneli\'ni seçin veya daha sonra mevcut hesabınızdan öğretmen araçlarını etkinleştirin — ayrı bir kayıt gerekmez.'],
                ['q'=>'Temel ve Premium öğretmen planları arasındaki fark nedir?','a'=>'Temel hesap, ödevler ve sonuç takibiyle en fazla '.config('plans.teacher.free.max_students').' öğrenciyi kapsar. Premium; sınırsız öğrenci, tam CRM, takvim ve planlama, çevrimiçi rezervasyon, içerik yayınlama ve ayrıntılı raporlar ekler.'],
                ['q'=>'Öğretmen profilim hemen yayınlanır mı?','a'=>'Hayır — profilinizi tamamlayıp incelemeye gönderirsiniz; Harmoniva ekibinin onayının ardından dizinde herkese açık hâle gelir.'],
            ]])
            </div>
        </article>

        {{-- ═══════════ 14. MÜZİK OKULLARI İÇİN ═══════════ --}}
        <article id="section-14" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[14] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">14</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Müzik Okulları için Harmoniva</h2>
                    <p class="text-gray-500 text-sm">Aynı öğretmenlik motoru, kurum ölçeğinde. {!! guideBadge('Müzik Okulu','school') !!}</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-4">Müzik Okulu Paneli, Öğretmen Paneli ile aynı motor üzerinde çalışır ve kurumlar için genişletilmiştir: öğretmen kadronuzu yönetin, öğrencileri davet edip kaydedin, egzersiz atayın ve herkesin ilerlemesini tek panelden izleyin. Okullar ayrıca dizinde — öğretmen profilleriyle aynı şekilde incelenip onaylanan — herkese açık bir okul profiline sahip olur; öğrenciler bir okula, özel bir öğretmene veya her ikisine birden bağlı olabilir.</p>

            <p class="text-gray-600 leading-relaxed mb-6">{!! guideBadge('Müzik Okulu','school') !!} Temel okul hesabı en fazla {{ $schoolFree['max_teachers'] }} öğretmen ve {{ $schoolFree['max_students'] }} öğrenciyi destekler. {!! guideBadge('Okul Premium','premium') !!} ise sınırsız öğretmen ve öğrencinin, tam CRM'in, takvimin, içerik yayınlamanın ve gelişmiş raporların kilidini açar.</p>

            {{-- visual: school strip --}}
            <div class="light-card rounded-2xl p-4 mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-orange-400 to-purple-500 flex items-center justify-center text-white flex-shrink-0"><i data-lucide="building-2" class="w-5 h-5"></i></div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-gray-900 text-sm leading-tight">Aria Müzik Okulu</p>
                        <p class="text-xs text-gray-400">Öğretmenler · öğrenciler · sınıf ödevleri ve ilerleme raporları tek panelde</p>
                    </div>
                    <span class="hidden sm:inline-block px-3 py-1.5 rounded-lg bg-purple-50 text-purple-700 text-xs font-semibold flex-shrink-0">Okul profili →</span>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                {!! guideBtn(route('page.schools'), 'Okullar için Harmoniva', 'building-2') !!}
                <a href="{{ route('page.request-demo') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">Demo Talep Edin <i data-lucide="calendar" class="w-4 h-4"></i></a>
            </div>

            <div class="mt-6">
            @include('pages.partials.guide-faq', ['label' => $faqLabel, 'faqs' => [
                ['q'=>'Öğretmen hesabıyla okul hesabı arasındaki fark nedir?','a'=>'Öğretmen hesabı bireysel bir eğitmen içindir; okul hesabı ise birden fazla öğretmeni ve onların öğrencilerini tek kurum çatısı altında, okul genelinde gözetimle yönetir.'],
                ['q'=>'Öğrenciler okulumuza nasıl katılır?','a'=>'Davetlerle — okul (veya öğretmenleri) öğrencileri davet eder; öğrenciler kabul edince kayıtları tamamlanır. Ödev sonuçları ve ilerlemeleri okul tarafından görülebilir hâle gelir.'],
                ['q'=>'Okul fiyatlarını nerede görebilirim?','a'=>'Öğretmenler ve Okullar fiyatlandırma sayfası, Premium katmanların neler eklediği dahil hem öğretmen hem okul planlarını kapsar.'],
            ]])
            </div>
        </article>

        {{-- ═══════════ 15. PLANLAR VE ERİŞİM ═══════════ --}}
        <article id="section-15" class="reveal scroll-mt-24">
            <a href="{{ $sectionLinks[15] }}" class="flex items-start sm:items-center gap-3 sm:gap-4 mb-4 group">
                <span class="w-9 h-9 sm:w-11 sm:h-11 rounded-lg sm:rounded-xl bg-purple-600 text-white font-bold text-base sm:text-lg flex items-center justify-center flex-shrink-0">15</span>
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold text-gray-900 group-hover:text-purple-700 transition-colors">Planlar ve Erişim</h2>
                    <p class="text-gray-500 text-sm">Hangi özellik hangi planda — tek bakışta özet.</p>
                </div><i data-lucide="arrow-right" class="hidden sm:block w-5 h-5 text-gray-300 group-hover:text-purple-600 group-hover:translate-x-1 transition-all ml-auto shrink-0"></i>
            </a>

            <p class="text-gray-600 leading-relaxed mb-6">Rehber boyunca küçük erişim rozetleri gördünüz. İşte hepsinin tek yerdeki özeti — bu sayılar doğrudan canlı plan yapılandırmasından gelir:</p>

            <div class="light-card rounded-2xl p-5 mb-6">
                <div class="space-y-3 text-sm text-gray-700">
                    <div class="flex flex-wrap items-start gap-2 pb-3 border-b border-gray-100">
                        {!! guideBadge('Misafir','guest') !!}
                        <span class="flex-1 min-w-[200px]">Pratik egzersizlerini, Piyano Stüdyosu'nu ve oyun başına {{ $guestPlaysPerGame }} hakkı (toplam {{ $guestPlaysTotal }}) deneyin — hiçbir şey kaydedilmez.</span>
                    </div>
                    <div class="flex flex-wrap items-start gap-2 pb-3 border-b border-gray-100">
                        {!! guideBadge('Ücretsiz') !!}
                        <span class="flex-1 min-w-[200px]">10 egzersiz türünün tamamı · Yapılandırılmış Öğrenme Yolu (günde {{ $freePlan['learning_path_daily_sessions'] }} oturum, her biri {{ $freePlan['session_question_cap'] }} soru) · Egzersiz Kurulum Stüdyosu (günde {{ $freePlan['studio_daily_sessions'] }} oturum, {{ $freePlan['saved_plans_limit'] }} kayıtlı şablon) · sınırsız Piyano Stüdyosu · oyunlar (oyun başına {{ $freePlan['games_daily_plays_per_type'] }}, günde {{ $freePlan['games_daily_plays_total'] }}) · Ask AI (günde {{ $freePlan['ask_ai_daily'] }}) · temel ilerleme takibi · topluluk akışı.</span>
                    </div>
                    <div class="flex flex-wrap items-start gap-2 pb-3 border-b border-gray-100">
                        {!! guideBadge('Premium','premium') !!}
                        <span class="flex-1 min-w-[200px]">Ücretsiz plandaki her şey günlük limitler olmadan · sınırsız şablon · AI Egzersizleri · tam AI Koç · ayrıntılı grafikler · sınırsız oyun + global sıralama.</span>
                    </div>
                    <div class="flex flex-wrap items-start gap-2 pb-3 border-b border-gray-100">
                        {!! guideBadge('Öğretmen','teacher') !!}
                        <span class="flex-1 min-w-[200px]">En fazla {{ $teacherFree['max_students'] }} öğrenci, ödevler ve sonuç takibi; Öğretmen Premium sınırsız öğrenci, CRM, takvim, rezervasyon, içerik yayınlama ve raporlar ekler.</span>
                    </div>
                    <div class="flex flex-wrap items-start gap-2">
                        {!! guideBadge('Müzik Okulu','school') !!}
                        <span class="flex-1 min-w-[200px]">En fazla {{ $schoolFree['max_teachers'] }} öğretmen ve {{ $schoolFree['max_students'] }} öğrenci; Okul Premium sınırsız kadro, CRM, takvim ve gelişmiş raporlar ekler.</span>
                    </div>
                </div>
            </div>

            <div class="flex flex-wrap gap-3">
                {!! guideBtn(route('pricing.index'), 'Tüm Fiyatları Görün', 'tag') !!}
                <a href="{{ route('pricing.teachers') }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl font-semibold text-sm w-full sm:w-auto border-2 border-purple-200 text-purple-700 bg-white hover:bg-purple-50 transition-all">Öğretmen ve Okul Fiyatları <i data-lucide="briefcase" class="w-4 h-4"></i></a>
            </div>

            <div class="mt-6">
            @include('pages.partials.guide-faq', ['label' => $faqLabel, 'faqs' => [
                ['q'=>'Premium\'a nasıl yükseltirim?','a'=>'Fiyatlandırma sayfasını açın, bir plan seçin ve ödemeyi tamamlayın — hesabınız anında yükseltilir ve bu rehberdeki tüm limitler buna göre kalkar.'],
                ['q'=>'Öğretmen ve okulların ayrı fiyatlandırması var mı?','a'=>'Evet — Öğretmenler ve Okullar fiyatlandırma sayfası, kişisel Ücretsiz/Premium planlardan ayrı olarak bu planları kapsar.'],
                ['q'=>'Nereden yardım alabilirim?','a'=>'Yardım Merkezi ve SSS sayfaları yaygın soruları yanıtlar; dilediğiniz an İletişim sayfasından ekibe ulaşabilirsiniz.'],
            ]])
            </div>
        </article>

    </div>
</div>

{{-- ============ BOTTOM CTA ============ --}}
<section class="bg-white py-20 px-4">
    <div class="max-w-2xl mx-auto text-center reveal">
        <span class="text-xs font-bold uppercase tracking-[0.2em] text-purple-600 mb-3 block">Başlamaya hazır mısınız?</span>
        <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mb-4">Kulağınızı eğitmeye bugün başlayın</h2>
        <p class="text-gray-500 text-lg mb-8">Ücretsiz bir hesap oluşturun ve bu rehberi uygulamaya dökün. Kredi kartı yok, taahhüt yok.</p>
        <div class="flex flex-wrap justify-center gap-3">
            @auth
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl text-white font-semibold shadow-lg shadow-purple-500/25 hover:-translate-y-0.5 transition-all w-full sm:w-auto" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">Panele Git <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
            @else
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl text-white font-semibold shadow-lg shadow-purple-500/25 hover:-translate-y-0.5 transition-all w-full sm:w-auto" style="background:linear-gradient(135deg,#9333ea,#7c3aed);">Ücretsiz Başla <i data-lucide="arrow-right" class="w-4 h-4"></i></a>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl font-semibold border-2 border-gray-200 text-gray-700 hover:bg-gray-50 transition-all w-full sm:w-auto">Giriş Yap</a>
            @endauth
        </div>
    </div>
</section>

@endsection
