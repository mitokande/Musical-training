<?php

namespace Database\Seeders;

use App\Models\QuestionnaireQuestion;
use Illuminate\Database\Seeder;

class QuestionnaireSeeder extends Seeder
{
    public function run(): void
    {
        $questions = [
            // ── Background ──────────────────────────────────────────────────────
            [
                'key'           => 'music_history',
                'question_text' => 'Muzikle ne zamandir ilgileniyorsunuz?',
                'question_type' => 'single_choice',
                'options'       => ['1 yildan az', '1-3 yil', '3-5 yil', '5-10 yil', '10 yildan fazla'],
                'category'      => 'background',
                'sort_order'    => 1,
                'translations'  => [
                    'tr' => [
                        'question_text' => 'Müzikle ne zamandır ilgileniyorsunuz?',
                        'options' => ['1 yıldan az', '1-3 yıl', '3-5 yıl', '5-10 yıl', '10 yıldan fazla'],
                    ],
                    'en' => [
                        'question_text' => 'How long have you been interested in music?',
                        'options' => ['Less than 1 year', '1-3 years', '3-5 years', '5-10 years', 'More than 10 years'],
                    ],
                    'de' => [
                        'question_text' => 'Wie lange interessieren Sie sich schon für Musik?',
                        'options' => ['Weniger als 1 Jahr', '1-3 Jahre', '3-5 Jahre', '5-10 Jahre', 'Mehr als 10 Jahre'],
                    ],
                    'fr' => [
                        'question_text' => 'Depuis combien de temps vous intéressez-vous à la musique ?',
                        'options' => ['Moins d\'1 an', '1-3 ans', '3-5 ans', '5-10 ans', 'Plus de 10 ans'],
                    ],
                    'es' => [
                        'question_text' => '¿Cuánto tiempo llevas interesado en la música?',
                        'options' => ['Menos de 1 año', '1-3 años', '3-5 años', '5-10 años', 'Más de 10 años'],
                    ],
                    'it' => [
                        'question_text' => 'Da quanto tempo ti interessi alla musica?',
                        'options' => ['Meno di 1 anno', '1-3 anni', '3-5 anni', '5-10 anni', 'Più di 10 anni'],
                    ],
                    'ru' => [
                        'question_text' => 'Как долго вы интересуетесь музыкой?',
                        'options' => ['Менее 1 года', '1-3 года', '3-5 лет', '5-10 лет', 'Более 10 лет'],
                    ],
                    'ar' => [
                        'question_text' => 'منذ متى وأنت مهتم بالموسيقى؟',
                        'options' => ['أقل من سنة', '1-3 سنوات', '3-5 سنوات', '5-10 سنوات', 'أكثر من 10 سنوات'],
                    ],
                    'zh' => [
                        'question_text' => '您对音乐感兴趣多久了？',
                        'options' => ['不到1年', '1-3年', '3-5年', '5-10年', '超过10年'],
                    ],
                    'ja' => [
                        'question_text' => '音楽に興味を持ってどのくらいになりますか？',
                        'options' => ['1年未満', '1〜3年', '3〜5年', '5〜10年', '10年以上'],
                    ],
                    'ko' => [
                        'question_text' => '음악에 관심을 가진 지 얼마나 됐나요?',
                        'options' => ['1년 미만', '1-3년', '3-5년', '5-10년', '10년 이상'],
                    ],
                    'pt' => [
                        'question_text' => 'Há quanto tempo você se interessa por música?',
                        'options' => ['Menos de 1 ano', '1-3 anos', '3-5 anos', '5-10 anos', 'Mais de 10 anos'],
                    ],
                    'nl' => [
                        'question_text' => 'Hoe lang bent u al geïnteresseerd in muziek?',
                        'options' => ['Minder dan 1 jaar', '1-3 jaar', '3-5 jaar', '5-10 jaar', 'Meer dan 10 jaar'],
                    ],
                    'pl' => [
                        'question_text' => 'Od jak dawna interesujesz się muzyką?',
                        'options' => ['Mniej niż rok', '1-3 lata', '3-5 lat', '5-10 lat', 'Ponad 10 lat'],
                    ],
                    'sv' => [
                        'question_text' => 'Hur länge har du intresserat dig för musik?',
                        'options' => ['Mindre än 1 år', '1-3 år', '3-5 år', '5-10 år', 'Mer än 10 år'],
                    ],
                ],
            ],
            [
                'key'           => 'instrument_experience',
                'question_text' => 'Enstruman calmadaki deneyim seviyeniz nedir?',
                'question_type' => 'single_choice',
                'options'       => ['Hic calismadim', 'Yeni basliyorum', 'Temel seviye', 'Orta seviye', 'Ileri seviye'],
                'category'      => 'background',
                'sort_order'    => 2,
                'translations'  => [
                    'tr' => [
                        'question_text' => 'Enstrüman çalmadaki deneyim seviyeniz nedir?',
                        'options' => ['Hiç çalışmadım', 'Yeni başlıyorum', 'Temel seviye', 'Orta seviye', 'İleri seviye'],
                    ],
                    'en' => [
                        'question_text' => 'What is your experience level in playing an instrument?',
                        'options' => ['Never practiced', 'Just starting out', 'Beginner level', 'Intermediate level', 'Advanced level'],
                    ],
                    'de' => [
                        'question_text' => 'Wie ist Ihr Erfahrungsniveau beim Spielen eines Instruments?',
                        'options' => ['Noch nie geübt', 'Gerade erst angefangen', 'Anfänger', 'Mittelstufe', 'Fortgeschritten'],
                    ],
                    'fr' => [
                        'question_text' => 'Quel est votre niveau d\'expérience dans la pratique d\'un instrument ?',
                        'options' => ['Jamais pratiqué', 'Je commence tout juste', 'Niveau débutant', 'Niveau intermédiaire', 'Niveau avancé'],
                    ],
                    'es' => [
                        'question_text' => '¿Cuál es tu nivel de experiencia tocando un instrumento?',
                        'options' => ['Nunca he practicado', 'Recién empezando', 'Nivel básico', 'Nivel intermedio', 'Nivel avanzado'],
                    ],
                    'it' => [
                        'question_text' => 'Qual è il tuo livello di esperienza nel suonare uno strumento?',
                        'options' => ['Mai praticato', 'Sto appena iniziando', 'Livello base', 'Livello intermedio', 'Livello avanzato'],
                    ],
                    'ru' => [
                        'question_text' => 'Каков ваш уровень опыта игры на инструменте?',
                        'options' => ['Никогда не занимался', 'Только начинаю', 'Начальный уровень', 'Средний уровень', 'Продвинутый уровень'],
                    ],
                    'ar' => [
                        'question_text' => 'ما هو مستوى خبرتك في العزف على آلة موسيقية؟',
                        'options' => ['لم أتدرب قط', 'بدأت للتو', 'مستوى مبتدئ', 'مستوى متوسط', 'مستوى متقدم'],
                    ],
                    'zh' => [
                        'question_text' => '您演奏乐器的经验水平如何？',
                        'options' => ['从未练习过', '刚刚开始', '初级水平', '中级水平', '高级水平'],
                    ],
                    'ja' => [
                        'question_text' => '楽器演奏の経験レベルはどのくらいですか？',
                        'options' => ['練習したことがない', '始めたばかり', '初級レベル', '中級レベル', '上級レベル'],
                    ],
                    'ko' => [
                        'question_text' => '악기 연주 경험 수준은 어느 정도인가요?',
                        'options' => ['연습한 적 없음', '이제 막 시작', '초급 수준', '중급 수준', '고급 수준'],
                    ],
                    'pt' => [
                        'question_text' => 'Qual é o seu nível de experiência em tocar um instrumento?',
                        'options' => ['Nunca pratiquei', 'Estou começando agora', 'Nível básico', 'Nível intermediário', 'Nível avançado'],
                    ],
                    'nl' => [
                        'question_text' => 'Wat is uw ervaringsniveau in het bespelen van een instrument?',
                        'options' => ['Nooit geoefend', 'Net begonnen', 'Beginniveau', 'Gemiddeld niveau', 'Gevorderd niveau'],
                    ],
                    'pl' => [
                        'question_text' => 'Jaki jest Twój poziom doświadczenia w grze na instrumencie?',
                        'options' => ['Nigdy nie ćwiczyłem', 'Dopiero zaczynam', 'Poziom podstawowy', 'Poziom średni', 'Poziom zaawansowany'],
                    ],
                    'sv' => [
                        'question_text' => 'Vilken erfarenhetsnivå har du när det gäller att spela instrument?',
                        'options' => ['Har aldrig övat', 'Precis börjat', 'Nybörjarnivå', 'Mellannivå', 'Avancerad nivå'],
                    ],
                ],
            ],
            [
                'key'           => 'formal_education',
                'question_text' => 'Daha once muzik egitimi aldiniz mi?',
                'question_type' => 'single_choice',
                'options'       => ['Hayir, tamamen kendi kendime', 'Bireysel dersler', 'Muzik okulu / kursu', 'Konservatuvar / universite', 'Profesyonel egitim'],
                'category'      => 'background',
                'sort_order'    => 3,
                'translations'  => [
                    'tr' => [
                        'question_text' => 'Daha önce müzik eğitimi aldınız mı?',
                        'options' => ['Hayır, tamamen kendi kendime', 'Bireysel dersler', 'Müzik okulu / kursu', 'Konservatuvar / üniversite', 'Profesyonel eğitim'],
                    ],
                    'en' => [
                        'question_text' => 'Have you received any formal music education?',
                        'options' => ['No, completely self-taught', 'Private lessons', 'Music school / course', 'Conservatory / university', 'Professional training'],
                    ],
                    'de' => [
                        'question_text' => 'Haben Sie eine formelle Musikausbildung erhalten?',
                        'options' => ['Nein, vollständig autodidaktisch', 'Einzelunterricht', 'Musikschule / Kurs', 'Konservatorium / Universität', 'Professionelle Ausbildung'],
                    ],
                    'fr' => [
                        'question_text' => 'Avez-vous reçu une formation musicale formelle ?',
                        'options' => ['Non, entièrement autodidacte', 'Cours particuliers', 'École de musique / cours', 'Conservatoire / université', 'Formation professionnelle'],
                    ],
                    'es' => [
                        'question_text' => '¿Has recibido educación musical formal?',
                        'options' => ['No, completamente autodidacta', 'Clases privadas', 'Escuela de música / curso', 'Conservatorio / universidad', 'Formación profesional'],
                    ],
                    'it' => [
                        'question_text' => 'Hai ricevuto un\'educazione musicale formale?',
                        'options' => ['No, completamente autodidatta', 'Lezioni private', 'Scuola di musica / corso', 'Conservatorio / università', 'Formazione professionale'],
                    ],
                    'ru' => [
                        'question_text' => 'Получали ли вы формальное музыкальное образование?',
                        'options' => ['Нет, полностью самоучка', 'Частные уроки', 'Музыкальная школа / курс', 'Консерватория / университет', 'Профессиональное обучение'],
                    ],
                    'ar' => [
                        'question_text' => 'هل تلقيت تعليماً موسيقياً رسمياً؟',
                        'options' => ['لا، تعلمت بنفسي تمامًا', 'دروس خاصة', 'مدرسة موسيقى / دورة', 'معهد / جامعة', 'تدريب احترافي'],
                    ],
                    'zh' => [
                        'question_text' => '您是否接受过正规音乐教育？',
                        'options' => ['没有，完全自学', '私人课程', '音乐学校/课程', '音乐学院/大学', '专业培训'],
                    ],
                    'ja' => [
                        'question_text' => '正式な音楽教育を受けたことがありますか？',
                        'options' => ['いいえ、完全に独学', '個人レッスン', '音楽学校・コース', '音楽院・大学', 'プロフェッショナル研修'],
                    ],
                    'ko' => [
                        'question_text' => '정식 음악 교육을 받은 적이 있나요?',
                        'options' => ['아니요, 완전 독학', '개인 레슨', '음악 학교/과정', '음악원/대학교', '전문 교육'],
                    ],
                    'pt' => [
                        'question_text' => 'Você recebeu alguma educação musical formal?',
                        'options' => ['Não, completamente autodidata', 'Aulas particulares', 'Escola de música / curso', 'Conservatório / universidade', 'Treinamento profissional'],
                    ],
                    'nl' => [
                        'question_text' => 'Heeft u een formele muziekopleiding gehad?',
                        'options' => ['Nee, volledig autodidact', 'Privéles', 'Muziekschool / cursus', 'Conservatorium / universiteit', 'Professionele opleiding'],
                    ],
                    'pl' => [
                        'question_text' => 'Czy otrzymałeś formalne wykształcenie muzyczne?',
                        'options' => ['Nie, całkowicie samouk', 'Prywatne lekcje', 'Szkoła muzyczna / kurs', 'Konserwatorium / uczelnia', 'Szkolenie zawodowe'],
                    ],
                    'sv' => [
                        'question_text' => 'Har du fått formell musikutbildning?',
                        'options' => ['Nej, helt självlärd', 'Privatlektioner', 'Musikskola / kurs', 'Konservatorium / universitet', 'Professionell utbildning'],
                    ],
                ],
            ],

            // ── Skills ──────────────────────────────────────────────────────────
            [
                'key'           => 'ear_training_level',
                'question_text' => 'Kulak egitimi seviyenizi nasil degerlendirirsiniz?',
                'question_type' => 'scale',
                'options'       => null,
                'category'      => 'skills',
                'sort_order'    => 10,
                'translations'  => [
                    'tr' => ['question_text' => 'Kulak eğitimi seviyenizi nasıl değerlendirirsiniz?'],
                    'en' => ['question_text' => 'How would you rate your ear training level?'],
                    'de' => ['question_text' => 'Wie würden Sie Ihr Gehörbildungsniveau einschätzen?'],
                    'fr' => ['question_text' => 'Comment évalueriez-vous votre niveau en formation de l\'oreille ?'],
                    'es' => ['question_text' => '¿Cómo calificarías tu nivel de entrenamiento auditivo?'],
                    'it' => ['question_text' => 'Come valuteresti il tuo livello di ear training?'],
                    'ru' => ['question_text' => 'Как бы вы оценили свой уровень слухового тренинга?'],
                    'ar' => ['question_text' => 'كيف تقيّم مستوى تدريبك السمعي؟'],
                    'zh' => ['question_text' => '您如何评价自己的听音训练水平？'],
                    'ja' => ['question_text' => '耳のトレーニングレベルをどのように評価しますか？'],
                    'ko' => ['question_text' => '청음 훈련 수준을 어떻게 평가하시나요?'],
                    'pt' => ['question_text' => 'Como avaliaria o seu nível de treinamento auditivo?'],
                    'nl' => ['question_text' => 'Hoe zou u uw niveau van gehoortraining beoordelen?'],
                    'pl' => ['question_text' => 'Jak oceniasz swój poziom treningu słuchu?'],
                    'sv' => ['question_text' => 'Hur skulle du bedöma din nivå av gehörsträning?'],
                ],
            ],
            [
                'key'           => 'rhythm_perception',
                'question_text' => 'Ritim alginiz ne seviyede?',
                'question_type' => 'scale',
                'options'       => null,
                'category'      => 'skills',
                'sort_order'    => 11,
                'translations'  => [
                    'tr' => ['question_text' => 'Ritim algınız ne seviyede?'],
                    'en' => ['question_text' => 'What is your level of rhythm perception?'],
                    'de' => ['question_text' => 'Wie ist Ihr Rhythmusgefühl?'],
                    'fr' => ['question_text' => 'Quel est votre niveau de perception rythmique ?'],
                    'es' => ['question_text' => '¿Cuál es tu nivel de percepción rítmica?'],
                    'it' => ['question_text' => 'Qual è il tuo livello di percezione ritmica?'],
                    'ru' => ['question_text' => 'Каков ваш уровень восприятия ритма?'],
                    'ar' => ['question_text' => 'ما هو مستوى إدراكك للإيقاع؟'],
                    'zh' => ['question_text' => '您的节奏感知水平如何？'],
                    'ja' => ['question_text' => 'リズム知覚のレベルはどのくらいですか？'],
                    'ko' => ['question_text' => '리듬 감각 수준은 어느 정도인가요?'],
                    'pt' => ['question_text' => 'Qual é o seu nível de percepção rítmica?'],
                    'nl' => ['question_text' => 'Wat is uw niveau van ritmisch gehoor?'],
                    'pl' => ['question_text' => 'Jaki jest Twój poziom percepcji rytmu?'],
                    'sv' => ['question_text' => 'Vilken nivå har du när det gäller rytmuppfattning?'],
                ],
            ],
            [
                'key'           => 'sight_reading',
                'question_text' => 'Nota okuma seviyeniz nedir?',
                'question_type' => 'single_choice',
                'options'       => ['Nota okuyamiyorum', 'Basit melodi okuyabiliyorum', 'Orta zorlukta parcalar', 'Akici nota okuyabiliyorum'],
                'category'      => 'skills',
                'sort_order'    => 12,
                'translations'  => [
                    'tr' => [
                        'question_text' => 'Nota okuma seviyeniz nedir?',
                        'options' => ['Nota okuyamıyorum', 'Basit melodi okuyabiliyorum', 'Orta zorlukta parçalar', 'Akıcı nota okuyabiliyorum'],
                    ],
                    'en' => [
                        'question_text' => 'What is your sight-reading level?',
                        'options' => ['Cannot read music', 'Can read simple melodies', 'Intermediate pieces', 'Fluent sight-reader'],
                    ],
                    'de' => [
                        'question_text' => 'Wie ist Ihr Niveau beim Vom-Blatt-Lesen?',
                        'options' => ['Kann keine Noten lesen', 'Kann einfache Melodien lesen', 'Mittelschwere Stücke', 'Flüssiges Vom-Blatt-Lesen'],
                    ],
                    'fr' => [
                        'question_text' => 'Quel est votre niveau en lecture à vue ?',
                        'options' => ['Ne peut pas lire la musique', 'Peut lire des mélodies simples', 'Pièces de difficulté moyenne', 'Lecture à vue fluide'],
                    ],
                    'es' => [
                        'question_text' => '¿Cuál es tu nivel de lectura a primera vista?',
                        'options' => ['No puedo leer música', 'Puedo leer melodías simples', 'Piezas de dificultad media', 'Lectura a vista fluida'],
                    ],
                    'it' => [
                        'question_text' => 'Qual è il tuo livello di lettura a prima vista?',
                        'options' => ['Non riesco a leggere la musica', 'Riesco a leggere melodie semplici', 'Brani di media difficoltà', 'Lettura a prima vista fluente'],
                    ],
                    'ru' => [
                        'question_text' => 'Каков ваш уровень чтения нот с листа?',
                        'options' => ['Не умею читать ноты', 'Могу читать простые мелодии', 'Произведения средней сложности', 'Свободно читаю с листа'],
                    ],
                    'ar' => [
                        'question_text' => 'ما هو مستوى قراءتك للنوتة الموسيقية؟',
                        'options' => ['لا أستطيع قراءة النوتة', 'أستطيع قراءة ألحان بسيطة', 'مقطوعات متوسطة الصعوبة', 'قراءة نوتة طليقة'],
                    ],
                    'zh' => [
                        'question_text' => '您的视奏水平如何？',
                        'options' => ['不会读谱', '能读简单旋律', '中等难度曲目', '视奏流畅'],
                    ],
                    'ja' => [
                        'question_text' => '初見演奏のレベルはどのくらいですか？',
                        'options' => ['楽譜が読めない', '簡単なメロディーが読める', '中程度の難易度の曲', '流暢に初見演奏できる'],
                    ],
                    'ko' => [
                        'question_text' => '악보 초견 수준은 어떻게 되나요?',
                        'options' => ['악보를 읽지 못함', '간단한 멜로디 읽기 가능', '중간 난이도 곡', '유창한 초견 연주'],
                    ],
                    'pt' => [
                        'question_text' => 'Qual é o seu nível de leitura à primeira vista?',
                        'options' => ['Não consigo ler música', 'Consigo ler melodias simples', 'Peças de dificuldade média', 'Leitura à primeira vista fluente'],
                    ],
                    'nl' => [
                        'question_text' => 'Wat is uw niveau van bladzingen (prima vista)?',
                        'options' => ['Kan geen muziek lezen', 'Kan eenvoudige melodieën lezen', 'Stukken van gemiddelde moeilijkheid', 'Vloeiend van blad spelen'],
                    ],
                    'pl' => [
                        'question_text' => 'Jaki jest Twój poziom czytania nut a vista?',
                        'options' => ['Nie umiem czytać nut', 'Czytam proste melodie', 'Utwory średniego poziomu', 'Płynne czytanie a vista'],
                    ],
                    'sv' => [
                        'question_text' => 'Vilken nivå har du på prima vista-spel?',
                        'options' => ['Kan inte läsa noter', 'Kan läsa enkla melodier', 'Stycken av medelsvårighetsgrad', 'Flytande prima vista'],
                    ],
                ],
            ],
            [
                'key'           => 'interval_recognition',
                'question_text' => 'Araliklaryi duyarak taniyabilme beceriniz?',
                'question_type' => 'scale',
                'options'       => null,
                'category'      => 'skills',
                'sort_order'    => 13,
                'translations'  => [
                    'tr' => ['question_text' => 'Aralıkları duyarak tanıyabilme beceriniz?'],
                    'en' => ['question_text' => 'How well can you recognize intervals by ear?'],
                    'de' => ['question_text' => 'Wie gut können Sie Intervalle nach Gehör erkennen?'],
                    'fr' => ['question_text' => 'À quel point pouvez-vous reconnaître les intervalles à l\'oreille ?'],
                    'es' => ['question_text' => '¿Qué tan bien puedes reconocer intervalos de oído?'],
                    'it' => ['question_text' => 'Quanto bene riesci a riconoscere gli intervalli ad orecchio?'],
                    'ru' => ['question_text' => 'Насколько хорошо вы можете определять интервалы на слух?'],
                    'ar' => ['question_text' => 'ما مدى قدرتك على التعرف على الفترات الموسيقية بالسمع؟'],
                    'zh' => ['question_text' => '您靠听觉识别音程的能力如何？'],
                    'ja' => ['question_text' => '音程を耳で認識する能力はどのくらいですか？'],
                    'ko' => ['question_text' => '음정을 귀로 인식하는 능력은 어느 정도인가요?'],
                    'pt' => ['question_text' => 'Quão bem você consegue reconhecer intervalos de ouvido?'],
                    'nl' => ['question_text' => 'Hoe goed kunt u intervallen op gehoor herkennen?'],
                    'pl' => ['question_text' => 'Jak dobrze rozpoznajesz interwały ze słuchu?'],
                    'sv' => ['question_text' => 'Hur bra kan du känna igen intervall på gehör?'],
                ],
            ],
            [
                'key'           => 'chord_recognition',
                'question_text' => 'Akorlari duyarak tanimlayabilme beceriniz?',
                'question_type' => 'scale',
                'options'       => null,
                'category'      => 'skills',
                'sort_order'    => 14,
                'translations'  => [
                    'tr' => ['question_text' => 'Akorları duyarak tanımlayabilme beceriniz?'],
                    'en' => ['question_text' => 'How well can you identify chords by ear?'],
                    'de' => ['question_text' => 'Wie gut können Sie Akkorde nach Gehör identifizieren?'],
                    'fr' => ['question_text' => 'À quel point pouvez-vous identifier les accords à l\'oreille ?'],
                    'es' => ['question_text' => '¿Qué tan bien puedes identificar acordes de oído?'],
                    'it' => ['question_text' => 'Quanto bene riesci a identificare gli accordi ad orecchio?'],
                    'ru' => ['question_text' => 'Насколько хорошо вы можете определять аккорды на слух?'],
                    'ar' => ['question_text' => 'ما مدى قدرتك على التعرف على الأوتار الموسيقية بالسمع؟'],
                    'zh' => ['question_text' => '您靠听觉识别和弦的能力如何？'],
                    'ja' => ['question_text' => 'コードを耳で識別する能力はどのくらいですか？'],
                    'ko' => ['question_text' => '화음을 귀로 식별하는 능력은 어느 정도인가요?'],
                    'pt' => ['question_text' => 'Quão bem você consegue identificar acordes de ouvido?'],
                    'nl' => ['question_text' => 'Hoe goed kunt u akkoorden op gehoor identificeren?'],
                    'pl' => ['question_text' => 'Jak dobrze rozpoznajesz akordy ze słuchu?'],
                    'sv' => ['question_text' => 'Hur bra kan du identifiera ackord på gehör?'],
                ],
            ],
            [
                'key'           => 'difficult_topics',
                'question_text' => 'Hangi konularda zorlaniyorsunuz?',
                'question_type' => 'multi_choice',
                'options'       => ['Araliklar', 'Akorlar', 'Ritim', 'Nota okuma', 'Melodi dikte', 'Tonal analiz', 'Transpozisyon'],
                'category'      => 'skills',
                'sort_order'    => 15,
                'translations'  => [
                    'tr' => [
                        'question_text' => 'Hangi konularda zorlanıyorsunuz?',
                        'options' => ['Aralıklar', 'Akorlar', 'Ritim', 'Nota okuma', 'Melodi dikte', 'Tonal analiz', 'Transpozisyon'],
                    ],
                    'en' => [
                        'question_text' => 'Which topics do you find most challenging?',
                        'options' => ['Intervals', 'Chords', 'Rhythm', 'Sight reading', 'Melodic dictation', 'Tonal analysis', 'Transposition'],
                    ],
                    'de' => [
                        'question_text' => 'Bei welchen Themen haben Sie am meisten Schwierigkeiten?',
                        'options' => ['Intervalle', 'Akkorde', 'Rhythmus', 'Vom-Blatt-Lesen', 'Melodisches Diktat', 'Tonale Analyse', 'Transposition'],
                    ],
                    'fr' => [
                        'question_text' => 'Quels sujets vous posent le plus de difficultés ?',
                        'options' => ['Intervalles', 'Accords', 'Rythme', 'Lecture à vue', 'Dictée mélodique', 'Analyse tonale', 'Transposition'],
                    ],
                    'es' => [
                        'question_text' => '¿En qué temas tienes más dificultades?',
                        'options' => ['Intervalos', 'Acordes', 'Ritmo', 'Lectura a vista', 'Dictado melódico', 'Análisis tonal', 'Transposición'],
                    ],
                    'it' => [
                        'question_text' => 'In quali argomenti hai più difficoltà?',
                        'options' => ['Intervalli', 'Accordi', 'Ritmo', 'Lettura a prima vista', 'Dettato melodico', 'Analisi tonale', 'Trasposizione'],
                    ],
                    'ru' => [
                        'question_text' => 'В каких темах у вас наибольшие трудности?',
                        'options' => ['Интервалы', 'Аккорды', 'Ритм', 'Чтение с листа', 'Мелодический диктант', 'Тональный анализ', 'Транспозиция'],
                    ],
                    'ar' => [
                        'question_text' => 'ما المواضيع التي تجدها أكثر صعوبة؟',
                        'options' => ['الفترات الموسيقية', 'الأوتار', 'الإيقاع', 'القراءة بالنظرة الأولى', 'الإملاء اللحني', 'التحليل التوني', 'النقل الموسيقي'],
                    ],
                    'zh' => [
                        'question_text' => '您觉得哪些主题最具挑战性？',
                        'options' => ['音程', '和弦', '节奏', '视奏', '旋律听写', '调性分析', '移调'],
                    ],
                    'ja' => [
                        'question_text' => 'どのトピックが最も難しいと感じますか？',
                        'options' => ['音程', 'コード', 'リズム', '初見演奏', 'メロディーディクテーション', '調性分析', '移調'],
                    ],
                    'ko' => [
                        'question_text' => '어떤 주제가 가장 어렵다고 생각하나요?',
                        'options' => ['음정', '화음', '리듬', '초견 연주', '멜로디 받아쓰기', '조성 분석', '전조'],
                    ],
                    'pt' => [
                        'question_text' => 'Quais tópicos você acha mais desafiadores?',
                        'options' => ['Intervalos', 'Acordes', 'Ritmo', 'Leitura à vista', 'Ditado melódico', 'Análise tonal', 'Transposição'],
                    ],
                    'nl' => [
                        'question_text' => 'Welke onderwerpen vindt u het meest uitdagend?',
                        'options' => ['Intervallen', 'Akkoorden', 'Ritme', 'Bladzingen', 'Melodisch dictee', 'Tonale analyse', 'Transpositie'],
                    ],
                    'pl' => [
                        'question_text' => 'Które tematy sprawiają Ci największe trudności?',
                        'options' => ['Interwały', 'Akordy', 'Rytm', 'Czytanie a vista', 'Dyktando melodyczne', 'Analiza tonalna', 'Transpozycja'],
                    ],
                    'sv' => [
                        'question_text' => 'Vilka ämnen tycker du är mest utmanande?',
                        'options' => ['Intervaller', 'Ackord', 'Rytm', 'Prima vista', 'Melodidiktat', 'Tonal analys', 'Transponering'],
                    ],
                ],
            ],

            // ── Goals ────────────────────────────────────────────────────────────
            [
                'key'           => 'learning_objectives',
                'question_text' => 'Temel ogrenme hedefleriniz nelerdir?',
                'question_type' => 'multi_choice',
                'options'       => ['Kulak egitimi gelistirme', 'Nota okuma', 'Muzik teorisi', 'Sinav hazirligi', 'Enstruman performansi', 'Kompozisyon / duzenleme', 'Hobi olarak muzik'],
                'category'      => 'goals',
                'sort_order'    => 20,
                'translations'  => [
                    'tr' => [
                        'question_text' => 'Temel öğrenme hedefleriniz nelerdir?',
                        'options' => ['Kulak eğitimi geliştirme', 'Nota okuma', 'Müzik teorisi', 'Sınav hazırlığı', 'Enstrüman performansı', 'Kompozisyon / düzenleme', 'Hobi olarak müzik'],
                    ],
                    'en' => [
                        'question_text' => 'What are your main learning objectives?',
                        'options' => ['Improve ear training', 'Sight reading', 'Music theory', 'Exam preparation', 'Instrument performance', 'Composition / arrangement', 'Music as a hobby'],
                    ],
                    'de' => [
                        'question_text' => 'Was sind Ihre Hauptlernziele?',
                        'options' => ['Gehörbildung verbessern', 'Vom-Blatt-Lesen', 'Musiktheorie', 'Prüfungsvorbereitung', 'Instrumentalperformance', 'Komposition / Arrangement', 'Musik als Hobby'],
                    ],
                    'fr' => [
                        'question_text' => 'Quels sont vos principaux objectifs d\'apprentissage ?',
                        'options' => ['Améliorer la formation de l\'oreille', 'Lecture à vue', 'Théorie musicale', 'Préparation aux examens', 'Performance instrumentale', 'Composition / arrangement', 'Musique comme loisir'],
                    ],
                    'es' => [
                        'question_text' => '¿Cuáles son tus principales objetivos de aprendizaje?',
                        'options' => ['Mejorar el entrenamiento auditivo', 'Lectura a vista', 'Teoría musical', 'Preparación para exámenes', 'Rendimiento instrumental', 'Composición / arreglo', 'Música como pasatiempo'],
                    ],
                    'it' => [
                        'question_text' => 'Quali sono i tuoi principali obiettivi di apprendimento?',
                        'options' => ['Migliorare il training auditivo', 'Lettura a prima vista', 'Teoria musicale', 'Preparazione agli esami', 'Performance strumentale', 'Composizione / arrangiamento', 'Musica come hobby'],
                    ],
                    'ru' => [
                        'question_text' => 'Каковы ваши основные цели обучения?',
                        'options' => ['Улучшить слуховой тренинг', 'Чтение с листа', 'Теория музыки', 'Подготовка к экзаменам', 'Инструментальное исполнительство', 'Композиция / аранжировка', 'Музыка как хобби'],
                    ],
                    'ar' => [
                        'question_text' => 'ما هي أهدافك التعليمية الرئيسية؟',
                        'options' => ['تحسين التدريب السمعي', 'القراءة بالنظرة الأولى', 'نظرية الموسيقى', 'التحضير للامتحانات', 'الأداء الآلي', 'التأليف / الترتيب', 'الموسيقى كهواية'],
                    ],
                    'zh' => [
                        'question_text' => '您的主要学习目标是什么？',
                        'options' => ['提高听音训练', '视奏', '音乐理论', '备考', '乐器演奏', '作曲/编曲', '把音乐作为爱好'],
                    ],
                    'ja' => [
                        'question_text' => '主な学習目標は何ですか？',
                        'options' => ['耳のトレーニングを改善する', '初見演奏', '音楽理論', '試験対策', '楽器演奏', '作曲・編曲', '趣味としての音楽'],
                    ],
                    'ko' => [
                        'question_text' => '주요 학습 목표는 무엇인가요?',
                        'options' => ['청음 훈련 향상', '초견 연주', '음악 이론', '시험 준비', '악기 연주', '작곡/편곡', '취미로서의 음악'],
                    ],
                    'pt' => [
                        'question_text' => 'Quais são os seus principais objetivos de aprendizado?',
                        'options' => ['Melhorar o treinamento auditivo', 'Leitura à primeira vista', 'Teoria musical', 'Preparação para exames', 'Performance instrumental', 'Composição / arranjo', 'Música como hobby'],
                    ],
                    'nl' => [
                        'question_text' => 'Wat zijn uw belangrijkste leerdoelen?',
                        'options' => ['Gehoortraining verbeteren', 'Bladzingen', 'Muziektheorie', 'Examenvoorbereiding', 'Instrumentale uitvoering', 'Compositie / arrangement', 'Muziek als hobby'],
                    ],
                    'pl' => [
                        'question_text' => 'Jakie są Twoje główne cele nauki?',
                        'options' => ['Poprawa treningu słuchu', 'Czytanie a vista', 'Teoria muzyki', 'Przygotowanie do egzaminów', 'Gra na instrumencie', 'Kompozycja / aranżacja', 'Muzyka jako hobby'],
                    ],
                    'sv' => [
                        'question_text' => 'Vilka är dina huvudsakliga lärandemål?',
                        'options' => ['Förbättra gehörsträning', 'Prima vista', 'Musikteori', 'Tentamensförberedelse', 'Instrumentalframträdande', 'Komposition / arrangemang', 'Musik som hobby'],
                    ],
                ],
            ],
            [
                'key'           => 'exam_preparation',
                'question_text' => 'Sinav hazirliginiz var mi?',
                'question_type' => 'single_choice',
                'options'       => ['Hayir', 'ABRSM', 'Konservatuvar girisi', 'Universite sinavi', 'Diger'],
                'category'      => 'goals',
                'sort_order'    => 21,
                'translations'  => [
                    'tr' => [
                        'question_text' => 'Sınav hazırlığınız var mı?',
                        'options' => ['Hayır', 'ABRSM', 'Konservatuvar girişi', 'Üniversite sınavı', 'Diğer'],
                    ],
                    'en' => [
                        'question_text' => 'Are you preparing for any exams?',
                        'options' => ['No', 'ABRSM', 'Conservatory entrance', 'University exam', 'Other'],
                    ],
                    'de' => [
                        'question_text' => 'Bereiten Sie sich auf Prüfungen vor?',
                        'options' => ['Nein', 'ABRSM', 'Konservatoriumsaufnahme', 'Universitätsprüfung', 'Sonstiges'],
                    ],
                    'fr' => [
                        'question_text' => 'Vous préparez-vous à des examens ?',
                        'options' => ['Non', 'ABRSM', 'Entrée au conservatoire', 'Examen universitaire', 'Autre'],
                    ],
                    'es' => [
                        'question_text' => '¿Te estás preparando para algún examen?',
                        'options' => ['No', 'ABRSM', 'Ingreso al conservatorio', 'Examen universitario', 'Otro'],
                    ],
                    'it' => [
                        'question_text' => 'Ti stai preparando per degli esami?',
                        'options' => ['No', 'ABRSM', 'Ingresso al conservatorio', 'Esame universitario', 'Altro'],
                    ],
                    'ru' => [
                        'question_text' => 'Готовитесь ли вы к каким-либо экзаменам?',
                        'options' => ['Нет', 'ABRSM', 'Вступительные в консерваторию', 'Университетский экзамен', 'Другое'],
                    ],
                    'ar' => [
                        'question_text' => 'هل تستعد لأي امتحانات؟',
                        'options' => ['لا', 'ABRSM', 'قبول في المعهد الموسيقي', 'امتحان جامعي', 'أخرى'],
                    ],
                    'zh' => [
                        'question_text' => '您是否正在备考？',
                        'options' => ['否', 'ABRSM', '音乐学院入学考试', '大学考试', '其他'],
                    ],
                    'ja' => [
                        'question_text' => '何かの試験に向けて準備していますか？',
                        'options' => ['いいえ', 'ABRSM', '音楽院入学試験', '大学入試', 'その他'],
                    ],
                    'ko' => [
                        'question_text' => '어떤 시험을 준비 중인가요?',
                        'options' => ['아니요', 'ABRSM', '음악원 입학시험', '대학 시험', '기타'],
                    ],
                    'pt' => [
                        'question_text' => 'Você está se preparando para algum exame?',
                        'options' => ['Não', 'ABRSM', 'Ingresso no conservatório', 'Exame universitário', 'Outro'],
                    ],
                    'nl' => [
                        'question_text' => 'Bereidt u zich voor op examens?',
                        'options' => ['Nee', 'ABRSM', 'Conservatoriumtoelating', 'Universitair examen', 'Anders'],
                    ],
                    'pl' => [
                        'question_text' => 'Czy przygotowujesz się do jakichś egzaminów?',
                        'options' => ['Nie', 'ABRSM', 'Egzamin wstępny do konserwatorium', 'Egzamin na uczelnię', 'Inne'],
                    ],
                    'sv' => [
                        'question_text' => 'Förbereder du dig för några prov?',
                        'options' => ['Nej', 'ABRSM', 'Konservatorieantagning', 'Universitetsexamen', 'Annat'],
                    ],
                ],
            ],
            [
                'key'           => 'weekly_study_time',
                'question_text' => 'Haftada kac saat calisma zamani ayirabilirsiniz?',
                'question_type' => 'single_choice',
                'options'       => ['1 saatten az', '1-3 saat', '3-5 saat', '5-10 saat', '10 saatten fazla'],
                'category'      => 'goals',
                'sort_order'    => 22,
                'translations'  => [
                    'tr' => [
                        'question_text' => 'Haftada kaç saat çalışma zamanı ayırabilirsiniz?',
                        'options' => ['1 saatten az', '1-3 saat', '3-5 saat', '5-10 saat', '10 saatten fazla'],
                    ],
                    'en' => [
                        'question_text' => 'How many hours per week can you dedicate to practice?',
                        'options' => ['Less than 1 hour', '1-3 hours', '3-5 hours', '5-10 hours', 'More than 10 hours'],
                    ],
                    'de' => [
                        'question_text' => 'Wie viele Stunden pro Woche können Sie dem Üben widmen?',
                        'options' => ['Weniger als 1 Stunde', '1-3 Stunden', '3-5 Stunden', '5-10 Stunden', 'Mehr als 10 Stunden'],
                    ],
                    'fr' => [
                        'question_text' => 'Combien d\'heures par semaine pouvez-vous consacrer à la pratique ?',
                        'options' => ['Moins d\'1 heure', '1-3 heures', '3-5 heures', '5-10 heures', 'Plus de 10 heures'],
                    ],
                    'es' => [
                        'question_text' => '¿Cuántas horas por semana puedes dedicar a practicar?',
                        'options' => ['Menos de 1 hora', '1-3 horas', '3-5 horas', '5-10 horas', 'Más de 10 horas'],
                    ],
                    'it' => [
                        'question_text' => 'Quante ore a settimana puoi dedicare alla pratica?',
                        'options' => ['Meno di 1 ora', '1-3 ore', '3-5 ore', '5-10 ore', 'Più di 10 ore'],
                    ],
                    'ru' => [
                        'question_text' => 'Сколько часов в неделю вы можете посвятить занятиям?',
                        'options' => ['Менее 1 часа', '1-3 часа', '3-5 часов', '5-10 часов', 'Более 10 часов'],
                    ],
                    'ar' => [
                        'question_text' => 'كم ساعة في الأسبوع يمكنك تخصيصها للتدريب؟',
                        'options' => ['أقل من ساعة', '1-3 ساعات', '3-5 ساعات', '5-10 ساعات', 'أكثر من 10 ساعات'],
                    ],
                    'zh' => [
                        'question_text' => '每周您能花多少小时练习？',
                        'options' => ['不到1小时', '1-3小时', '3-5小时', '5-10小时', '超过10小时'],
                    ],
                    'ja' => [
                        'question_text' => '週に何時間練習に充てられますか？',
                        'options' => ['1時間未満', '1〜3時間', '3〜5時間', '5〜10時間', '10時間以上'],
                    ],
                    'ko' => [
                        'question_text' => '일주일에 몇 시간이나 연습할 수 있나요?',
                        'options' => ['1시간 미만', '1-3시간', '3-5시간', '5-10시간', '10시간 이상'],
                    ],
                    'pt' => [
                        'question_text' => 'Quantas horas por semana você pode dedicar à prática?',
                        'options' => ['Menos de 1 hora', '1-3 horas', '3-5 horas', '5-10 horas', 'Mais de 10 horas'],
                    ],
                    'nl' => [
                        'question_text' => 'Hoeveel uur per week kunt u aan oefenen besteden?',
                        'options' => ['Minder dan 1 uur', '1-3 uur', '3-5 uur', '5-10 uur', 'Meer dan 10 uur'],
                    ],
                    'pl' => [
                        'question_text' => 'Ile godzin tygodniowo możesz poświęcić na ćwiczenia?',
                        'options' => ['Mniej niż 1 godzina', '1-3 godziny', '3-5 godzin', '5-10 godzin', 'Ponad 10 godzin'],
                    ],
                    'sv' => [
                        'question_text' => 'Hur många timmar per vecka kan du ägna åt att öva?',
                        'options' => ['Mindre än 1 timme', '1-3 timmar', '3-5 timmar', '5-10 timmar', 'Mer än 10 timmar'],
                    ],
                ],
            ],

            // ── Preferences ──────────────────────────────────────────────────────
            [
                'key'           => 'learning_style',
                'question_text' => 'Tercih ettiginiz ogrenme yontemi nedir?',
                'question_type' => 'single_choice',
                'options'       => ['Gorsel (nota, grafik)', 'Isitsel (dinleme, tekrar)', 'Uygulamali (calarak ogrenme)', 'Karisik'],
                'category'      => 'preferences',
                'sort_order'    => 30,
                'translations'  => [
                    'tr' => [
                        'question_text' => 'Tercih ettiğiniz öğrenme yöntemi nedir?',
                        'options' => ['Görsel (nota, grafik)', 'İşitsel (dinleme, tekrar)', 'Uygulamalı (çalarak öğrenme)', 'Karışık'],
                    ],
                    'en' => [
                        'question_text' => 'What is your preferred learning style?',
                        'options' => ['Visual (notation, charts)', 'Auditory (listening, repetition)', 'Hands-on (learning by playing)', 'Mixed'],
                    ],
                    'de' => [
                        'question_text' => 'Was ist Ihr bevorzugter Lernstil?',
                        'options' => ['Visuell (Noten, Diagramme)', 'Auditiv (Zuhören, Wiederholen)', 'Praktisch (Lernen durch Spielen)', 'Gemischt'],
                    ],
                    'fr' => [
                        'question_text' => 'Quel est votre style d\'apprentissage préféré ?',
                        'options' => ['Visuel (notation, graphiques)', 'Auditif (écoute, répétition)', 'Pratique (apprendre en jouant)', 'Mixte'],
                    ],
                    'es' => [
                        'question_text' => '¿Cuál es tu estilo de aprendizaje preferido?',
                        'options' => ['Visual (notación, gráficos)', 'Auditivo (escuchar, repetir)', 'Práctico (aprender tocando)', 'Mixto'],
                    ],
                    'it' => [
                        'question_text' => 'Qual è il tuo stile di apprendimento preferito?',
                        'options' => ['Visivo (notazione, grafici)', 'Uditivo (ascolto, ripetizione)', 'Pratico (imparare suonando)', 'Misto'],
                    ],
                    'ru' => [
                        'question_text' => 'Какой стиль обучения вы предпочитаете?',
                        'options' => ['Визуальный (ноты, графики)', 'Слуховой (прослушивание, повторение)', 'Практический (обучение через игру)', 'Смешанный'],
                    ],
                    'ar' => [
                        'question_text' => 'ما هو أسلوب التعلم المفضل لديك؟',
                        'options' => ['بصري (نوتة، رسوم)', 'سمعي (استماع، تكرار)', 'عملي (التعلم بالعزف)', 'مختلط'],
                    ],
                    'zh' => [
                        'question_text' => '您偏好哪种学习方式？',
                        'options' => ['视觉（乐谱、图表）', '听觉（聆听、重复）', '动手（边弹边学）', '混合'],
                    ],
                    'ja' => [
                        'question_text' => '好みの学習スタイルは何ですか？',
                        'options' => ['視覚（楽譜、チャート）', '聴覚（リスニング、反復）', '実践（演奏して学ぶ）', '混合'],
                    ],
                    'ko' => [
                        'question_text' => '선호하는 학습 방식은 무엇인가요?',
                        'options' => ['시각적 (악보, 차트)', '청각적 (듣기, 반복)', '실습 (연주하며 배우기)', '혼합'],
                    ],
                    'pt' => [
                        'question_text' => 'Qual é o seu estilo de aprendizagem preferido?',
                        'options' => ['Visual (notação, gráficos)', 'Auditivo (ouvir, repetição)', 'Prático (aprender tocando)', 'Misto'],
                    ],
                    'nl' => [
                        'question_text' => 'Wat is uw voorkeursstijl van leren?',
                        'options' => ['Visueel (notatie, grafieken)', 'Auditief (luisteren, herhalen)', 'Praktisch (leren door spelen)', 'Gemengd'],
                    ],
                    'pl' => [
                        'question_text' => 'Jaki jest Twój preferowany styl nauki?',
                        'options' => ['Wzrokowy (nuty, wykresy)', 'Słuchowy (słuchanie, powtarzanie)', 'Praktyczny (nauka przez grę)', 'Mieszany'],
                    ],
                    'sv' => [
                        'question_text' => 'Vilken inlärningsstil föredrar du?',
                        'options' => ['Visuell (noter, diagram)', 'Auditiv (lyssna, repetera)', 'Praktisk (lär genom att spela)', 'Blandad'],
                    ],
                ],
            ],
            [
                'key'           => 'practice_time_preference',
                'question_text' => 'Gunun hangi saatlerinde calismayyi tercih edersiniz?',
                'question_type' => 'single_choice',
                'options'       => ['Sabah', 'Ogleden sonra', 'Aksam', 'Gece', 'Farketmez'],
                'category'      => 'preferences',
                'sort_order'    => 31,
                'translations'  => [
                    'tr' => [
                        'question_text' => 'Günün hangi saatlerinde çalışmayı tercih edersiniz?',
                        'options' => ['Sabah', 'Öğleden sonra', 'Akşam', 'Gece', 'Fark etmez'],
                    ],
                    'en' => [
                        'question_text' => 'At what time of day do you prefer to practice?',
                        'options' => ['Morning', 'Afternoon', 'Evening', 'Night', 'No preference'],
                    ],
                    'de' => [
                        'question_text' => 'Zu welcher Tageszeit üben Sie am liebsten?',
                        'options' => ['Morgen', 'Nachmittag', 'Abend', 'Nacht', 'Keine Präferenz'],
                    ],
                    'fr' => [
                        'question_text' => 'À quel moment de la journée préférez-vous pratiquer ?',
                        'options' => ['Matin', 'Après-midi', 'Soir', 'Nuit', 'Peu importe'],
                    ],
                    'es' => [
                        'question_text' => '¿En qué momento del día prefieres practicar?',
                        'options' => ['Mañana', 'Tarde', 'Noche temprana', 'Noche', 'No tengo preferencia'],
                    ],
                    'it' => [
                        'question_text' => 'In quale momento della giornata preferisci esercitarti?',
                        'options' => ['Mattina', 'Pomeriggio', 'Sera', 'Notte', 'Indifferente'],
                    ],
                    'ru' => [
                        'question_text' => 'В какое время суток вы предпочитаете заниматься?',
                        'options' => ['Утро', 'День', 'Вечер', 'Ночь', 'Без разницы'],
                    ],
                    'ar' => [
                        'question_text' => 'في أي وقت من اليوم تفضل التدريب؟',
                        'options' => ['الصباح', 'بعد الظهر', 'المساء', 'الليل', 'لا يهم'],
                    ],
                    'zh' => [
                        'question_text' => '您倾向于在一天中的哪个时间练习？',
                        'options' => ['早上', '下午', '傍晚', '夜间', '无所谓'],
                    ],
                    'ja' => [
                        'question_text' => '一日のどの時間帯に練習するのが好きですか？',
                        'options' => ['朝', '昼', '夕方', '夜', 'こだわらない'],
                    ],
                    'ko' => [
                        'question_text' => '하루 중 언제 연습하는 것을 선호하나요?',
                        'options' => ['아침', '오후', '저녁', '밤', '상관없음'],
                    ],
                    'pt' => [
                        'question_text' => 'Em que hora do dia você prefere praticar?',
                        'options' => ['Manhã', 'Tarde', 'Noite', 'Madrugada', 'Sem preferência'],
                    ],
                    'nl' => [
                        'question_text' => 'Op welk moment van de dag oefent u het liefst?',
                        'options' => ['Ochtend', 'Middag', 'Avond', 'Nacht', 'Maakt niet uit'],
                    ],
                    'pl' => [
                        'question_text' => 'O jakiej porze dnia wolisz ćwiczyć?',
                        'options' => ['Rano', 'Po południu', 'Wieczorem', 'W nocy', 'Obojętnie'],
                    ],
                    'sv' => [
                        'question_text' => 'Vid vilken tid på dagen föredrar du att öva?',
                        'options' => ['Morgon', 'Eftermiddag', 'Kväll', 'Natt', 'Spelar ingen roll'],
                    ],
                ],
            ],
            [
                'key'           => 'free_notes',
                'question_text' => 'Eklemek istediginiz notlariniz var mi?',
                'question_type' => 'text',
                'options'       => null,
                'category'      => 'preferences',
                'sort_order'    => 32,
                'is_required'   => false,
                'translations'  => [
                    'tr' => ['question_text' => 'Eklemek istediğiniz notlarınız var mı?'],
                    'en' => ['question_text' => 'Do you have any additional notes you\'d like to add?'],
                    'de' => ['question_text' => 'Haben Sie weitere Anmerkungen, die Sie hinzufügen möchten?'],
                    'fr' => ['question_text' => 'Avez-vous des notes supplémentaires que vous souhaitez ajouter ?'],
                    'es' => ['question_text' => '¿Tienes alguna nota adicional que quieras agregar?'],
                    'it' => ['question_text' => 'Hai note aggiuntive che vorresti aggiungere?'],
                    'ru' => ['question_text' => 'Есть ли у вас дополнительные заметки, которые вы хотели бы добавить?'],
                    'ar' => ['question_text' => 'هل لديك أي ملاحظات إضافية تود إضافتها؟'],
                    'zh' => ['question_text' => '您是否有任何想要补充的备注？'],
                    'ja' => ['question_text' => '追加したいメモはありますか？'],
                    'ko' => ['question_text' => '추가하고 싶은 메모가 있나요?'],
                    'pt' => ['question_text' => 'Você tem alguma nota adicional que gostaria de adicionar?'],
                    'nl' => ['question_text' => 'Heeft u nog aanvullende opmerkingen die u wilt toevoegen?'],
                    'pl' => ['question_text' => 'Czy masz jakieś dodatkowe notatki, które chciałbyś dodać?'],
                    'sv' => ['question_text' => 'Har du några ytterligare anteckningar du vill lägga till?'],
                ],
            ],
        ];

        foreach ($questions as $data) {
            QuestionnaireQuestion::updateOrCreate(
                ['key' => $data['key']],
                $data
            );
        }
    }
}
