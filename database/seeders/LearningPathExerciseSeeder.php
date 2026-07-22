<?php

namespace Database\Seeders;

use App\Livewire\PracticeIntervalComparison;
use App\Models\ExerciseCategory;
use App\Models\LearningPathExercise;
use Illuminate\Database\Seeder;

class LearningPathExerciseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = ExerciseCategory::all()->keyBy('slug');

        $curriculum = $this->buildCurriculum($categories);

        foreach ($curriculum as $exercise) {
            LearningPathExercise::updateOrCreate(
                ['slug' => $exercise['slug']],
                $exercise
            );
        }
    }

    private function buildCurriculum(iterable $categories): array
    {
        $data = [];

        // ── MELODIC INTERVALS ────────────────────────────────────────────────
        // Focused curriculum: every lesson teaches ONE core interval structure
        // in all its dimensions (both directions, full clef range). Configs
        // follow the Exercise Setup Studio rules — clef-driven pitch placement
        // (CLEF_RANGES), canonical interval names, no hardcoded octaves.
        // Advanced lessons (11–15) use 'near' distractors (nearest-neighbour
        // answer choices) for genuine half-step discrimination.
        $catId = $categories['melodic-intervals']->id ?? null;
        if ($catId) {
            $allTwelve = ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Tritone', 'Perfect 5th', 'Minor 6th', 'Major 6th', 'Minor 7th', 'Major 7th', 'Perfect Octave'];
            $near = ['distractor_mode' => 'near', 'distractor_count' => 3];
            $lessons = [
                [1, 'beginner',     5,  ['Perfect Unison', 'Perfect Octave'], 'treble', [],
                    'Unison & Octave: The Frame', 'Learn the identity interval (unison) and the octave — the frame every other interval lives inside. Hear the octave leap both rising and falling across the staff.',
                    'Unison ve Oktav: Çerçeve', 'Kimlik aralığı unison ile oktavı öğrenin — diğer tüm aralıkların içinde yaşadığı çerçeve. Oktav sıçramasını hem çıkıcı hem inici yönde duyun.',
                    ['unison', 'octave', 'perfect-intervals'], ['interval-recognition', 'audiation']],
                [2, 'beginner',     5,  ['Minor 2nd', 'Major 2nd'], 'treble', [],
                    'Seconds: Half Step vs Whole Step', 'Master the two smallest building blocks of melody. Every question contrasts the minor 2nd (half step) with the major 2nd (whole step), ascending and descending.',
                    'İkililer: Yarım ve Tam Adım', 'Melodinin en küçük iki yapı taşında ustalaşın. Her soru küçük ikiliyi (yarım adım) büyük ikiliyle (tam adım) karşılaştırır — çıkıcı ve inici yönde.',
                    ['seconds', 'half-step', 'whole-step'], ['interval-recognition', 'stepwise-hearing']],
                [3, 'beginner',     5,  ['Minor 3rd', 'Major 3rd'], 'treble', [],
                    'Thirds: The Major/Minor Colour', 'The third defines major versus minor. Learn its bright (major) and dark (minor) colour in both directions until the distinction is automatic.',
                    'Üçlüler: Majör/Minör Rengi', 'Üçlü, majör ile minörü belirler. Parlak (büyük) ve koyu (küçük) rengini her iki yönde, ayrım otomatikleşene kadar çalışın.',
                    ['thirds', 'chord-quality'], ['interval-recognition', 'major-minor-distinction']],
                [4, 'beginner',     5,  ['Perfect 4th', 'Perfect 5th'], 'treble', [],
                    'Perfect 4th & Perfect 5th', 'The two open, resonant pillars of harmony. Learn to tell the 4th from the 5th in both directions — the foundation of every melodic skeleton.',
                    'Tam Dörtlü ve Tam Beşli', 'Armoninin iki açık ve rezonant sütunu. Dörtlüyü beşliden her iki yönde ayırt etmeyi öğrenin — her melodik iskeletin temeli.',
                    ['perfect-intervals', 'fourths', 'fifths'], ['interval-recognition']],
                [5, 'beginner',     5,  ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Perfect 5th'], 'treble', [],
                    'Steps vs Leaps: First Synthesis', 'Bring lessons 1–4 together: seconds, thirds and perfect intervals in one pool, both directions. Your first cumulative test of the small-interval vocabulary.',
                    'Adımlar ve Sıçramalar: İlk Sentez', 'Ders 1–4\'ü birleştirin: ikililer, üçlüler ve tam aralıklar tek havuzda, iki yönde. Küçük aralık dağarcığınızın ilk birikimli testi.',
                    ['synthesis', 'steps', 'leaps'], ['interval-recognition', 'comprehensive']],
                [6, 'intermediate', 8,  ['Minor 6th', 'Major 6th'], 'treble', [],
                    'Sixths: The Expressive Leap', 'The sixth is melody\'s most expressive leap. Learn to separate the warm major 6th from the yearning minor 6th, rising and falling.',
                    'Altılılar: Anlatımcı Sıçrama', 'Altılı, melodinin en anlatımcı sıçramasıdır. Sıcak büyük altılıyı özlem dolu küçük altılıdan çıkıcı ve inici yönde ayırmayı öğrenin.',
                    ['sixths', 'expressive-leaps'], ['interval-recognition', 'major-minor-distinction']],
                [7, 'intermediate', 8,  ['Minor 7th', 'Major 7th'], 'treble', [],
                    'Sevenths: Tension Before the Octave', 'Sevenths sit one step short of the octave and carry strong tension. Contrast the gentler minor 7th with the razor-sharp major 7th in both directions.',
                    'Yedililer: Oktav Öncesi Gerilim', 'Yedililer oktavın bir adım altındadır ve güçlü gerilim taşır. Daha yumuşak küçük yediliyi keskin büyük yedili ile iki yönde karşılaştırın.',
                    ['sevenths', 'tension', 'dissonance'], ['interval-recognition', 'dissonance']],
                [8, 'intermediate', 8,  ['Major 3rd', 'Perfect 4th', 'Tritone', 'Perfect 5th'], 'treble', [],
                    'The Tritone Among Its Neighbours', 'Place the restless tritone against the intervals that surround it — major 3rd, perfect 4th and perfect 5th. Learn why it demands resolution.',
                    'Komşuları Arasında Triton', 'Huzursuz tritonu onu çevreleyen aralıkların — büyük üçlü, tam dörtlü ve tam beşli — karşısına koyun. Neden çözülme istediğini öğrenin.',
                    ['tritone', 'dissonance', 'context'], ['interval-recognition', 'chromatic-awareness']],
                [9, 'intermediate', 8,  ['Minor 3rd', 'Major 3rd', 'Minor 6th', 'Major 6th'], 'treble', [],
                    'Quality Hearing: 3rds & 6ths', 'Thirds and sixths are inversion partners that carry the major/minor colour. Train quality discrimination across both families in both directions.',
                    'Kalite Duyumu: Üçlüler ve Altılılar', 'Üçlüler ve altılılar, majör/minör rengini taşıyan çevrim ortaklarıdır. Her iki ailede ve her iki yönde kalite ayrımı çalışın.',
                    ['thirds', 'sixths', 'quality'], ['interval-recognition', 'major-minor-distinction']],
                [10, 'intermediate', 8, ['Perfect 4th', 'Perfect 5th', 'Minor 6th', 'Major 6th', 'Perfect Octave'], 'treble', [],
                    'Consonant Leaps', 'All the stable leaps in one pool — 4ths, 5ths, 6ths and the octave. Build fluent recognition of melody\'s consonant jumps in both directions.',
                    'Konsonant Sıçramalar', 'Tüm kararlı sıçramalar tek havuzda — dörtlüler, beşliler, altılılar ve oktav. Melodinin konsonant sıçramalarını iki yönde akıcı biçimde tanıyın.',
                    ['consonance', 'leaps'], ['interval-recognition', 'comprehensive']],
                [11, 'advanced',    10, ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Tritone'], 'treble', $near,
                    'Chromatic Precision: Small Intervals', 'Every interval from minor 2nd to the tritone, with answer choices deliberately drawn from the nearest neighbours. Half-step precision is the goal.',
                    'Kromatik Hassasiyet: Küçük Aralıklar', 'Küçük ikiliden tritona kadar her aralık; cevap şıkları bilinçli olarak en yakın komşu aralıklardan seçilir. Hedef, yarım ton hassasiyeti.',
                    ['chromatic', 'small-intervals'], ['interval-recognition', 'chromatic-precision']],
                [12, 'advanced',    10, ['Perfect 5th', 'Minor 6th', 'Major 6th', 'Minor 7th', 'Major 7th', 'Perfect Octave'], 'treble', $near,
                    'Chromatic Precision: Large Intervals', 'The upper half of the spectrum — perfect 5th to octave — with nearest-neighbour answer choices. Wide leaps in both directions.',
                    'Kromatik Hassasiyet: Büyük Aralıklar', 'Spektrumun üst yarısı — tam beşliden oktava — en yakın komşu şıklarla. Geniş sıçramalar iki yönde.',
                    ['chromatic', 'large-intervals'], ['interval-recognition', 'chromatic-precision']],
                [13, 'advanced',    10, ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Minor 6th', 'Major 6th', 'Minor 7th', 'Major 7th'], 'treble', $near,
                    'Inversion Pairs: 2nds↔7ths, 3rds↔6ths', 'An interval and its inversion add up to an octave: seconds invert to sevenths, thirds to sixths. Hear both sides of each pair and their major/minor swap.',
                    'Çevrim Çiftleri: 2↔7, 3↔6', 'Bir aralık ile çevrimi bir oktav eder: ikililer yedililere, üçlüler altılılara dönüşür. Her çiftin iki yüzünü ve majör/minör takasını duyun.',
                    ['inversions', 'pairs'], ['interval-recognition', 'inversion-hearing']],
                [14, 'advanced',    10, $allTwelve, 'treble', $near,
                    'The Full Twelve', 'All twelve intervals in a single pool with nearest-neighbour choices — the complete chromatic vocabulary in the treble range, both directions.',
                    'On İki Aralığın Tamamı', 'On iki aralığın tamamı tek havuzda, en yakın komşu şıklarla — sol anahtarı aralığında tam kromatik dağarcık, iki yönde.',
                    ['chromatic', 'comprehensive'], ['interval-recognition', 'mastery']],
                [15, 'advanced',    10, $allTwelve, 'bass', $near,
                    'Master: The Twelve in Bass Register', 'The capstone: all twelve intervals in the bass clef\'s low register (C2–C4). True mastery means recognizing every interval regardless of register.',
                    'Usta: Bas Register\'da On İki Aralık', 'Doruk noktası: on iki aralığın tamamı bas anahtarının pes register\'ında (C2–C4). Gerçek ustalık, her aralığı register\'dan bağımsız tanımaktır.',
                    ['master', 'bass-clef', 'register'], ['interval-recognition', 'mastery', 'register-independence']],
            ];
            foreach ($lessons as $l) {
                $data[] = $this->makeMelodicInterval('melodic-intervals', $catId, $l);
            }
        }

        // ── INTERVAL DIRECTION ───────────────────────────────────────────────
        // Focused curriculum: every lesson trains directional hearing on ONE
        // core interval structure, both directions. This is a pure listening
        // exercise (no staff is shown) — `clef` only selects the pitch
        // register via CLEF_RANGES (treble G3–G5, bass C2–C4, alto C3–C5).
        // Configs follow Exercise Setup Studio rules: clef-driven placement,
        // full natural-note pool, no hardcoded octaves. Lesson 15 passes a
        // clef ARRAY — each question draws its register at random.
        $catId = $categories['interval-direction']->id ?? null;
        if ($catId) {
            $allNotes = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
            $allTwelveSt = range(1, 12);
            $lessons = [
                [1, 'beginner',     5,  [1, 2],          $allNotes, 'treble',
                    'Steps: Up or Down', 'Stepwise motion is the backbone of melody. Hear half steps and whole steps rise and fall, and learn to feel the smallest possible change of direction.',
                    'Adımlar: Yukarı mı Aşağı mı', 'Adım hareketi melodinin omurgasıdır. Yarım ve tam adımların yükselip alçalışını duyun; yöndeki en küçük değişimi hissetmeyi öğrenin.',
                    ['steps', 'seconds'], ['directional-hearing', 'stepwise']],
                [2, 'beginner',     5,  [3, 4],          $allNotes, 'treble',
                    'Thirds: The First Leap', 'The third is the smallest true leap. Track minor and major 3rds in both directions and feel how a leap outlines the start of a chord.',
                    'Üçlüler: İlk Sıçrama', 'Üçlü, gerçek anlamda ilk sıçramadır. Küçük ve büyük üçlüleri iki yönde izleyin; bir sıçramanın akorun başlangıcını nasıl çizdiğini hissedin.',
                    ['thirds', 'leaps'], ['directional-hearing']],
                [3, 'beginner',     5,  [5, 7],          $allNotes, 'treble',
                    'Perfect 4th & 5th', 'The open, hollow leaps of the perfect 4th and 5th. Their strong, stable sound makes them ideal for anchoring your sense of up and down.',
                    'Tam Dörtlü ve Beşli', 'Tam dörtlü ve beşlinin açık, boş sıçramaları. Güçlü ve stabil sesleri, yukarı-aşağı duyunuzu sabitlemek için idealdir.',
                    ['perfect-intervals'], ['directional-hearing']],
                [4, 'beginner',     5,  [12],            $allNotes, 'treble',
                    'The Octave Leap', 'The octave — the same note in a new register. Learn to tell instantly whether the melody jumped a full octave up or a full octave down.',
                    'Oktav Sıçraması', 'Oktav — aynı notanın yeni bir register\'daki hâli. Melodinin tam bir oktav yukarı mı yoksa aşağı mı sıçradığını anında ayırt etmeyi öğrenin.',
                    ['octave', 'leaps'], ['directional-hearing', 'register']],
                [5, 'beginner',     5,  [1, 2, 3, 4],    $allNotes, 'treble',
                    'Synthesis: Step or Leap', 'Seconds and thirds together — the four most common melodic moves. Build fluent, automatic direction calls on the intervals melodies use most.',
                    'Sentez: Adım mı Sıçrama mı', 'İkililer ve üçlüler bir arada — en yaygın dört melodik hareket. Melodilerin en çok kullandığı aralıklarda akıcı, otomatik yön tespiti geliştirin.',
                    ['synthesis', 'seconds', 'thirds'], ['directional-hearing', 'stepwise']],
                [6, 'intermediate', 8,  [8, 9],          $allNotes, 'treble',
                    'Sixths: Wide and Sweet', 'Minor and major 6ths span most of the octave. Wide leaps can fool the ear — learn to track their direction without losing the first note.',
                    'Altılılar: Geniş ve Tatlı', 'Küçük ve büyük altılılar oktavın büyük bölümünü kapsar. Geniş sıçramalar kulağı yanıltabilir — ilk notayı kaybetmeden yönlerini izlemeyi öğrenin.',
                    ['sixths', 'large-intervals'], ['directional-hearing']],
                [7, 'intermediate', 8,  [10, 11],        $allNotes, 'treble',
                    'Sevenths: Maximum Tension', 'The 7ths reach almost a full octave and carry the strongest melodic tension. Judge their direction confidently despite the dissonant stretch.',
                    'Yedililer: Azami Gerilim', 'Yedililer neredeyse tam bir oktava uzanır ve en güçlü melodik gerilimi taşır. Disonant açıklığa rağmen yönlerini güvenle değerlendirin.',
                    ['sevenths', 'large-intervals'], ['directional-hearing']],
                [8, 'intermediate', 8,  [5, 6, 7],       $allNotes, 'treble',
                    'The Tritone and Its Neighbours', 'The unstable tritone sits between the perfect 4th and 5th. Hear all three mid-range leaps in both directions and keep your bearings.',
                    'Triton ve Komşuları', 'Kararsız triton, tam dörtlü ile beşlinin arasında durur. Bu üç orta boy sıçramayı iki yönde duyun ve yön duyunuzu koruyun.',
                    ['tritone', 'perfect-intervals'], ['directional-hearing']],
                [9, 'intermediate', 8,  [1, 2, 3, 4],    $allNotes, 'bass',
                    'Low Register: Steps & Thirds', 'The same steps and thirds, now deep in the bass range (C2–C4). Low pitches blur more easily — sharpen your directional hearing where it is hardest.',
                    'Pes Register: Adımlar ve Üçlüler', 'Aynı adımlar ve üçlüler, bu kez pes bas bölgesinde (C2–C4). Düşük perdeler daha kolay bulanıklaşır — yön duyunuzu en zor olduğu yerde keskinleştirin.',
                    ['low-register', 'steps', 'thirds'], ['directional-hearing', 'register']],
                [10, 'intermediate', 8, [5, 7, 8, 9, 12], $allNotes, 'treble',
                    'Consonant Leaps', 'All the stable leaps in one pool — perfect 4th, 5th, both 6ths and the octave. Tell wide consonant leaps apart by size and direction.',
                    'Konsonant Sıçramalar', 'Tüm stabil sıçramalar tek havuzda — tam dörtlü, beşli, iki altılı ve oktav. Geniş konsonant sıçramaları boyut ve yön olarak ayırt edin.',
                    ['consonance', 'leaps'], ['directional-hearing']],
                [11, 'advanced',    10, $allTwelveSt,    $allNotes, 'treble',
                    'The Full Twelve: High Register', 'Every interval from minor 2nd to octave in the middle-high range (G3–G5). Complete directional command of the register melodies live in.',
                    'On İki Aralık: Tiz Register', 'Küçük ikiliden oktava tüm aralıklar, orta-tiz bölgede (G3–G5). Melodilerin yaşadığı register\'da eksiksiz yön hâkimiyeti.',
                    ['comprehensive', 'high-register'], ['directional-hearing', 'mastery']],
                [12, 'advanced',    10, $allTwelveSt,    $allNotes, 'bass',
                    'The Full Twelve: Low Register', 'The complete chromatic set moved into the bass range (C2–C4). True mastery means the register never changes your answer.',
                    'On İki Aralık: Pes Register', 'Tam kromatik set bas bölgesine (C2–C4) taşındı. Gerçek ustalık, register\'ın cevabınızı asla değiştirmemesidir.',
                    ['comprehensive', 'low-register'], ['directional-hearing', 'mastery', 'register-independence']],
                [13, 'advanced',    10, $allTwelveSt,    $allNotes, 'alto',
                    'The Full Twelve: Middle Register', 'All twelve intervals in the middle range (C3–C5) — the zone shared by voices, viola and cello, where up and down are subtlest.',
                    'On İki Aralık: Orta Register', 'On iki aralığın tamamı orta bölgede (C3–C5) — insan sesi, viyola ve çellonun paylaştığı, yukarı-aşağının en incelikli olduğu bölge.',
                    ['comprehensive', 'middle-register'], ['directional-hearing', 'mastery']],
                [14, 'advanced',    10, [1, 2, 10, 11],  $allNotes, 'treble',
                    'Extremes Drill: 2nds vs 7ths', 'Rapid-fire contrast between the smallest and largest moves — tiny seconds against huge sevenths. Build instant, automatic direction calls.',
                    'Uçlar Drilli: İkililer vs Yedililer', 'En küçük ve en büyük hareketler arasında hızlı zıtlık — minicik ikililere karşı kocaman yedililer. Anında, otomatik yön tespiti geliştirin.',
                    ['extremes', 'speed'], ['directional-hearing', 'speed-training']],
                [15, 'advanced',    10, $allTwelveSt,    $allNotes, ['treble', 'bass', 'alto'],
                    'Master: Every Interval, Every Register', 'The capstone: all twelve intervals, with each question drawn at random from the low, middle or high register. No interval, no register should surprise you.',
                    'Usta: Her Aralık, Her Register', 'Doruk noktası: on iki aralığın tamamı; her soru pes, orta veya tiz register\'dan rastgele gelir. Hiçbir aralık, hiçbir register sizi şaşırtmamalı.',
                    ['master', 'all-registers'], ['directional-hearing', 'mastery', 'register-independence']],
            ];
            foreach ($lessons as $l) {
                $data[] = $this->makeDirection('interval-direction', $catId, $l);
            }
        }

        // ── HARMONIC INTERVALS ───────────────────────────────────────────────
        // Focused curriculum mirroring the melodic-intervals track: every
        // lesson teaches ONE core dyad structure in all its dimensions.
        // Configs follow the Exercise Setup Studio rules — clef-driven pitch
        // placement (CLEF_RANGES), canonical interval names (Tritone; no
        // Augmented 4th / Diminished 5th aliases), no hardcoded octaves, no
        // Perfect Unison (two identical simultaneous notes read as one pitch;
        // the Studio pool deliberately excludes it). Advanced lessons (11–15)
        // use 'near' distractors for genuine half-step discrimination.
        $catId = $categories['harmonic-intervals']->id ?? null;
        if ($catId) {
            $allTwelve = ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Tritone', 'Perfect 5th', 'Minor 6th', 'Major 6th', 'Minor 7th', 'Major 7th', 'Perfect Octave'];
            $near = ['distractor_mode' => 'near', 'distractor_count' => 3];
            $lessons = [
                [1, 'beginner',     5,  ['Perfect 5th', 'Perfect Octave'], 'treble', [],
                    'Octave & Fifth: The Pillars', 'The two most stable simultaneous sounds: the octave (the same note doubled) and the open perfect 5th. Learn the resonant frame all harmony is built on.',
                    'Oktav ve Beşli: Sütunlar', 'En stabil iki eş zamanlı ses: oktav (aynı notanın katlanması) ve açık tam beşli. Tüm armoninin üzerine kurulduğu rezonant çerçeveyi öğrenin.',
                    ['octave', 'fifths', 'perfect-intervals'], ['harmonic-hearing', 'consonance']],
                [2, 'beginner',     5,  ['Perfect 4th', 'Perfect 5th'], 'treble', [],
                    'Perfect 4th vs Perfect 5th', 'Both sound open and hollow, yet they differ by a whole step. Learn to tell the wider, grounded 5th from the tighter, suspended 4th when they ring together.',
                    'Tam Dörtlü vs Tam Beşli', 'İkisi de açık ve boş tınlar, ama aralarında bir tam adım fark vardır. Birlikte tınlarken daha geniş ve oturmuş beşliyi, daha dar ve askıda duran dörtlüden ayırmayı öğrenin.',
                    ['perfect-intervals', 'fourths', 'fifths'], ['harmonic-hearing']],
                [3, 'beginner',     5,  ['Minor 3rd', 'Major 3rd'], 'treble', [],
                    'Thirds: The Major/Minor Colour', 'The third gives harmony its emotional colour — warm and bright (major) or dark and melancholic (minor). Master the distinction that defines tonality itself.',
                    'Üçlüler: Majör/Minör Rengi', 'Armoni duygusal rengini üçlüden alır — sıcak ve parlak (majör) ya da koyu ve melankolik (minör). Tonalitenin kendisini tanımlayan bu ayrımda ustalaşın.',
                    ['thirds', 'major-minor'], ['harmonic-hearing', 'chord-quality']],
                [4, 'beginner',     5,  ['Minor 2nd', 'Major 2nd'], 'treble', [],
                    'Seconds: The Clash', 'The dissonant seconds sounded together: the biting minor 2nd against the softer but still tense major 2nd. Learn to hear inside the clash instead of flinching from it.',
                    'İkililer: Çarpışma', 'Birlikte tınlayan disonant ikililer: ısıran küçük ikiliye karşı daha yumuşak ama yine de gergin büyük ikili. Çarpışmadan kaçınmak yerine içini duymayı öğrenin.',
                    ['seconds', 'dissonance'], ['harmonic-hearing', 'dissonance']],
                [5, 'beginner',     5,  ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Perfect 5th'], 'treble', [],
                    'First Synthesis: Small Dyads', 'Lessons 1–4 combined: seconds, thirds and perfect intervals in one pool. Your first cumulative test of naming simultaneous sounds.',
                    'İlk Sentez: Küçük Dyadlar', 'Ders 1–4 birleşti: ikililer, üçlüler ve tam aralıklar tek havuzda. Eş zamanlı sesleri adlandırmanın ilk birikimli testi.',
                    ['synthesis', 'diatonic'], ['harmonic-hearing', 'comprehensive']],
                [6, 'intermediate', 8,  ['Minor 6th', 'Major 6th'], 'treble', [],
                    'Sixths: The Sweet Consonance', 'Sixths are the warmest of the wide consonances — the backbone of parallel harmonization. Separate the bright major 6th from the wistful minor 6th.',
                    'Altılılar: Tatlı Konsonans', 'Altılılar, geniş konsonansların en sıcağıdır — paralel armonizasyonun omurgası. Parlak büyük altılıyı hüzünlü küçük altılıdan ayırın.',
                    ['sixths', 'consonance'], ['harmonic-hearing', 'chord-quality']],
                [7, 'intermediate', 8,  ['Minor 7th', 'Major 7th'], 'treble', [],
                    'Sevenths: Harmonic Tension', 'Sevenths carry harmony\'s strongest pull toward resolution. Contrast the dominant-chord minor 7th with the razor-sharp major 7th as simultaneous sounds.',
                    'Yedililer: Harmonik Gerilim', 'Yedililer, armoninin çözülüme en güçlü çekimini taşır. Dominant akorun küçük yedilisini, keskin büyük yedili ile eş zamanlı sesler olarak karşılaştırın.',
                    ['sevenths', 'tension', 'dissonance'], ['harmonic-hearing', 'dissonance']],
                [8, 'intermediate', 8,  ['Major 3rd', 'Perfect 4th', 'Tritone', 'Perfect 5th'], 'treble', [],
                    'The Tritone Among Its Neighbours', 'Place the restless tritone against the intervals that surround it — major 3rd, perfect 4th and perfect 5th. Hear why it demands resolution while they rest.',
                    'Komşuları Arasında Triton', 'Huzursuz tritonu onu çevreleyen aralıkların — büyük üçlü, tam dörtlü ve tam beşli — karşısına koyun. Onlar dinlenirken tritonun neden çözülme istediğini duyun.',
                    ['tritone', 'dissonance', 'context'], ['harmonic-hearing', 'chromatic-awareness']],
                [9, 'intermediate', 8,  ['Minor 3rd', 'Major 3rd', 'Minor 6th', 'Major 6th'], 'treble', [],
                    'Quality Hearing: 3rds & 6ths', 'Thirds and sixths are inversion partners that carry the major/minor colour. Train quality discrimination across both families of sweet consonances.',
                    'Kalite Duyumu: Üçlüler ve Altılılar', 'Üçlüler ve altılılar, majör/minör rengini taşıyan çevrim ortaklarıdır. Her iki tatlı konsonans ailesinde kalite ayrımı çalışın.',
                    ['thirds', 'sixths', 'quality'], ['harmonic-hearing', 'chord-quality']],
                [10, 'intermediate', 8, ['Minor 2nd', 'Major 2nd', 'Perfect 4th', 'Tritone', 'Perfect 5th', 'Minor 7th', 'Major 7th', 'Perfect Octave'], 'treble', [],
                    'Consonance vs Dissonance', 'The core categorical skill of harmonic hearing: stable open consonances (4th, 5th, octave) against sharp dissonances (2nds, tritone, 7ths) in one pool.',
                    'Konsonans vs Disonans', 'Harmonik duyumun temel kategorik becerisi: kararlı açık konsonanslar (dörtlü, beşli, oktav) ile keskin disonanslar (ikililer, triton, yedililer) tek havuzda.',
                    ['consonance', 'dissonance'], ['harmonic-hearing', 'perception']],
                [11, 'advanced',    10, ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Tritone'], 'treble', $near,
                    'Chromatic Precision: Small Dyads', 'Every dyad from minor 2nd to the tritone, with answer choices deliberately drawn from the nearest neighbours. Half-step precision is the goal.',
                    'Kromatik Hassasiyet: Küçük Dyadlar', 'Küçük ikiliden tritona kadar her dyad; cevap şıkları bilinçli olarak en yakın komşu aralıklardan seçilir. Hedef, yarım ton hassasiyeti.',
                    ['chromatic', 'small-intervals'], ['harmonic-hearing', 'fine-discrimination']],
                [12, 'advanced',    10, ['Perfect 5th', 'Minor 6th', 'Major 6th', 'Minor 7th', 'Major 7th', 'Perfect Octave'], 'treble', $near,
                    'Chromatic Precision: Wide Dyads', 'The upper half of the spectrum — perfect 5th to octave — with nearest-neighbour answer choices. Wide dyads blur easily; learn to keep them apart.',
                    'Kromatik Hassasiyet: Geniş Dyadlar', 'Spektrumun üst yarısı — tam beşliden oktava — en yakın komşu şıklarla. Geniş dyadlar kolay bulanıklaşır; onları ayrı tutmayı öğrenin.',
                    ['chromatic', 'wide-intervals'], ['harmonic-hearing', 'fine-discrimination']],
                [13, 'advanced',    10, ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Minor 6th', 'Major 6th', 'Minor 7th', 'Major 7th'], 'treble', $near,
                    'Inversion Pairs: 2nds↔7ths, 3rds↔6ths', 'An interval and its inversion add up to an octave: seconds invert to sevenths, thirds to sixths. Hear both sides of each pair and their major/minor swap.',
                    'Çevrim Çiftleri: 2↔7, 3↔6', 'Bir aralık ile çevrimi bir oktav eder: ikililer yedililere, üçlüler altılılara dönüşür. Her çiftin iki yüzünü ve majör/minör takasını duyun.',
                    ['inversions', 'pairs'], ['harmonic-hearing', 'inversion-hearing']],
                [14, 'advanced',    10, $allTwelve, 'treble', $near,
                    'The Full Twelve', 'All twelve dyads in a single pool with nearest-neighbour choices — the complete chromatic vocabulary of simultaneous sounds in the treble range.',
                    'On İki Aralığın Tamamı', 'On iki dyadın tamamı tek havuzda, en yakın komşu şıklarla — sol anahtarı aralığında eş zamanlı seslerin tam kromatik dağarcığı.',
                    ['chromatic', 'comprehensive'], ['harmonic-hearing', 'mastery']],
                [15, 'advanced',    10, $allTwelve, 'bass', $near,
                    'Master: The Twelve in Bass Register', 'The capstone: all twelve dyads in the bass clef\'s low register (C2–C4), where close intervals blur the most. True mastery is register-independent.',
                    'Usta: Bas Register\'da On İki Aralık', 'Doruk noktası: on iki dyadın tamamı bas anahtarının pes register\'ında (C2–C4) — dar aralıkların en çok bulanıklaştığı yerde. Gerçek ustalık register\'dan bağımsızdır.',
                    ['master', 'bass-clef', 'register'], ['harmonic-hearing', 'mastery', 'register-independence']],
            ];
            foreach ($lessons as $l) {
                $data[] = $this->makeHarmonicInterval('harmonic-intervals', $catId, $l);
            }
        }

        // ── INTERVAL CONSTRUCTION ────────────────────────────────────────────
        // Focused curriculum: every lesson teaches ONE core construction
        // structure in all its dimensions. Construction is a WRITTEN skill, so
        // the dimensions are spelling (which accidental, and why), direction
        // (build above vs below), root family (naturals → sharps → flats) and
        // clef. Configs follow the Exercise Setup Studio rules — clef-driven
        // pitch placement (CLEF_RANGES), canonical interval names (Tritone; no
        // Augmented 4th / Diminished 5th aliases), no hardcoded octaves.
        // Sharp/flat-root lessons (11–12) use interval subsets curated so every
        // diatonic answer is a single-accidental, playable spelling (no ##/bb,
        // no B#/Cb). Advanced lessons (11–15) use 'near' distractors — answer
        // choices a half step from the correct note — for genuine spelling
        // precision.
        $catId = $categories['interval-construction']->id ?? null;
        if ($catId) {
            $allRoots = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
            $allTwelve = ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Tritone', 'Perfect 5th', 'Minor 6th', 'Major 6th', 'Minor 7th', 'Major 7th', 'Perfect Octave'];
            $near = ['distractor_mode' => 'near', 'distractor_count' => 3];
            $lessons = [
                [1, 'beginner',  5,  ['Major 2nd'], $allRoots, 'treble', 'ascending', [],
                    'Whole Steps: Build Major 2nds', 'Build the whole step above every natural root. E and B force your first sharps — F# and C# — the moment note spelling becomes a real skill.',
                    'Tam Adımlar: Büyük İkili Kur', 'Her doğal kökün üzerine tam adım kurun. E ve B kökleri ilk diyezlerinizi — F# ve C# — zorunlu kılar; nota hecelemenin gerçek bir beceriye dönüştüğü an.',
                    ['major-2nd', 'whole-step', 'construction'], ['interval-building', 'note-spelling']],
                [2, 'beginner',  5,  ['Minor 2nd'], $allRoots, 'treble', 'ascending', [],
                    'Half Steps: Build Minor 2nds', 'Build the half step above every natural root. Only E–F and B–C are natural half steps; every other root needs a flat — learn why C rises to Db, not C#.',
                    'Yarım Adımlar: Küçük İkili Kur', 'Her doğal kökün üzerine yarım adım kurun. Yalnızca E–F ve B–C doğal yarım adımdır; diğer tüm kökler bemol gerektirir — C\'nin neden C#\'a değil Db\'ye yükseldiğini öğrenin.',
                    ['minor-2nd', 'half-step', 'construction'], ['interval-building', 'note-spelling']],
                [3, 'beginner',  5,  ['Minor 3rd', 'Major 3rd'], $allRoots, 'treble', 'ascending', [],
                    'Thirds: Spell the Major/Minor Colour', 'Build major and minor 3rds above every natural root. The 3rd decides major versus minor — count two letter names up, then check the semitones: four for major, three for minor.',
                    'Üçlüler: Majör/Minör Rengini Hecele', 'Her doğal kökün üzerine büyük ve küçük üçlüler kurun. Üçlü, majör ile minörü belirler — iki harf adı yukarı sayın, sonra yarım tonları kontrol edin: büyük için dört, küçük için üç.',
                    ['thirds', 'construction'], ['interval-building', 'major-minor']],
                [4, 'beginner',  5,  ['Perfect 4th', 'Perfect 5th'], $allRoots, 'treble', 'ascending', [],
                    'Perfect 4ths & 5ths: The Pillars', 'Build the two pillars of harmony above every natural root. All are pure letter counts except the F–B pair — a perfect 4th above F needs Bb, a perfect 5th above B needs F#.',
                    'Tam Dörtlü ve Beşli: Sütunlar', 'Armoninin iki sütununu her doğal kökün üzerine kurun. F–B çifti dışında hepsi saf harf sayımıdır — F\'nin tam dörtlü üstü Bb, B\'nin tam beşli üstü F# ister.',
                    ['perfect-intervals', 'fourths', 'fifths'], ['interval-building']],
                [5, 'beginner',  5,  ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd', 'Perfect 4th', 'Perfect 5th'], $allRoots, 'treble', 'ascending', [],
                    'First Synthesis: 2nds to 5ths', 'Lessons 1–4 in one pool: build any interval from minor 2nd to perfect 5th above any natural root. Your first cumulative construction test.',
                    'İlk Sentez: İkiliden Beşliye', 'Ders 1–4 tek havuzda: herhangi bir doğal kökün üzerine küçük ikiliden tam beşliye kadar herhangi bir aralığı kurun. İlk birikimli inşa testiniz.',
                    ['synthesis', 'construction'], ['interval-building', 'comprehensive']],
                [6, 'intermediate', 8, ['Minor 6th', 'Major 6th'], $allRoots, 'treble', 'ascending', [],
                    'Sixths: Think in Inversions', 'Build 6ths above every natural root. The shortcut: a major 6th is an inverted minor 3rd, a minor 6th an inverted major 3rd — think down a 3rd, then up an octave.',
                    'Altılılar: Çevrimlerle Düşün', 'Her doğal kökün üzerine altılılar kurun. Kısayol: büyük altılı ters çevrilmiş küçük üçlü, küçük altılı ters çevrilmiş büyük üçlüdür — bir üçlü aşağı, sonra bir oktav yukarı düşünün.',
                    ['sixths', 'inversions', 'construction'], ['interval-building', 'inversion-thinking']],
                [7, 'intermediate', 8, ['Minor 7th', 'Major 7th'], $allRoots, 'treble', 'ascending', [],
                    'Sevenths: Count Down from the Octave', 'Build 7ths above every natural root. Work from the octave: one semitone below it is the major 7th, two below is the minor 7th — far faster than counting up.',
                    'Yedililer: Oktavdan Geriye Say', 'Her doğal kökün üzerine yedililer kurun. Oktavdan çalışın: bir yarım ton altı büyük yedili, iki yarım ton altı küçük yedili — yukarı saymaktan çok daha hızlı.',
                    ['sevenths', 'construction'], ['interval-building', 'octave-reference']],
                [8, 'intermediate', 8, ['Tritone'], $allRoots, 'treble', 'ascending', [],
                    'The Tritone: Six Semitones Exactly', 'Build the tritone — exactly six semitones, splitting the octave in half — above every natural root. Only F–B needs no accidental; every other root demands one.',
                    'Triton: Tam Altı Yarım Ton', 'Her doğal kökün üzerine tritonu — oktavı tam ortadan bölen altı yarım tonu — kurun. Yalnızca F–B arıza istemez; diğer tüm kökler bir arıza talep eder.',
                    ['tritone', 'construction', 'chromatic'], ['interval-building', 'chromatic-awareness']],
                [9, 'intermediate', 8, ['Minor 2nd', 'Major 2nd', 'Minor 3rd', 'Major 3rd'], $allRoots, 'treble', 'descending', [],
                    'Building Downward: Steps & Thirds', 'A new dimension: build 2nds and 3rds BELOW the given note. Descending construction reverses your habits — a minor 2nd below E is D#, not Eb.',
                    'Aşağı Doğru İnşa: Adımlar ve Üçlüler', 'Yeni bir boyut: ikilileri ve üçlüleri verilen notanın ALTINA kurun. İnici inşa alışkanlıklarınızı tersine çevirir — E\'nin bir küçük ikili altı Eb değil D#\'tır.',
                    ['descending', 'steps', 'thirds'], ['interval-building', 'descending-construction']],
                [10, 'intermediate', 8, ['Perfect 4th', 'Perfect 5th', 'Minor 6th', 'Major 6th', 'Minor 7th', 'Major 7th'], $allRoots, 'treble', 'descending', [],
                    'Building Downward: 4ths to 7ths', 'Build the wide intervals below the given note. Use inversion to stay fast: a perfect 5th below lands on the same letter as a perfect 4th above, one octave lower.',
                    'Aşağı Doğru İnşa: Dörtlüden Yediliye', 'Geniş aralıkları verilen notanın altına kurun. Hızlı kalmak için çevrimi kullanın: bir tam beşli aşağısı, bir tam dörtlü yukarısıyla aynı harfe düşer — bir oktav peste.',
                    ['descending', 'large-intervals'], ['interval-building', 'descending-construction', 'inversion-thinking']],
                [11, 'advanced', 10, ['Major 2nd', 'Minor 3rd', 'Perfect 4th', 'Perfect 5th', 'Minor 7th'], ['C#', 'F#', 'G#'], 'treble', 'ascending', $near,
                    'Build from Sharp Roots', 'Start from C#, F# and G# instead of naturals. The accidental travels with the letter arithmetic — a perfect 5th above C# is G#, never Ab. Nearest-neighbour answer choices punish sloppy spelling.',
                    'Diyezli Köklerden Kur', 'Doğal kökler yerine C#, F# ve G#\'tan başlayın. Arıza, harf aritmetiğiyle birlikte taşınır — C#\'ın tam beşli üstü G#\'tır, asla Ab değil. En yakın komşu şıklar özensiz hecelemeyi affetmez.',
                    ['sharps', 'accidental-roots', 'construction'], ['interval-building', 'chromatic-roots']],
                [12, 'advanced', 10, ['Major 2nd', 'Major 3rd', 'Perfect 4th', 'Perfect 5th', 'Major 6th', 'Minor 7th'], ['Bb', 'Eb', 'Ab'], 'treble', 'ascending', $near,
                    'Build from Flat Roots', 'Construction from Bb, Eb and Ab — the roots band and orchestral players live on. Flat-side spelling keeps the letter chain intact: a perfect 4th above Bb is Eb, not D#.',
                    'Bemollü Köklerden Kur', 'Bb, Eb ve Ab\'den inşa — bando ve orkestra çalgıcılarının yaşadığı kökler. Bemol tarafı heceleme harf zincirini korur: Bb\'nin tam dörtlü üstü D# değil Eb\'dir.',
                    ['flats', 'accidental-roots', 'construction'], ['interval-building', 'chromatic-roots']],
                [13, 'advanced', 10, $allTwelve, $allRoots, 'treble', 'ascending', $near,
                    'The Full Twelve', 'Every interval from minor 2nd to the octave above any natural root, with answer choices drawn from the nearest semitone neighbours. Complete upward construction fluency.',
                    'On İki Aralığın Tamamı', 'Herhangi bir doğal kökün üzerine küçük ikiliden oktava kadar her aralık; şıklar doğru cevabın en yakın yarım ton komşularından seçilir. Eksiksiz yukarı yönlü inşa akıcılığı.',
                    ['chromatic', 'comprehensive', 'construction'], ['interval-building', 'mastery']],
                [14, 'advanced', 10, $allTwelve, $allRoots, 'treble', 'mixed', $near,
                    'The Full Twelve, Both Directions', 'All twelve intervals, built above or below the given note at random. Reading the direction before you build is now part of the skill.',
                    'On İki Aralık, İki Yön', 'On iki aralığın tamamı, verilen notanın rastgele üstüne veya altına kurulur. Kurmadan önce yönü okumak artık becerinin parçası.',
                    ['chromatic', 'mixed-direction'], ['interval-building', 'descending-construction', 'mastery']],
                [15, 'advanced', 10, $allTwelve, $allRoots, 'bass', 'mixed', $near,
                    'Master: Construction in the Bass Clef', 'The capstone: every interval, both directions, written in the bass clef\'s low register (C2–C4). True construction mastery is clef-independent.',
                    'Usta: Bas Anahtarında İnşa', 'Doruk noktası: her aralık, iki yönde, bas anahtarının pes bölgesinde (C2–C4) yazılır. Gerçek inşa ustalığı anahtardan bağımsızdır.',
                    ['master', 'bass-clef', 'register'], ['interval-building', 'mastery', 'register-independence']],
            ];
            foreach ($lessons as $l) {
                $data[] = $this->makeConstruction('interval-construction', $catId, $l);
            }
        }

        // ── INTERVAL COMPARISON ──────────────────────────────────────────────
        // Focused curriculum: every lesson isolates ONE size-comparison
        // structure and teaches it exhaustively. Configs follow the Exercise
        // Setup Studio rules — canonical same-octave pair spellings
        // (PracticeIntervalComparison::POOL_TO_PAIR), clef-driven pitch
        // placement, no hardcoded octaves. The generator transposes each pair
        // onto every natural root that fits the octave and adds the A/B-swapped
        // variant, so each lesson covers its structure in all dimensions.
        // The octave interval is deliberately absent: the comparison engine
        // renders both notes in one shared octave (Studio omits 8ve too).
        $catId = $categories['interval-comparison']->id ?? null;
        if ($catId) {
            // Canonical same-octave C-root pair per interval abbreviation.
            $p = PracticeIntervalComparison::POOL_TO_PAIR;
            $compLessons = [
                [1, 'beginner',  5, 'treble', [[$p['M2'], $p['M3']], [$p['m2'], $p['m3']]],
                    '2nd vs 3rd: Step or Skip?', 'Hear the boundary between a step (2nd) and a skip (3rd), in both their major and minor forms. Each pair appears on many different roots, with the larger interval sometimes played first, sometimes second.',
                    '2\'li mi 3\'lü mü: Adım mı Atlama mı?', 'Adım (ikili) ile atlama (üçlü) arasındaki sınırı hem büyük hem küçük biçimleriyle duyun. Her çift farklı köklerde gelir; büyük aralık bazen önce, bazen sonra çalınır.',
                    ['seconds', 'thirds', 'step-vs-skip'], ['comparison', 'interval-size']],
                [2, 'beginner',  5, 'treble', [[$p['M3'], $p['P4']], [$p['m3'], $p['P4']]],
                    '3rd vs Perfect 4th', 'The 4th sits just above the third — one or two semitones apart. Learn this adjacent-category boundary against both the major and the minor 3rd, across many roots.',
                    'Üçlü ve Tam Dörtlü', 'Dörtlü, üçlünün hemen üzerindedir — bir veya iki yarım ton fark. Bu bitişik kategori sınırını hem büyük hem küçük üçlüye karşı, farklı köklerde öğrenin.',
                    ['thirds', 'fourths', 'adjacent-categories'], ['comparison', 'interval-size']],
                [3, 'beginner',  5, 'treble', [[$p['P4'], $p['P5']]],
                    'Perfect 4th vs Perfect 5th', 'One single, essential contrast: the two open pillars of harmony, a whole tone apart. Nothing else in the pool — drill this pair on every root until the difference is automatic.',
                    'Tam Dörtlü ve Tam Beşli', 'Tek ve temel bir kontrast: armoninin iki açık sütunu, aralarında bir tam ton. Havuzda başka hiçbir şey yok — fark otomatikleşene kadar bu çifti her kökte çalışın.',
                    ['fourths', 'fifths', 'perfect-intervals'], ['comparison', 'interval-size']],
                [4, 'beginner',  5, 'treble', [[$p['m2'], $p['P4']], [$p['M2'], $p['P5']], [$p['m3'], $p['M6']], [$p['M3'], $p['P5']]],
                    'Wide Contrast: Small vs Large', 'Clearly small intervals against clearly large ones. Wide contrasts are the easiest to judge — build confidence and anchor your inner sense of "small" and "large" before the close calls.',
                    'Geniş Kontrast: Küçüğe Karşı Büyük', 'Açıkça küçük aralıklar açıkça büyük olanlara karşı. Geniş kontrastları yargılamak en kolayıdır — ince ayrımlardan önce güven kazanın, içsel "küçük" ve "büyük" duygunuzu sabitleyin.',
                    ['wide-contrast'], ['comparison', 'confidence-building']],
                [5, 'beginner',  5, 'treble', [[$p['m3'], $p['M3']]],
                    'Minor 3rd vs Major 3rd', 'Your first one-semitone close call: the dark minor 3rd against the bright major 3rd. One focused pair, many roots, both orders — until the colour difference becomes a size judgement.',
                    'Küçük Üçlü ve Büyük Üçlü', 'İlk bir-yarım-ton ince ayrımınız: koyu küçük üçlüye karşı parlak büyük üçlü. Tek odaklı çift, birçok kök, iki sıra — renk farkı bir boyut yargısına dönüşene kadar.',
                    ['thirds', 'close-comparison'], ['comparison', 'fine-discrimination']],
                [6, 'intermediate', 8, 'treble', [[$p['P5'], $p['m6']], [$p['P5'], $p['M6']]],
                    '5th vs 6th', 'The stable perfect 5th against both sixths — one and two semitones above it. Learn where the open 5th ends and the warmer 6th region begins.',
                    'Beşli ve Altılı', 'Stabil tam beşli, her iki altılıya karşı — bir ve iki yarım ton üstünde. Açık beşlinin nerede bitip daha sıcak altılı bölgesinin nerede başladığını öğrenin.',
                    ['fifths', 'sixths'], ['comparison', 'interval-size']],
                [7, 'intermediate', 8, 'treble', [[$p['m6'], $p['M6']]],
                    'Minor 6th vs Major 6th', 'The notoriously tricky pair: minor 6th against major 6th, one semitone apart. A single focused contrast — no distractions — until this famous stumbling block is solid.',
                    'Küçük Altılı ve Büyük Altılı', 'Meşhur zorlu çift: küçük altılıya karşı büyük altılı, bir yarım ton fark. Tek odaklı kontrast — dikkat dağıtan hiçbir şey yok — bu ünlü takılma noktası sağlamlaşana kadar.',
                    ['sixths', 'close-comparison'], ['comparison', 'fine-discrimination']],
                [8, 'intermediate', 8, 'treble', [[$p['m2'], $p['M2']]],
                    'Half Step vs Whole Step', 'The most fundamental size discrimination in music: minor 2nd against major 2nd. The engine moves this single pair through every root, in both orders, until the judgement is instant.',
                    'Yarım Adım ve Tam Adım', 'Müzikteki en temel boyut ayrımı: küçük ikiliye karşı büyük ikili. Motor bu tek çifti her kökte ve iki sırada dolaştırır — yargı anlık hale gelene kadar.',
                    ['seconds', 'half-step', 'whole-step'], ['comparison', 'fine-discrimination']],
                [9, 'intermediate', 8, 'treble', [[$p['P4'], $p['TT']], [$p['TT'], $p['P5']]],
                    'The Tritone Zone', 'The restless tritone measured against both stable neighbours: perfect 4th below, perfect 5th above. Learn to place the tritone precisely between them by size alone.',
                    'Triton Bölgesi', 'Huzursuz triton, iki stabil komşusuna karşı: altında tam dörtlü, üstünde tam beşli. Tritonu yalnızca boyutuyla ikisinin tam ortasına yerleştirmeyi öğrenin.',
                    ['tritone', 'perfect-4th', 'perfect-5th', 'close-comparison'], ['comparison', 'fine-discrimination']],
                [10, 'intermediate', 8, 'treble', [[$p['m6'], $p['m7']], [$p['M6'], $p['M7']], [$p['M6'], $p['m7']], [$p['m6'], $p['M7']]],
                    '6th vs 7th', 'The category boundary in the upper register: every sixth against every seventh. The 7th is always larger and tenser — learn to hear that edge regardless of quality.',
                    'Altılı ve Yedili', 'Üst bölgedeki kategori sınırı: her altılı her yediliye karşı. Yedili her zaman daha büyük ve daha gergindir — kaliteden bağımsız olarak bu sınırı duymayı öğrenin.',
                    ['sixths', 'sevenths'], ['comparison', 'interval-size']],
                [11, 'advanced', 10, 'treble', [[$p['m7'], $p['M7']], [$p['M6'], $p['M7']], [$p['M6'], $p['m7']]],
                    'The Sevenths Zone: m7 vs M7', 'The subtlest region of the octave: minor 7th against major 7th — one semitone apart at the very top — anchored by the major 6th below them. Maximum-precision listening.',
                    'Yedililer Bölgesi: Küçük ve Büyük Yedili', 'Oktavın en ince bölgesi: en tepede bir yarım ton arayla küçük yediliye karşı büyük yedili — altlarındaki büyük altılıyla demirlenmiş. Maksimum hassasiyette dinleme.',
                    ['sevenths', 'close-comparison'], ['comparison', 'fine-discrimination']],
                [12, 'advanced', 10, 'treble', [[$p['m2'], $p['M2']], [$p['m3'], $p['M3']], [$p['P4'], $p['TT']], [$p['TT'], $p['P5']], [$p['m6'], $p['M6']], [$p['m7'], $p['M7']]],
                    'Semitone Apart: Full Sweep', 'Every one-semitone contrast in the octave, from seconds to sevenths, in one pool. The complete fine-discrimination test: you never know which register the close call lands in.',
                    'Yarım Ton Fark: Tam Tarama', 'Oktavdaki tüm bir-yarım-ton kontrastları, ikililerden yedililere, tek havuzda. Eksiksiz ince ayrım testi: ince ayrımın hangi bölgeye düşeceğini asla bilemezsiniz.',
                    ['chromatic', 'close-comparison', 'comprehensive'], ['comparison', 'fine-discrimination']],
                [13, 'advanced', 10, 'treble', [[$p['m2'], $p['m3']], [$p['M2'], $p['M3']], [$p['m3'], $p['P4']], [$p['M3'], $p['TT']], [$p['P4'], $p['P5']], [$p['TT'], $p['m6']], [$p['P5'], $p['M6']], [$p['m6'], $p['m7']], [$p['M6'], $p['M7']]],
                    'One Tone Apart: Full Sweep', 'Every whole-tone contrast in the octave, from the bottom to the top of the range. Slightly wider than a semitone — but spread across every register, so nothing can be judged by habit.',
                    'Tam Ton Fark: Tam Tarama', 'Oktavdaki tüm bir-tam-ton kontrastları, aralığın en altından en üstüne. Yarım tondan biraz daha geniş — ama her bölgeye yayılmış; hiçbir şey alışkanlıkla yargılanamaz.',
                    ['whole-tone', 'close-comparison', 'comprehensive'], ['comparison', 'fine-discrimination']],
                [14, 'advanced', 10, 'treble', PracticeIntervalComparison::buildPairsFromPool(['m2', 'm3', 'P4', 'P5', 'M6', 'M7']),
                    'Speed Drill: Rapid Size Judgement', 'Every pairing from a six-interval pool — contrasts from huge to subtle, shuffled without warning. Train fast, automatic size judgement across the whole octave.',
                    'Hız Antrenmanı: Hızlı Boyut Yargısı', 'Altı aralıklık havuzdan tüm eşleşmeler — devasa kontrastlardan incelere, uyarısız karışık. Tüm oktav boyunca hızlı, otomatik boyut yargısı geliştirin.',
                    ['speed', 'mixed'], ['comparison', 'speed-training']],
                [15, 'advanced', 10, 'bass', PracticeIntervalComparison::buildPairsFromPool(array_keys($p)),
                    'Master: Any Two Intervals (Bass Clef)', 'The capstone: every possible pairing from the full chromatic pool (m2–M7), written and heard in the bass clef\'s low register. True comparison mastery is register-independent.',
                    'Usta: Herhangi İki Aralık (Bas Anahtarı)', 'Doruk noktası: tam kromatik havuzdan (k2–B7) mümkün olan her eşleşme, bas anahtarının pes bölgesinde yazılır ve duyulur. Gerçek karşılaştırma ustalığı registerden bağımsızdır.',
                    ['master', 'comprehensive', 'bass-clef'], ['comparison', 'mastery', 'register-independence']],
            ];
            foreach ($compLessons as $l) {
                $data[] = $this->makeComparison('interval-comparison', $catId, $l);
            }
        }

        // ── SCALES & MODES ───────────────────────────────────────────────────
        // Focused curriculum following the overhauled interval tracks: every
        // lesson teaches ONE core scale structure in all its dimensions, with
        // the answer pool built from that structure's nearest confusions.
        // Configs follow the Exercise Setup Studio rules — canonical
        // ScalePractice::scaleIntervals() names (lowercase slugs silently fall
        // back to Major intervals!), clef-driven pitch placement (CLEF_RANGES,
        // no hardcoded octaves), direction 'both' mixes ascending/descending.
        // Acoustic twins (Aeolian = Natural Minor, Ionian = Major) are never
        // placed in the same answer pool — one label per sound per lesson.
        $catId = $categories['scales-modes']->id ?? null;
        if ($catId) {
            $scaleLessons = [
                [1, 'beginner',  5,  ['Major', 'Natural Minor'], ['C', 'G', 'F', 'D'], 'ascending', 'treble',
                    ['Major', 'Natural Minor', 'Harmonic Minor', 'Major Pentatonic'],
                    'The Major Scale', 'Learn the major scale inside out — its bright, confident character comes from the raised 3rd, 6th and 7th degrees. Every question sets it against its dark mirror, the natural minor.',
                    'Majör Gam', 'Majör gamı derinlemesine öğrenin — parlak, kendinden emin karakteri yükseltilmiş 3., 6. ve 7. derecelerden gelir. Her soru onu koyu aynası doğal minörle karşı karşıya getirir.',
                    ['major-scale', 'foundation'], ['scale-recognition']],
                [2, 'beginner',  5,  ['Natural Minor', 'Major'], ['A', 'E', 'D', 'B'], 'ascending', 'treble',
                    ['Natural Minor', 'Major', 'Harmonic Minor', 'Minor Pentatonic'],
                    'The Natural Minor Scale', 'The natural minor scale in depth — the lowered 3rd, 6th and 7th degrees give it its melancholic colour. Anchor its sound against the major scale until the contrast is automatic.',
                    'Doğal Minör Gam', 'Doğal minör gam derinlemesine — düşürülmüş 3., 6. ve 7. dereceler ona melankolik rengini verir. Kontrast otomatikleşene dek sesini majör gama karşı sabitleyin.',
                    ['natural-minor', 'foundation'], ['scale-recognition']],
                [3, 'beginner',  5,  ['Harmonic Minor', 'Natural Minor'], ['A', 'D', 'E'], 'ascending', 'treble',
                    ['Harmonic Minor', 'Natural Minor', 'Melodic Minor', 'Major'],
                    'The Harmonic Minor Scale', 'The harmonic minor raises the 7th degree, creating an exotic augmented 2nd between the 6th and 7th. Learn to catch that signature leap against the plain natural minor.',
                    'Harmonik Minör Gam', 'Harmonik minör 7. dereceyi yükseltir ve 6. ile 7. arasında egzotik bir artık ikili oluşturur. Bu imza sıçramayı sade doğal minöre karşı yakalamayı öğrenin.',
                    ['harmonic-minor'], ['scale-recognition']],
                [4, 'beginner',  5,  ['Melodic Minor', 'Harmonic Minor', 'Natural Minor'], ['A', 'D', 'G'], 'ascending', 'treble',
                    ['Melodic Minor', 'Harmonic Minor', 'Natural Minor', 'Major'],
                    'The Melodic Minor Scale', 'The melodic minor raises both the 6th and 7th degrees — smoother than harmonic minor, one shade darker than major. Distinguish it from both of its minor siblings.',
                    'Melodik Minör Gam', 'Melodik minör hem 6. hem 7. dereceyi yükseltir — harmonik minörden akıcı, majörden bir ton koyu. Onu iki minör kardeşinden de ayırt edin.',
                    ['melodic-minor'], ['scale-recognition']],
                [5, 'beginner',  5,  ['Major', 'Natural Minor', 'Harmonic Minor', 'Melodic Minor'], ['C', 'D', 'E', 'G', 'A'], 'ascending', 'treble',
                    ['Major', 'Natural Minor', 'Harmonic Minor', 'Melodic Minor'],
                    'Synthesis: Major & the Three Minors', 'The beginner capstone — all four foundation scales in one pool. Major against every minor variant; the subtle 6th- and 7th-degree differences demand deep listening.',
                    'Sentez: Majör ve Üç Minör', 'Başlangıç seviyesinin doruğu — dört temel gam tek havuzda. Majöre karşı tüm minör varyantlar; 6. ve 7. derecelerdeki ince farklar derin dinleme ister.',
                    ['synthesis', 'minor-forms'], ['scale-recognition', 'discrimination']],
                [6, 'intermediate', 8, ['Dorian', 'Natural Minor'], ['D', 'G', 'A'], 'ascending', 'treble',
                    ['Dorian', 'Natural Minor', 'Mixolydian', 'Phrygian'],
                    'Dorian Mode', 'Dorian is the natural minor with a raised 6th — the "cool minor" of jazz and rock, from "So What" to "Scarborough Fair". Every question tests that single degree against plain minor.',
                    'Dorian Modu', 'Dorian, 6. derecesi yükseltilmiş doğal minördür — cazın ve rock\'ın "serin minörü". Her soru bu tek dereceyi sade minöre karşı sınar.',
                    ['dorian', 'modes'], ['scale-recognition', 'modal']],
                [7, 'intermediate', 8, ['Phrygian', 'Natural Minor'], ['E', 'A', 'B'], 'ascending', 'treble',
                    ['Phrygian', 'Natural Minor', 'Dorian', 'Locrian'],
                    'Phrygian Mode', 'Phrygian darkens the natural minor with a lowered 2nd — the Spanish-flamenco colour that opens the scale with a half step. Learn to catch that first-step signature instantly.',
                    'Frig Modu', 'Frig, doğal minörü düşürülmüş 2. dereceyle koyulaştırır — gamı yarım adımla açan İspanyol-flamenko rengi. Bu ilk-adım imzasını anında yakalamayı öğrenin.',
                    ['phrygian', 'modes'], ['scale-recognition', 'modal']],
                [8, 'intermediate', 8, ['Lydian', 'Major'], ['F', 'C', 'G'], 'ascending', 'treble',
                    ['Lydian', 'Major', 'Mixolydian', 'Whole Tone Scale'],
                    'Lydian Mode', 'Lydian is the major scale with a raised 4th — the dreamy, floating sound of film scores. A single degree separates it from plain major; train your ear to hear exactly that.',
                    'Lidya Modu', 'Lidya, 4. derecesi yükseltilmiş majör gamdır — film müziklerinin rüya gibi, süzülen sesi. Onu sade majörden tek derece ayırır; kulağınızı tam bunu duymaya eğitin.',
                    ['lydian', 'modes'], ['scale-recognition', 'modal']],
                [9, 'intermediate', 8, ['Mixolydian', 'Major'], ['G', 'D', 'C'], 'ascending', 'treble',
                    ['Mixolydian', 'Major', 'Lydian', 'Dorian'],
                    'Mixolydian Mode', 'Mixolydian is the major scale with a lowered 7th — the bluesy major of rock, blues and folk. Spot the softened leading tone against the true major scale.',
                    'Miksolidya Modu', 'Miksolidya, 7. derecesi düşürülmüş majör gamdır — rock, blues ve folk\'un "bluesy majörü". Yumuşatılmış yeden sesini gerçek majöre karşı fark edin.',
                    ['mixolydian', 'modes'], ['scale-recognition', 'modal']],
                [10, 'intermediate', 8, ['Aeolian', 'Locrian'], ['B', 'E', 'A'], 'ascending', 'treble',
                    ['Aeolian', 'Locrian', 'Phrygian', 'Dorian'],
                    'Aeolian & Locrian', 'The two darkest modes. Aeolian is the modal name for the natural minor; Locrian lowers the 2nd AND the 5th — the only mode with a diminished tonic triad. Answer options stay strictly modal.',
                    'Aeolian ve Lokriyen', 'En koyu iki mod. Aeolian, doğal minörün modal adıdır; Lokriyen hem 2. hem 5. dereceyi düşürür — tonik üçlüsü eksili olan tek mod. Cevap seçenekleri tamamen modal kalır.',
                    ['aeolian', 'locrian', 'modes'], ['scale-recognition', 'modal']],
                [11, 'advanced', 10, ['Ionian', 'Dorian', 'Phrygian', 'Lydian', 'Mixolydian', 'Aeolian', 'Locrian'], ['C', 'D', 'E', 'F', 'G', 'A'], 'both', 'treble',
                    ['Ionian', 'Dorian', 'Phrygian', 'Lydian', 'Mixolydian', 'Aeolian', 'Locrian'],
                    'All Seven Church Modes', 'The complete modal system in one pool — Ionian through Locrian, heard ascending and descending. Purely modal answer options; total fluency across all seven colours.',
                    'Yedi Kilise Modu', 'Tam modal sistem tek havuzda — Ionian\'dan Lokriyen\'e, çıkıcı ve inici duyulur. Cevap seçenekleri tamamen modal; yedi rengin hepsinde tam akıcılık.',
                    ['modes', 'comprehensive'], ['scale-recognition', 'modal-mastery']],
                [12, 'advanced', 10, ['Major Pentatonic', 'Minor Pentatonic'], ['C', 'G', 'D', 'A', 'E'], 'both', 'treble',
                    ['Major Pentatonic', 'Minor Pentatonic', 'Blues Scale', 'Major'],
                    'Pentatonic Scales', 'Five-note scales that power folk and pop worldwide. Major versus minor pentatonic — the same open sound, opposite emotional poles — heard rising and falling.',
                    'Pentatonik Gamlar', 'Dünya genelinde folk ve pop\'a güç veren beş sesli gamlar. Majör pentatoniğe karşı minör pentatonik — aynı açık ses, zıt duygusal kutuplar — çıkıcı ve inici duyulur.',
                    ['pentatonic'], ['scale-recognition', 'popular-music']],
                [13, 'advanced', 10, ['Blues Scale', 'Minor Pentatonic'], ['A', 'E', 'G', 'C'], 'both', 'treble',
                    ['Blues Scale', 'Minor Pentatonic', 'Major Pentatonic', 'Natural Minor'],
                    'The Blues Scale', 'The blues scale is the minor pentatonic plus one note — the flat-5 "blue note". Every question hinges on hearing whether that extra chromatic sting is present.',
                    'Blues Gamı', 'Blues gamı, minör pentatonik artı tek bir notadır — bemol 5, yani "blue note". Her soru, bu ekstra kromatik iğnenin duyulup duyulmadığına bağlıdır.',
                    ['blues', 'pentatonic'], ['scale-recognition', 'popular-music']],
                [14, 'advanced', 10, ['Chromatic Scale', 'Whole Tone Scale'], ['C', 'D', 'E', 'F', 'G'], 'both', 'treble',
                    ['Chromatic Scale', 'Whole Tone Scale', 'Blues Scale', 'Melodic Minor'],
                    'Symmetric Scales: Chromatic & Whole Tone', 'Scales with no tonal centre — the chromatic scale (all half steps) and the whole tone scale (all whole steps). Impressionist, dreamlike colours that break every diatonic rule.',
                    'Simetrik Gamlar: Kromatik ve Tam Ton', 'Tonal merkezi olmayan gamlar — kromatik gam (tümü yarım adım) ve tam ton gamı (tümü tam adım). Tüm diyatonik kuralları kıran empresyonist, rüyamsı renkler.',
                    ['chromatic', 'whole-tone', 'symmetric'], ['scale-recognition']],
                [15, 'advanced', 10, ['Major', 'Natural Minor', 'Harmonic Minor', 'Melodic Minor', 'Dorian', 'Phrygian', 'Lydian', 'Mixolydian', 'Locrian', 'Major Pentatonic', 'Minor Pentatonic', 'Blues Scale', 'Chromatic Scale', 'Whole Tone Scale'], ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'both', 'bass',
                    ['Major', 'Natural Minor', 'Harmonic Minor', 'Melodic Minor', 'Dorian', 'Phrygian', 'Lydian', 'Mixolydian', 'Locrian', 'Major Pentatonic', 'Minor Pentatonic', 'Blues Scale', 'Chromatic Scale', 'Whole Tone Scale'],
                    'Master: All Scales, Bass Clef', 'The capstone: fourteen scale types from any natural root, ascending or descending, heard in the bass clef\'s low register (C2–C4). True scale mastery is register-independent.',
                    'Usta: Tüm Gamlar, Bas Anahtarı', 'Doruk noktası: on dört gam türü, herhangi bir doğal kök, çıkıcı veya inici, bas anahtarının pes bölgesinde (C2–C4) duyulur. Gerçek gam ustalığı registerden bağımsızdır.',
                    ['master', 'all-scales', 'bass-clef'], ['scale-recognition', 'mastery', 'register-independence']],
            ];
            foreach ($scaleLessons as $l) {
                $data[] = $this->makeScale('scales-modes', $catId, $l);
            }
        }

        // ── CHORDS ───────────────────────────────────────────────────────────
        $catId = $categories['chords']->id ?? null;
        if ($catId) {
            // Tuple: [sort, level, duration, types, roots, voicing, inversions (bool|array), distractor_pool, clef, EN, TR, tags, skills]
            // Chord type names must be canonical ChordPractice::chordIntervals()
            // keys. Each lesson teaches ONE structure against its closest
            // confusions; distractor_pool must leave >= 3 wrong options for
            // every possible correct answer (blade shows 4 buttons).
            $chordLessons = [
                [1, 'beginner',  5,  ['Major', 'Minor'], ['C', 'D', 'E', 'F', 'G', 'A'], 'block', false, ['Major', 'Minor', 'Augmented', 'Diminished'], 'treble',
                    'Major vs Minor', 'The most fundamental quality distinction in music: only the middle note changes. Train yourself to identify bright vs dark instantly, from any root.',
                    'Majör vs Minör', 'Müzikteki en temel kalite ayrımı: yalnızca ortadaki nota değişir. Parlak ve koyu kaliteyi herhangi bir kökten anında tanımayı öğrenin.',
                    ['major', 'minor', 'comparison'], ['chord-recognition', 'quality-distinction']],
                [2, 'beginner',  5,  ['Diminished', 'Augmented'], ['C', 'D', 'E', 'F', 'G', 'A', 'B'], 'block', false, ['Major', 'Minor', 'Diminished', 'Augmented'], 'treble',
                    'Diminished & Augmented', 'The two tense triads side by side: the diminished (two stacked minor 3rds — dark, wanting to resolve) and the augmented (two stacked major 3rds — wide and unsettling). Tell the two dissonant colors apart, and from major and minor.',
                    'Eksik & Artık Beşli', 'İki gergin üçlü yan yana: eksik beşli (iki üst üste küçük 3\'lü — koyu, çözülmek isteyen) ve artık beşli (iki üst üste büyük 3\'lü — geniş, tekinsiz). İki disonan rengi birbirinden, ayrıca majör ve minörden ayırt edin.',
                    ['diminished', 'augmented', 'dissonance'], ['chord-recognition', 'quality-distinction']],
                [3, 'intermediate', 8, ['Major', 'Minor', 'Diminished', 'Augmented'], ['C', 'D', 'E', 'F', 'G', 'A'], 'block', false, ['Major', 'Minor', 'Diminished', 'Augmented'], 'treble',
                    'The Four Triads', 'Synthesis: all four triad qualities from any root. Major, minor, diminished, augmented — the complete basic vocabulary of tonal harmony in one exercise.',
                    'Dört Üçlü Kalite', 'Sentez: dört üçlü kalite, herhangi bir kökten. Majör, minör, eksik beşli, artık beşli — tonal armoninin tüm temel söz dağarcığı tek egzersizde.',
                    ['triads', 'comprehensive'], ['chord-recognition', 'quality-discrimination']],
                [4, 'intermediate', 8, ['Major', 'Minor', 'Diminished', 'Augmented'], ['C', 'D', 'E', 'G', 'A'], 'arpeggiated', false, ['Major', 'Minor', 'Diminished', 'Augmented'], 'treble',
                    'Arpeggiated Triads', 'The same four triad qualities, now played one note at a time. Arpeggiation exposes each interval in sequence — hear the quality through the melodic line.',
                    'Arpejli Üçlüler', 'Aynı dört üçlü kalite, bu kez tek tek çalınıyor. Arpej her aralığı sırayla duyurur — kaliteyi melodik çizgi üzerinden duyun.',
                    ['arpeggiated', 'triads'], ['chord-recognition']],
                [5, 'intermediate', 8, ['Sus2', 'Sus4'], ['C', 'D', 'F', 'G', 'A'], 'block', false, ['Sus2', 'Sus4', 'Major', 'Minor'], 'treble',
                    'Sus2 & Sus4 Chords', 'Suspended chords replace the 3rd with a 2nd or 4th — open, floating, neither major nor minor. Learn to hear which neighbor tone replaced the 3rd.',
                    'Sus2 ve Sus4 Akorlar', 'Sus akorları 3\'lünün yerine 2\'li veya 4\'lü koyar — açık, askıda, ne majör ne minör. 3\'lünün yerini hangi komşu notanın aldığını duymayı öğrenin.',
                    ['sus-chords', 'suspension'], ['chord-recognition', 'color-chords']],
                [6, 'intermediate', 8, ['Add9', 'Minor Add9'], ['C', 'D', 'F', 'G'], 'block', false, ['Add9', 'Minor Add9', 'Major', 'Sus2'], 'treble',
                    'Add9 Color Chords', 'A triad with the 9th added on top — bright, open and shimmering, without the pull of a 7th. Hear how the added 9th recolors both the major and the minor triad.',
                    'Add9 Renk Akorları', 'Üçlüye tepesinden 9\'lu eklenmiş hali — 7\'linin çekimi olmadan parlak, açık ve ışıltılı. Eklenen 9\'lunun hem majör hem minör üçlüyü nasıl yeniden renklendirdiğini duyun.',
                    ['add9', 'color-chords'], ['chord-recognition', 'color-chords']],
                [7, 'intermediate', 8, ['Dominant 7th', 'Major 7th'], ['C', 'F', 'G', 'D', 'A'], 'block', false, ['Dominant 7th', 'Major 7th', 'Minor 7th', 'Major'], 'treble',
                    'Dominant 7th & Major 7th', 'The two bright 7th colors on a major triad: the restless dominant 7th (minor 7th on top — the engine of tonal tension) against the lush, dreamy major 7th. Tell the tension apart from the shimmer.',
                    'Dominant 7\'li & Majör 7\'li', 'Majör üçlü üzerindeki iki parlak 7\'li renk: huzursuz dominant 7\'li (tepede küçük 7\'li — tonal gerilimin motoru) ile zengin, rüyamsı majör 7\'li. Gerilimi ışıltıdan ayırt edin.',
                    ['dominant-7th', 'major-7th', 'seventh-chords'], ['chord-recognition', 'seventh-chords']],
                [8, 'intermediate', 8, ['Minor 7th', 'Half-Diminished 7th'], ['C', 'D', 'F', 'G', 'A'], 'block', false, ['Minor 7th', 'Half-Diminished 7th', 'Diminished 7th', 'Minor'], 'treble',
                    'Minor 7th & Half-Diminished 7th', 'The two dark 7th colors: the mellow minor 7th against the tense half-diminished 7th (minor 7th with a flattened 5th — the ii° of minor keys). Only the fifth changes; learn to hear it drop.',
                    'Minör 7\'li & Yarı Eksik 7\'li', 'İki koyu 7\'li renk: tok minör 7\'liye karşı gergin yarı eksik 7\'li (5\'lisi pesleştirilmiş minör 7\'li — minör tonların ii° akoru). Yalnızca beşli değişir; onun düşüşünü duymayı öğrenin.',
                    ['minor-7th', 'half-diminished-7th', 'jazz'], ['chord-recognition', 'seventh-chords']],
                [9, 'advanced', 10, ['Dominant 7th', 'Major 7th', 'Minor 7th', 'Half-Diminished 7th'], ['C', 'D', 'G', 'A'], 'block', false, ['Dominant 7th', 'Major 7th', 'Minor 7th', 'Half-Diminished 7th'], 'treble',
                    'The Four 7th Chords', 'Synthesis: dominant, major, minor, and half-diminished 7ths from any root. The complete essential 7th chord vocabulary of jazz and classical harmony.',
                    'Dört 7\'li Akor', 'Sentez: dominant, majör, minör ve yarım eksik 7\'liler, herhangi bir kökten. Caz ve klasik armoninin eksiksiz temel 7\'li söz dağarcığı.',
                    ['seventh-chords', 'comprehensive'], ['chord-recognition', 'jazz-harmony']],
                [10, 'advanced', 10, ['Dominant 7th', 'Major 7th', 'Minor 7th', 'Half-Diminished 7th'], ['C', 'D', 'F', 'G', 'A'], 'arpeggiated', false, ['Dominant 7th', 'Major 7th', 'Minor 7th', 'Half-Diminished 7th'], 'treble',
                    'Arpeggiated 7th Chords', 'The four 7th qualities, unrolled one note at a time. Arpeggiation lays each chord tone bare in sequence — track the 3rd, 5th and 7th as a melodic line and name the quality.',
                    'Arpejli 7\'li Akorlar', 'Dört 7\'li kalite, tek tek açılarak çalınıyor. Arpej her akor sesini sırayla açığa çıkarır — 3\'lü, 5\'li ve 7\'liyi melodik bir çizgi olarak izleyip kaliteyi adlandırın.',
                    ['arpeggiated', 'seventh-chords'], ['chord-recognition', 'seventh-chords']],
                [11, 'advanced', 10, ['Diminished', 'Half-Diminished 7th', 'Diminished 7th'], ['B', 'C', 'D', 'E'], 'block', false, ['Diminished', 'Half-Diminished 7th', 'Diminished 7th', 'Minor 7th'], 'treble',
                    'The Diminished Family', 'Three shades of diminished: the bare triad, the half-diminished 7th, and the fully diminished 7th. Learn to hear exactly how far the darkness goes.',
                    'Eksik Beşli Ailesi', 'Eksik beşlinin üç tonu: yalın üçlü, yarım eksik 7\'li ve tam eksik 7\'li. Koyuluğun tam olarak nereye kadar gittiğini duymayı öğrenin.',
                    ['diminished-family', 'seventh-chords'], ['chord-recognition', 'advanced-harmony']],
                [12, 'advanced', 10, ['Major 6th', 'Minor 6th'], ['C', 'D', 'F', 'G'], 'block', false, ['Major 6th', 'Minor 6th', 'Major 7th', 'Dominant 7th', 'Minor 7th'], 'treble',
                    '6th Chords', 'The 6th replaces the 7th with a consonant added tone — the classic swing-era and bossa color. Distinguish major and minor 6ths from the 7th chords they resemble.',
                    '6\'lı Akorlar', '6\'lı, 7\'linin yerine konsonan bir ek ses getirir — swing dönemi ve bossanın klasik rengi. Majör ve minör 6\'lıları benzedikleri 7\'li akorlardan ayırt edin.',
                    ['sixth-chords', 'color-chords'], ['chord-recognition', 'color-chords']],
                [13, 'advanced', 10, ['Major', 'Minor'], ['C', 'D', 'E', 'F', 'G'], 'block', [1], ['Major', 'Minor', 'Augmented', 'Diminished'], 'treble',
                    'First Inversion Triads', 'Major and minor triads with the 3rd in the bass — every question is in first inversion. Recognize chord quality when the root is no longer the lowest note.',
                    'Birinci Çevrim Üçlüler', 'Basta 3\'lü olan majör ve minör üçlüler — her soru birinci çevrimdedir. Kök artık en pes ses değilken akor kalitesini tanıyın.',
                    ['inversions', 'first-inversion'], ['chord-recognition', 'voice-leading']],
                [14, 'advanced', 10, ['Major', 'Minor'], ['C', 'D', 'E', 'F', 'G'], 'block', [2], ['Major', 'Minor', 'Augmented', 'Diminished'], 'treble',
                    'Second Inversion Triads', 'Major and minor triads with the 5th in the bass — every question is in second inversion. The most unstable voicing; recognize quality when the perfect 5th is the lowest note.',
                    'İkinci Çevrim Üçlüler', 'Basta 5\'li olan majör ve minör üçlüler — her soru ikinci çevrimdedir. En kararsız dizilim; tam 5\'li en pes sesken kaliteyi tanıyın.',
                    ['inversions', 'second-inversion'], ['chord-recognition', 'voice-leading']],
                [15, 'advanced', 10, ['Major', 'Minor', 'Diminished', 'Augmented', 'Sus2', 'Sus4', 'Dominant 7th', 'Major 7th', 'Minor 7th', 'Half-Diminished 7th', 'Diminished 7th', 'Major 6th', 'Minor 6th'], ['C', 'C#', 'D', 'Eb', 'E', 'F', 'F#', 'G', 'A', 'Bb'], 'block', [0, 1, 2], [], 'bass',
                    'Master: All Chords, Bass Clef', 'The ultimate challenge — the full chord vocabulary of this path, any root, any inversion, in the low bass register. Professional-level harmonic hearing.',
                    'Usta: Tüm Akorlar, Fa Anahtarı', 'Nihai sınav — bu yolun tüm akor dağarcığı, herhangi bir kök, herhangi bir çevrim, pes bas bölgesinde. Profesyonel seviye armonik işitme.',
                    ['master', 'comprehensive', 'bass-clef'], ['chord-recognition', 'mastery', 'register-independence']],
            ];
            foreach ($chordLessons as $l) {
                $data[] = $this->makeChord('chords', $catId, $l);
            }
        }

        // ── RHYTHM ───────────────────────────────────────────────────────────
        $catId = $categories['rhythm']->id ?? null;
        if ($catId) {
            // Each lesson teaches ONE core rhythmic structure in depth, shown across
            // every meter where it naturally lives (multi-time-signature lessons mix
            // per-question meters). Studio generation rules apply: beat-aligned cells,
            // level-matched near-miss distractors (easy/medium/hard), exclude_cells to
            // keep out-of-scope figures (e.g. syncopation) away from focused lessons.
            // Row: [sort, level, minutes, timeSigs, noteValues, tempo, bars, excludeCells,
            //       titleEn, descEn, titleTr, descTr, tags, skills]
            $noSync = ['eighth,quarter,eighth'];
            $rhythmLessons = [
                [1, 'beginner',  5, ['2/4', '3/4', '4/4'], ['quarter', 'quarter_rest'], 80, 2, [],
                    'The Pulse: Quarter Notes & Rests', 'The quarter note IS the beat. Two-bar patterns of quarter notes and quarter rests in 2/4, 3/4 and 4/4 — count the pulse and catch every silence.',
                    'Nabız: Dörtlük Nota ve Sus', 'Dörtlük nota vuruşun kendisidir. 2/4, 3/4 ve 4/4\'te dörtlük nota ve suslardan oluşan iki ölçülük kalıplar — nabzı sayın ve her sessizliği yakalayın.',
                    ['quarter-notes', 'pulse', 'rests'], ['rhythm-recognition', 'pulse']],
                [2, 'beginner',  5, ['2/4', '3/4', '4/4'], ['quarter', 'half'], 76, 2, [],
                    'Half Notes: The Two-Beat Note', 'One sound, two beats. Hear where the half note falls among quarter notes — in 2/4, 3/4 and 4/4 — and feel duration against the pulse.',
                    'Yarım Nota: İki Vuruşluk Ses', 'Tek ses, iki vuruş. 2/4, 3/4 ve 4/4\'te yarım notanın dörtlükler arasında nereye düştüğünü duyun; süreyi nabza karşı hissedin.',
                    ['half-notes', 'duration'], ['rhythm-recognition', 'duration']],
                [3, 'beginner',  5, ['4/4'], ['whole', 'half', 'quarter', 'half_rest'], 76, 2, [],
                    'Whole Notes & Long Silences', 'The whole note fills the bar; the half rest empties half of it. Two-bar phrases that train you to keep counting through long sounds and long silences.',
                    'Tam Nota ve Uzun Suslar', 'Tam nota ölçüyü doldurur; yarım sus yarısını boşaltır. Uzun sesler ve uzun sessizlikler boyunca saymayı sürdürmeyi öğreten iki ölçülük cümleler.',
                    ['whole-notes', 'long-durations', 'rests'], ['rhythm-recognition', 'long-durations']],
                [4, 'beginner',  5, ['3/4'], ['quarter', 'half', 'dotted-half', 'quarter_rest'], 88, 2, [],
                    'The Waltz Bar: 3/4 & Dotted Half', 'Three beats per bar — the waltz. The dotted half fills the whole measure; halves, quarters and rests carve it up. Two-bar waltz phrases.',
                    'Vals Ölçüsü: 3/4 ve Noktalı Yarım', 'Ölçü başına üç vuruş — vals. Noktalı yarım tüm ölçüyü doldurur; yarımlar, dörtlükler ve suslar onu böler. İki ölçülük vals cümleleri.',
                    ['3/4', 'waltz', 'dotted-half'], ['rhythm-recognition', 'meter']],
                [5, 'beginner',  5, ['2/4', '4/4'], ['quarter', 'eighth'], 80, 1, $noSync,
                    'Eighth Notes: Splitting the Beat', 'Eighth notes divide each beat in two — "1-and-2-and". Learn to hear exactly which beats are split and which stay whole, in 2/4 and 4/4.',
                    'Sekizlikler: Vuruşu İkiye Bölmek', 'Sekizlikler her vuruşu ikiye böler — "1-ve-2-ve". 2/4 ve 4/4\'te hangi vuruşların bölündüğünü, hangilerinin bütün kaldığını tam olarak duymayı öğrenin.',
                    ['eighth-notes', 'subdivision'], ['rhythm-recognition', 'subdivision']],
                [6, 'intermediate', 8, ['4/4'], ['quarter', 'eighth', 'eighth_rest'], 80, 1, $noSync,
                    'Eighth Rests & the Off-Beat', 'A rest on the beat, a note off the beat — the figure behind funk, reggae and Latin grooves. Learn to place the sound on the "and".',
                    'Sekizlik Sus ve Off-Beat', 'Vuruşta sus, vuruş dışında nota — funk, reggae ve Latin groove\'larının temel figürü. Sesi "ve"nin üzerine yerleştirmeyi öğrenin.',
                    ['eighth-rest', 'off-beat'], ['rhythm-recognition', 'rests', 'off-beat']],
                [7, 'intermediate', 8, ['3/4', '4/4'], ['quarter', 'eighth', 'half', 'dotted-quarter'], 84, 1, $noSync,
                    'Dotted Quarter + Eighth: Long-Short', 'The dotted quarter borrows half of the next beat, making the classic "long-short" of anthems and marches. Hear it in 3/4 and 4/4.',
                    'Noktalı Dörtlük + Sekizlik: Uzun-Kısa', 'Noktalı dörtlük bir sonraki vuruşun yarısını ödünç alır ve marşların klasik "uzun-kısa" kalıbını oluşturur. 3/4 ve 4/4\'te duyun.',
                    ['dotted-quarter', 'long-short'], ['rhythm-recognition', 'dotted-rhythms']],
                [8, 'intermediate', 8, ['2/4', '4/4'], ['quarter', 'eighth', 'sixteenth'], 76, 1,
                    array_merge($noSync, ['eighth,sixteenth,sixteenth', 'sixteenth,sixteenth,eighth', 'sixteenth,eighth,sixteenth']),
                    'Sixteenth Notes: Four to a Beat', 'Sixteenths divide the beat into four — "1-e-and-a". Pure even subdivision: every beat is whole, split in two, or split in four.',
                    'On Altılıklar: Vuruşta Dört Nota', 'On altılıklar vuruşu dörde böler — "1-e-ve-a". Saf eşit alt bölünme: her vuruş ya bütündür, ya ikiye ya da dörde bölünür.',
                    ['sixteenth-notes', 'subdivision'], ['rhythm-recognition', 'fast-subdivision']],
                [9, 'intermediate', 8, ['4/4'], ['quarter', 'eighth', 'sixteenth'], 76, 1,
                    array_merge($noSync, ['sixteenth,sixteenth,sixteenth,sixteenth', 'sixteenth,eighth,sixteenth']),
                    'Eighth + Sixteenth Groups', 'The two asymmetric beat shapes: eighth + two sixteenths ("1-and-a") and two sixteenths + eighth ("1-e-and"). Learn to tell them apart instantly.',
                    'Sekizlik + On Altılık Grupları', 'İki asimetrik vuruş şekli: sekizlik + iki on altılık ("1-ve-a") ve iki on altılık + sekizlik ("1-e-ve"). İkisini anında ayırt etmeyi öğrenin.',
                    ['eighth-sixteenth', 'subdivision-groups'], ['rhythm-recognition', 'fast-subdivision']],
                [10, 'intermediate', 8, ['2/4', '4/4'], ['quarter', 'eighth', 'dotted-eighth', 'sixteenth'], 80, 1,
                    array_merge($noSync, ['eighth,sixteenth,sixteenth', 'sixteenth,sixteenth,eighth', 'sixteenth,eighth,sixteenth', 'sixteenth,sixteenth,sixteenth,sixteenth']),
                    'Dotted Eighth + Sixteenth: The Snap', 'The sharpest long-short inside one beat — the galloping snap of marches and folk dance, forwards (long-short) and reversed (short-long, the Scotch snap).',
                    'Noktalı Sekizlik + On Altılık', 'Tek vuruş içindeki en keskin uzun-kısa — marşların ve halk danslarının "gallop" kalıbı; hem düz (uzun-kısa) hem ters (kısa-uzun, Scotch snap) haliyle.',
                    ['dotted-eighth', 'snap-rhythm'], ['rhythm-recognition', 'dotted-rhythms']],
                [11, 'advanced', 10, ['4/4'], ['quarter', 'eighth', 'half', 'eighth_rest'], 80, 1, [],
                    'Syncopation: Off-Beat Accents', 'The eighth–quarter–eighth figure and off-beat rests shift the weight between the beats — the driving energy of jazz, funk and Latin music.',
                    'Senkop: Zayıf Vuruş Aksanı', 'Sekizlik–dörtlük–sekizlik figürü ve off-beat suslar ağırlığı vuruşların arasına kaydırır — caz, funk ve Latin müziğinin itici enerjisi.',
                    ['syncopation', 'off-beat'], ['rhythm-recognition', 'groove']],
                [12, 'advanced', 10, ['6/8'], ['dotted-half', 'dotted-quarter', 'quarter', 'eighth'], 112, 1, [],
                    'Compound Meter: 6/8', 'In 6/8 the beat is a dotted quarter that splits into three. Learn every basic shape of the compound beat: whole, quarter+eighth, eighth+quarter, three eighths.',
                    'Bileşik Ölçü: 6/8', '6/8\'de vuruş, üçe bölünen noktalı dörtlüktür. Bileşik vuruşun tüm temel şekillerini öğrenin: bütün, dörtlük+sekizlik, sekizlik+dörtlük, üç sekizlik.',
                    ['6/8', 'compound-meter'], ['rhythm-recognition', 'compound-meter']],
                [13, 'advanced', 10, ['6/8', '9/8'], ['dotted-quarter', 'quarter', 'eighth', 'dotted-eighth', 'sixteenth'], 116, 1, [],
                    'Compound Subdivision: 6/8 & 9/8', 'Sixteenths and dotted eighths inside the compound beat, in both 6/8 and 9/8. The full inner life of the dotted-quarter pulse.',
                    'Bileşik Alt Bölünme: 6/8 ve 9/8', '6/8 ve 9/8\'de bileşik vuruşun içindeki on altılıklar ve noktalı sekizlikler. Noktalı dörtlük nabzının tüm iç dünyası.',
                    ['compound-subdivision', '9/8'], ['rhythm-recognition', 'compound-meter', 'fast-subdivision']],
                [14, 'advanced', 10, ['4/4'], ['quarter', 'eighth', 'half', 'triplet-eighth'], 80, 1, $noSync,
                    'Triplets: Three in the Time of Two', 'The eighth-note triplet squeezes three even notes into one beat. Hear the "tri-pl-et" roll against straight eighths and quarters.',
                    'Triole: İki Yerine Üç', 'Sekizlik triole tek vuruşa üç eşit nota sığdırır. "Üç-le-me" yuvarlanışını düz sekizlik ve dörtlüklere karşı duyun.',
                    ['triplets', 'tuplets'], ['rhythm-recognition', 'triplets']],
                [15, 'advanced', 10, ['2/2'], ['whole', 'half', 'dotted-half', 'quarter', 'eighth'], 60, 1, $noSync,
                    'Alla Breve: Cut Time (2/2)', 'In cut time the HALF note carries the beat — two big beats per bar. The same values you know, felt at a different level of the pulse.',
                    'Alla Breve: Kesik Ölçü (2/2)', 'Kesik ölçüde vuruşu YARIM nota taşır — ölçü başına iki büyük vuruş. Bildiğiniz değerler, nabzın farklı bir katında hissedilir.',
                    ['alla-breve', '2/2'], ['rhythm-recognition', 'meter']],
                [16, 'advanced', 10, ['4/4', '6/8'], ['whole', 'half', 'dotted-half', 'quarter', 'dotted-quarter', 'eighth', 'dotted-eighth', 'sixteenth', 'quarter_rest', 'eighth_rest'], 80, 2, [],
                    'Master: Two-Bar Phrases', 'Everything combined: all note values, dotted figures, rests and syncopation in two-bar phrases across 4/4 and 6/8. The complete rhythm recognition challenge.',
                    'Usta: İki Ölçülük Cümleler', 'Her şey bir arada: 4/4 ve 6/8\'de iki ölçülük cümlelerde tüm nota değerleri, noktalı figürler, suslar ve senkop. Eksiksiz ritim tanıma sınavı.',
                    ['master', 'all-note-values', 'two-bar'], ['rhythm-recognition', 'mastery']],
            ];
            foreach ($rhythmLessons as $l) {
                $data[] = $this->makeRhythm('rhythm', $catId, $l);
            }
        }

        // ── MELODIC DICTATION ────────────────────────────────────────────────
        // Focused curriculum: every lesson teaches ONE core melodic structure
        // in all its dimensions. Configs follow the Exercise Setup Studio
        // engine — TonalMelodyGenerator melodies (difficulty-gated motion
        // rules), DictationRhythmService beat-pattern rhythms (bar math always
        // exact), major-root key signatures with an explicit `mode` for minor
        // lessons (never 'Am'/'Dm' pseudo-roots). Rhythm enters progressively:
        // lessons 1–5 are uniform quarter notes (pure pitch focus), 6+ widen
        // the note-value vocabulary, 16 runs the full Studio palette.
        $catId = $categories['melodic-dictation']->id ?? null;
        if ($catId) {
            $dictLessons = [
                [1, 'beginner', 5,
                    ['note_pool' => ['C4', 'D4', 'E4'], 'difficulty' => 'beginner', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'],
                        'allowed_note_values' => ['quarter'], 'tempo_range' => [58, 64]],
                    'Stepwise Motion I: Do–Re–Mi', 'Your first dictations use only C, D and E, moving by step. Learn to hear a line rise, fall or repeat a note — the core decision behind every dictation you will ever write.',
                    'Adım Adım I: Do–Re–Mi', 'İlk dikteleriniz yalnızca C, D ve E notalarını komşu adımlarla kullanır. Ezginin yükselmesini, inmesini ve nota tekrarını duymayı öğrenin — yazacağınız her diktenin arkasındaki çekirdek karar budur.',
                    ['steps', 'c-major', 'narrow-range'], ['melodic-dictation', 'stepwise-hearing']],
                [2, 'beginner', 5,
                    ['note_pool' => ['C4', 'D4', 'E4', 'F4', 'G4'], 'difficulty' => 'beginner', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'],
                        'allowed_note_values' => ['quarter'], 'tempo_range' => [56, 62]],
                    'Stepwise Motion II: The C Pentachord', 'The five-note pentachord Do–Sol, still purely by step. Track where the line sits inside the five scale degrees — the span in which most beginner melodies live.',
                    'Adım Adım II: C Pentakordu', 'Beş notalık Do–Sol pentakordu, hâlâ yalnızca komşu adımlarla. Ezginin beş gam derecesi içindeki konumunu izleyin — başlangıç melodilerinin çoğu bu alanda yaşar.',
                    ['steps', 'pentachord', 'c-major'], ['melodic-dictation', 'stepwise-hearing', 'scale-degrees']],
                [3, 'beginner', 6,
                    ['note_pool' => ['C4', 'E4', 'G4', 'C5'], 'difficulty' => 'intermediate', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'],
                        'allowed_note_values' => ['quarter'], 'tempo_range' => [56, 62]],
                    'The Tonic Triad Frame: Do–Mi–Sol', 'Melodies built only from C, E, G and high C — the tonic triad. Learn the sound of the arpeggio skeleton that frames nearly every tonal melody.',
                    'Tonik Üçlüsü Çatısı: Do–Mi–Sol', 'Yalnızca C, E, G ve tiz C\'den — tonik üçlüsünden — kurulan melodiler. Neredeyse her tonal melodiyi çerçeveleyen arpej iskeletinin sesini öğrenin.',
                    ['triad', 'arpeggio', 'c-major'], ['melodic-dictation', 'triad-hearing']],
                [4, 'beginner', 6,
                    ['note_pool' => ['C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4', 'C5'], 'difficulty' => 'beginner', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'],
                        'allowed_note_values' => ['quarter'], 'tempo_range' => [55, 61]],
                    'Scale Lines: Full C Major', 'Stepwise lines across the whole C major octave. Feel how scale runs gain direction and momentum, and where they come to rest.',
                    'Gam Çizgileri: Tam C Majör', 'C majör oktavının tamamında adım adım çizgiler. Gam koşularının nasıl yön ve ivme kazandığını, nerede karar bulduğunu hissedin.',
                    ['scale-runs', 'steps', 'c-major'], ['melodic-dictation', 'stepwise-hearing', 'scale-degrees']],
                [5, 'beginner', 6,
                    ['note_pool' => ['C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4', 'C5'], 'difficulty' => 'intermediate', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'],
                        'allowed_note_values' => ['quarter'], 'tempo_range' => [55, 61]],
                    'Steps Meet Skips', 'Steps and small skips (thirds, an occasional fourth) now mix in one line. Learn to catch the moment a melody jumps instead of walking.',
                    'Adımlar Atlamalarla Buluşuyor', 'Adımlar ve küçük atlamalar (üçlüler, ara sıra bir dörtlü) artık aynı çizgide karışıyor. Melodinin yürümek yerine sıçradığı ânı yakalamayı öğrenin.',
                    ['steps', 'skips', 'thirds', 'c-major'], ['melodic-dictation', 'interval-hearing']],
                [6, 'intermediate', 8,
                    ['note_pool' => ['C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4', 'C5'], 'difficulty' => 'intermediate', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'],
                        'allowed_note_values' => ['quarter', 'half'], 'tempo_range' => [54, 60]],
                    'Rhythm Enters: Quarters and Halves', 'The first true rhythm dimension: quarter and half notes. Hear which tones are held twice as long — duration is now part of the answer.',
                    'Ritim Sahnede: Dörtlükler ve İkilikler', 'İlk gerçek ritim boyutu: dörtlük ve ikilik notalar. Hangi seslerin iki kat uzun tutulduğunu duyun — süre artık cevabın bir parçası.',
                    ['rhythm', 'half-notes', 'c-major'], ['melodic-dictation', 'rhythm-hearing']],
                [7, 'intermediate', 8,
                    ['note_pool' => ['C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4', 'C5'], 'difficulty' => 'intermediate', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'],
                        'allowed_note_values' => ['quarter', 'eighth'], 'tempo_range' => [52, 58]],
                    'Eighth-Note Pairs', 'Beats now subdivide: paired eighth notes double the speed inside a beat. Learn to write two pitches where one pulse falls.',
                    'Sekizlik Çiftler', 'Vuruşlar artık bölünüyor: çift sekizlikler vuruş içindeki hızı ikiye katlar. Tek bir nabza iki perde yazmayı öğrenin.',
                    ['rhythm', 'eighth-notes', 'subdivision'], ['melodic-dictation', 'rhythm-hearing']],
                [8, 'intermediate', 8,
                    ['note_pool' => ['G3', 'A3', 'B3', 'C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4', 'C5'], 'difficulty' => 'intermediate', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'],
                        'allowed_note_values' => ['quarter', 'half'], 'tempo_range' => [52, 58]],
                    'Dominant and Tonic: The Sol–Do Pull', 'Phrases that end on Do (rest) or on Sol (suspense). Learn cadence hearing — the single most useful anchor in real dictation — over a register reaching down to G3.',
                    'Dominant ve Tonik: Sol–Do Çekimi', 'Do üzerinde (karar) ya da Sol üzerinde (askıda) biten cümleler. Kadans duyuşunu öğrenin — gerçek diktedeki en işlevsel çapa — G3\'e inen bir registerde.',
                    ['cadence', 'dominant', 'tonic'], ['melodic-dictation', 'cadence-hearing', 'scale-degrees']],
                [9, 'intermediate', 8,
                    ['note_pool' => ['A3', 'B3', 'C4', 'D4', 'E4', 'F4', 'G4', 'A4'], 'difficulty' => 'intermediate', 'mode' => 'minor', 'accidentals' => 'none', 'key_signatures' => ['C'],
                        'allowed_note_values' => ['quarter', 'half'], 'tempo_range' => [52, 58]],
                    'Minor Colour: A Natural Minor', 'The same skills, moved into A natural minor — no accidentals, pure minor colour. Anchor your ear on La as the new home tone.',
                    'Minör Renk: La Doğal Minör', 'Aynı beceriler La doğal minöre taşınıyor — hiç arıza yok, saf minör rengi. Kulağınızı yeni karar sesi La\'ya demirleyin.',
                    ['a-minor', 'natural-minor', 'mode'], ['melodic-dictation', 'minor-hearing']],
                [10, 'intermediate', 8,
                    ['note_pool' => ['G3', 'A3', 'B3', 'C4', 'D4', 'E4', 'F#4', 'G4', 'A4', 'B4', 'C5', 'D5'], 'difficulty' => 'intermediate', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['G'],
                        'allowed_note_values' => ['quarter', 'eighth', 'half'], 'tempo_range' => [52, 58]],
                    'G Major: Hearing the Sharp Side', 'All the structures so far — steps, skips, cadences, mixed rhythm — now in G major with F#. Re-anchor Do onto G.',
                    'G Majör: Diyezli Tarafı Duymak', 'Şimdiye dek öğrenilen tüm yapılar — adımlar, atlamalar, kadanslar, karma ritim — artık F#\'lı G majörde. Do\'yu G üzerine yeniden demirleyin.',
                    ['g-major', 'one-sharp', 'transposition'], ['melodic-dictation', 'key-flexibility']],
                [11, 'intermediate', 8,
                    ['note_pool' => ['C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'Bb4', 'C5', 'D5', 'E5', 'F5'], 'difficulty' => 'intermediate', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['F'],
                        'allowed_note_values' => ['quarter', 'eighth', 'half'], 'tempo_range' => [52, 58]],
                    'F Major: Hearing the Flat Side', 'The same structures in F major with Bb, reaching up to F5. One flat, a higher register, and a new home for Do.',
                    'F Majör: Bemollü Tarafı Duymak', 'Aynı yapılar Bb\'li F majörde, F5\'e uzanan tiz bir registerde. Bir bemol, daha tiz bir bölge ve Do için yeni bir yuva.',
                    ['f-major', 'one-flat', 'transposition'], ['melodic-dictation', 'key-flexibility']],
                [12, 'advanced', 10,
                    ['note_pool' => ['C4', 'D4', 'E4', 'F4', 'G4', 'A4', 'B4', 'C5'], 'difficulty' => 'intermediate', 'mode' => 'major', 'accidentals' => 'none', 'key_signatures' => ['C'],
                        'time_signature' => '3/4', 'allowed_note_values' => ['quarter', 'half', 'dotted-half', 'eighth'], 'tempo_range' => [50, 56]],
                    'Triple Metre: Dictation in 3/4', 'One dedicated dimension: the waltz metre. Bars of three beats change where the stress falls — learn to hear the dotted-half arrival and the 3/4 barline.',
                    'Üç Zaman: 3/4 İçinde Dikte', 'Tek ve özel bir boyut: vals ölçüsü. Üç vuruşluk ölçüler vurgunun yerini değiştirir — noktalı ikilik kararı ve 3/4 ölçü çizgisini duymayı öğrenin.',
                    ['triple-metre', 'waltz', '3-4'], ['melodic-dictation', 'metre-hearing', 'rhythm-hearing']],
                [13, 'advanced', 10,
                    ['note_pool' => ['A3', 'B3', 'C4', 'D4', 'E4', 'F4', 'G4', 'A4'], 'difficulty' => 'intermediate', 'mode' => 'minor', 'accidentals' => 'harmonic', 'key_signatures' => ['C'],
                        'allowed_note_values' => ['quarter', 'eighth'], 'tempo_range' => [50, 56]],
                    'Harmonic Minor: The Leading Tone', 'A minor with one dramatic change: G rises to G# whenever the line climbs home to La. Learn to spot the leading tone — the sharpest signpost in minor.',
                    'Armonik Minör: Yeden Sesi', 'La minörde tek ama çarpıcı bir değişiklik: ezgi La\'ya tırmanırken G, G#\'e yükselir. Yeden sesini yakalamayı öğrenin — minördeki en keskin işaret.',
                    ['harmonic-minor', 'leading-tone', 'a-minor'], ['melodic-dictation', 'minor-hearing', 'accidental-hearing']],
                [14, 'advanced', 10,
                    ['note_pool' => ['A3', 'B3', 'C4', 'D4', 'E4', 'F4', 'G4', 'A4'], 'difficulty' => 'intermediate', 'mode' => 'minor', 'accidentals' => 'melodic', 'key_signatures' => ['C'],
                        'allowed_note_values' => ['quarter', 'eighth'], 'tempo_range' => [50, 56]],
                    'Melodic Minor: The Rising 6–7–8', 'Ascending lines raise both F and G on the way to La — the smooth melodic-minor climb. Contrast it with the natural descent in the same melody.',
                    'Melodik Minör: Yükselen 6–7–8', 'Çıkıcı çizgiler La\'ya giderken hem F\'yi hem G\'yi yükseltir — pürüzsüz melodik minör tırmanışı. Aynı melodideki doğal inişle karşılaştırın.',
                    ['melodic-minor', 'raised-sixth', 'a-minor'], ['melodic-dictation', 'minor-hearing', 'accidental-hearing']],
                [15, 'advanced', 10,
                    ['difficulty' => 'advanced', 'mode' => 'major', 'key_signatures' => ['C'],
                        'allowed_note_values' => ['quarter', 'eighth', 'half'], 'tempo_range' => [50, 56]],
                    'Chromatic Approach Tones', 'Full treble range (G3–G5), with up to two chromatic approach tones sliding a half step into a scale note. Hear colour outside the key without losing the key.',
                    'Kromatik Yaklaşma Sesleri', 'Tam sol anahtarı aralığı (G3–G5) ve gam sesine yarım adımla kayan en fazla iki kromatik yaklaşma sesi. Tonun dışındaki rengi, tonu kaybetmeden duyun.',
                    ['chromatic', 'approach-tones', 'full-range'], ['melodic-dictation', 'chromatic-hearing', 'accidental-hearing']],
                [16, 'advanced', 10,
                    ['difficulty' => 'advanced', 'mode' => 'major', 'key_signatures' => ['C', 'G', 'F'], 'bars' => 4,
                        'allowed_note_values' => ['quarter', 'eighth', 'half', 'dotted-quarter', 'dotted-half'], 'tempo_range' => [48, 54]],
                    'Master: Four-Bar Dictation, Mixed Keys', 'The capstone: four-bar melodies in C, G or F major over the full treble range, with the complete rhythm palette including dotted values. Everything the path taught, in one long phrase.',
                    'Usta: Dört Ölçü, Karma Tonlar', 'Zirve ders: tam sol anahtarı aralığında, C, G veya F majörde, noktalı değerler dahil eksiksiz ritim paletiyle dört ölçülük melodiler. Yolun öğrettiği her şey tek bir uzun cümlede.',
                    ['master', 'four-bar', 'mixed-keys'], ['melodic-dictation', 'mastery', 'sustained-attention']],
            ];
            foreach ($dictLessons as $l) {
                $data[] = $this->makeDictation('melodic-dictation', $catId, $l);
            }
        }

        // ── SINGLE NOTE ──────────────────────────────────────────────────────
        // Exercise Setup Studio rules: clef-driven octave placement (treble
        // G3–G5, bass C2–C4, alto C3–C5). Each lesson teaches one focused
        // structure: diatonic sets → single accidental → two accidentals →
        // chromatic halves → full chromatic → bass and alto clefs. Answer
        // spellings follow the lesson's key (Bb in F major); the piano-answer
        // flow accepts enharmonic equivalents. Lessons 1–10 label the answer
        // keys (note-names); 11–15 use the unlabeled keyboard.
        $catId = $categories['single-note']->id ?? null;
        if ($catId) {
            $naturals = ['C', 'D', 'E', 'F', 'G', 'A', 'B'];
            $allTwelve = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
            $noteLessons = [
                [1, 'beginner', 5, ['C', 'G'], 'treble', 'note-names',
                    'Anchor Notes: C and G', 'Identify C and G in every octave of the treble staff. C is the tonal center; G sits a perfect 5th above — the two anchors of tonal music.',
                    'Çapa Notalar: C ve G', 'C ve G\'yi sol anahtarı portesinin her oktavında tanıyın. C tonal merkezdir; G tam beşli yukarıdadır — tonal müziğin iki çapası.',
                    ['anchor-notes', 'c-g'], ['pitch-recognition']],
                [2, 'beginner', 5, ['C', 'D', 'E', 'F', 'G'], 'treble', 'note-names',
                    'C Major Pentachord', 'The five notes from C to G form the pentachord — the core of most beginner melodies. Learn every degree across the full treble range.',
                    'C Majör Pentakordu', 'C\'den G\'ye beş nota pentakordu oluşturur — çoğu başlangıç melodisinin çekirdeği. Her dereceyi sol anahtarının tüm aralığında öğrenin.',
                    ['pentachord', 'c-major'], ['pitch-recognition']],
                [3, 'beginner', 5, ['G', 'A', 'B', 'C'], 'treble', 'note-names',
                    'Upper Tetrachord: G to C', 'The four notes from G up to C complete the C major scale. Master the upper tetrachord in every octave of the treble staff.',
                    'Üst Tetrakord: G\'den C\'ye', 'G\'den C\'ye dört nota C majör gamını tamamlar. Üst tetrakordu sol anahtarı portesinin her oktavında öğrenin.',
                    ['tetrachord', 'c-major'], ['pitch-recognition']],
                [4, 'beginner', 6, $naturals, 'treble', 'note-names',
                    'Full C Major Scale', 'All seven natural notes — the white keys of the piano. Combine both tetrachords and identify any degree of the C major scale.',
                    'Tam C Majör Gamı', 'Yedi doğal notanın tamamı — piyanonun beyaz tuşları. İki tetrakordu birleştirin ve C majör gamının her derecesini tanıyın.',
                    ['c-major', 'natural-notes'], ['pitch-recognition']],
                [5, 'beginner', 6, ['G', 'A', 'B', 'C', 'D', 'E', 'F#'], 'treble', 'note-names',
                    'G Major: Meet F#', 'G major replaces F with F#. Learn to hear and spot the raised leading tone among the natural notes.',
                    'G Majör: F# ile Tanışma', 'G majör F yerine F# kullanır. Yükseltilmiş yeden sesini doğal notalar arasında duymayı ve görmeyi öğrenin.',
                    ['g-major', 'one-sharp'], ['pitch-recognition', 'accidentals']],
                [6, 'intermediate', 8, ['F', 'G', 'A', 'Bb', 'C', 'D', 'E'], 'treble', 'note-names',
                    'F Major: Meet B-flat', 'F major replaces B with Bb. Your first flat — hear how it softens the character of the scale.',
                    'F Majör: Si Bemol ile Tanışma', 'F majör B yerine Bb kullanır. İlk bemolünüz — gamın karakterini nasıl yumuşattığını duyun.',
                    ['f-major', 'one-flat'], ['pitch-recognition', 'accidentals']],
                [7, 'intermediate', 8, ['D', 'E', 'F#', 'G', 'A', 'B', 'C#'], 'treble', 'note-names',
                    'D Major: Two Sharps', 'D major carries F# and C#. Track both sharps at once inside a full diatonic set.',
                    'D Majör: İki Diyez', 'D majör F# ve C# içerir. Tam bir diyatonik set içinde iki diyezi birden takip edin.',
                    ['d-major', 'two-sharps'], ['pitch-recognition', 'accidentals']],
                [8, 'intermediate', 8, ['Bb', 'C', 'D', 'Eb', 'F', 'G', 'A'], 'treble', 'note-names',
                    'B-flat Major: Two Flats', 'B-flat major carries Bb and Eb. Learn to recognise both flats inside a full diatonic set.',
                    'Si Bemol Majör: İki Bemol', 'Bb majör, Bb ve Eb içerir. Tam bir diyatonik set içinde iki bemolü tanımayı öğrenin.',
                    ['bb-major', 'two-flats'], ['pitch-recognition', 'accidentals']],
                [9, 'intermediate', 8, ['C', 'C#', 'D', 'D#', 'E', 'F'], 'treble', 'note-names',
                    'Chromatic Steps I: C to F', 'Every half step from C to F. Focus on hearing the difference between a natural note and its sharpened neighbour.',
                    'Kromatik Adımlar I: C\'den F\'ye', 'C\'den F\'ye her yarım adım. Doğal nota ile diyezli komşusu arasındaki farkı duymaya odaklanın.',
                    ['chromatic', 'lower-half'], ['pitch-recognition', 'chromatic']],
                [10, 'intermediate', 8, ['F#', 'G', 'G#', 'A', 'A#', 'B'], 'treble', 'note-names',
                    'Chromatic Steps II: F# to B', 'Every half step from F# to B — the upper half of the chromatic scale.',
                    'Kromatik Adımlar II: F#\'dan B\'ye', 'F#\'dan B\'ye her yarım adım — kromatik gamın üst yarısı.',
                    ['chromatic', 'upper-half'], ['pitch-recognition', 'chromatic']],
                [11, 'advanced', 10, $allTwelve, 'treble', 'keyboard',
                    'All 12 Chromatic Notes', 'The complete chromatic set on the treble staff — no key labels this time. Full pitch recognition across every octave.',
                    '12 Kromatik Notanın Tamamı', 'Sol anahtarında tam kromatik set — bu kez tuş etiketleri yok. Her oktavda tam perde tanıma.',
                    ['all-12', 'chromatic'], ['pitch-recognition', 'mastery']],
                [12, 'advanced', 8, $naturals, 'bass', 'keyboard',
                    'Bass Clef: Natural Notes', 'Natural notes on the bass staff (C2–C4). Reading and hearing in the low register.',
                    'Fa Anahtarı: Doğal Notalar', 'Fa anahtarı portesinde doğal notalar (C2–C4). Pes registerde okuma ve duyma.',
                    ['bass-clef', 'natural-notes'], ['pitch-recognition', 'clef-reading']],
                [13, 'advanced', 10, $allTwelve, 'bass', 'keyboard',
                    'Bass Clef: All 12 Notes', 'The full chromatic set in the bass register. Low pitches demand the most careful listening.',
                    'Fa Anahtarı: 12 Nota', 'Pes registerde tam kromatik set. Düşük perdeler en dikkatli dinlemeyi ister.',
                    ['bass-clef', 'all-12'], ['pitch-recognition', 'clef-reading', 'mastery']],
                [14, 'advanced', 8, $naturals, 'alto', 'keyboard',
                    'Alto Clef: Natural Notes', 'Natural notes on the alto staff (C3–C5), where middle C sits on the centre line.',
                    'Do Anahtarı: Doğal Notalar', 'Do (alto) anahtarı portesinde doğal notalar (C3–C5); orta Do orta çizgidedir.',
                    ['alto-clef', 'natural-notes'], ['pitch-recognition', 'clef-reading']],
                [15, 'advanced', 10, $allTwelve, 'alto', 'keyboard',
                    'Master: Alto Clef, All 12 Notes', 'The ultimate single-note challenge: the complete chromatic set on the alto staff with an unlabeled keyboard.',
                    'Usta: Do Anahtarı, 12 Nota', 'Nihai tek nota mücadelesi: do anahtarı portesinde tam kromatik set, etiketsiz klavye.',
                    ['master', 'alto-clef', 'all-12'], ['pitch-recognition', 'clef-reading', 'mastery']],
            ];
            foreach ($noteLessons as $l) {
                $data[] = $this->makeSingleNote('single-note', $catId, $l);
            }
        }

        return $data;
    }

    // ── BUILDER HELPERS ──────────────────────────────────────────────────────

    /**
     * Harmonic-interval lessons follow the Exercise Setup Studio rules: the
     * config carries a clef (generator keeps every dyad inside CLEF_RANGES)
     * instead of hardcoded octaves. No direction key — both notes sound
     * simultaneously. $extraCfg merges extra generator keys (e.g.
     * distractor_mode/distractor_count for 'near').
     */
    private function makeHarmonicInterval(string $categorySlug, int $catId, array $l): array
    {
        [$sortOrder, $level, $duration, $intervals, $clef, $extraCfg,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        return [
            'category_id' => $catId,
            'slug' => "{$categorySlug}-lesson-{$sortOrder}",
            'title' => $titleEn,
            'description' => $descEn,
            'translations' => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level' => $level,
            'sort_order' => $sortOrder,
            'estimated_duration_minutes' => $duration,
            'tags' => $tags,
            'skills_trained' => $skills,
            'config_json' => array_merge([
                'practice_type' => 'harmonic-interval-practice',
                'allowed_intervals' => $intervals,
                'allowed_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'],
                'clef' => $clef,
                'question_counts' => ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
            ], $extraCfg),
        ];
    }

    /**
     * Melodic-interval lessons follow the Exercise Setup Studio rules: the
     * config carries a clef (generator keeps every note inside CLEF_RANGES)
     * and a direction instead of hardcoded octaves. $extraCfg merges extra
     * generator keys (e.g. distractor_mode/distractor_count for 'near').
     */
    private function makeMelodicInterval(string $categorySlug, int $catId, array $l): array
    {
        [$sortOrder, $level, $duration, $intervals, $clef, $extraCfg,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        return [
            'category_id' => $catId,
            'slug' => "{$categorySlug}-lesson-{$sortOrder}",
            'title' => $titleEn,
            'description' => $descEn,
            'translations' => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level' => $level,
            'sort_order' => $sortOrder,
            'estimated_duration_minutes' => $duration,
            'tags' => $tags,
            'skills_trained' => $skills,
            'config_json' => array_merge([
                'practice_type' => 'melodic-interval-practice',
                'allowed_intervals' => $intervals,
                'allowed_notes' => ['C', 'D', 'E', 'F', 'G', 'A', 'B'],
                'clef' => $clef,
                'direction' => 'mixed',
                'question_counts' => ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
            ], $extraCfg),
        ];
    }

    private function makeDirection(string $categorySlug, int $catId, array $l): array
    {
        // No 'octave' key: pitch placement is clef-driven (CLEF_RANGES).
        // $clef may be a string or an array of clefs (mixed-register lessons).
        [$sortOrder, $level, $duration, $semitones, $notes, $clef,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        return [
            'category_id' => $catId,
            'slug' => "{$categorySlug}-lesson-{$sortOrder}",
            'title' => $titleEn,
            'description' => $descEn,
            'translations' => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level' => $level,
            'sort_order' => $sortOrder,
            'estimated_duration_minutes' => $duration,
            'tags' => $tags,
            'skills_trained' => $skills,
            'config_json' => [
                'practice_type' => 'interval-direction-practice',
                'allowed_intervals_semitones' => $semitones,
                'allowed_notes' => $notes,
                'clef' => $clef,
                'question_counts' => ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
            ],
        ];
    }

    private function makeConstruction(string $categorySlug, int $catId, array $l): array
    {
        // No 'octave' key: pitch placement is clef-driven (CLEF_RANGES), the
        // Exercise Setup Studio rule. $extraCfg carries distractor settings
        // ('near' mode) for the advanced lessons.
        [$sortOrder, $level, $duration, $intervals, $roots, $clef, $direction, $extraCfg,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        return [
            'category_id' => $catId,
            'slug' => "{$categorySlug}-lesson-{$sortOrder}",
            'title' => $titleEn,
            'description' => $descEn,
            'translations' => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level' => $level,
            'sort_order' => $sortOrder,
            'estimated_duration_minutes' => $duration,
            'tags' => $tags,
            'skills_trained' => $skills,
            'config_json' => array_merge([
                'practice_type' => 'interval-construction-practice',
                'allowed_intervals' => $intervals,
                'allowed_root_notes' => $roots,
                'clef' => $clef,
                'direction' => $direction,
                'question_counts' => ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
            ], $extraCfg),
        ];
    }

    private function makeComparison(string $categorySlug, int $catId, array $l): array
    {
        [$sortOrder, $level, $duration, $clef, $pairs,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        return [
            'category_id' => $catId,
            'slug' => "{$categorySlug}-lesson-{$sortOrder}",
            'title' => $titleEn,
            'description' => $descEn,
            'translations' => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level' => $level,
            'sort_order' => $sortOrder,
            'estimated_duration_minutes' => $duration,
            'tags' => $tags,
            'skills_trained' => $skills,
            'config_json' => [
                'practice_type' => 'interval-comparison-practice',
                'allowed_interval_pairs' => $pairs,
                // Clef-driven placement (Studio rule): the generator picks the
                // octave inside CLEF_RANGES — no hardcoded octave.
                'clef' => $clef,
                'question_counts' => ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
            ],
        ];
    }

    private function makeScale(string $categorySlug, int $catId, array $l): array
    {
        [$sortOrder, $level, $duration, $scaleTypes, $roots, $direction, $clef, $distractors,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        return [
            'category_id' => $catId,
            'slug' => "{$categorySlug}-lesson-{$sortOrder}",
            'title' => $titleEn,
            'description' => $descEn,
            'translations' => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level' => $level,
            'sort_order' => $sortOrder,
            'estimated_duration_minutes' => $duration,
            'tags' => $tags,
            'skills_trained' => $skills,
            'config_json' => [
                'practice_type' => 'scale-practice',
                'allowed_scale_types' => $scaleTypes,
                'allowed_root_notes' => $roots,
                'direction' => $direction,
                // Clef-driven placement (Studio rule): the generator picks the
                // octave inside CLEF_RANGES — no hardcoded octave.
                'clef' => $clef,
                'distractor_pool' => $distractors,
                'question_counts' => ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
            ],
        ];
    }

    private function makeChord(string $categorySlug, int $catId, array $l): array
    {
        [$sortOrder, $level, $duration, $chordTypes, $roots, $voicing, $inversions, $distractors, $clef,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        // $inversions: bool (Exercise Setup semantics — true mixes 0/1/2) or an
        // explicit array of inversion values for focused lessons (e.g. [1] for
        // a first-inversion-only lesson).
        $config = [
            'practice_type' => 'chord-practice',
            'allowed_chord_types' => $chordTypes,
            'allowed_root_notes' => $roots,
            'voicing' => $voicing,
            'include_inversions' => is_array($inversions) ? $inversions !== [0] : $inversions,
            'distractor_pool' => $distractors,
            'clef' => $clef,
            'question_counts' => ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
        ];
        if (is_array($inversions)) {
            $config['inversion_values'] = $inversions;
        }

        return [
            'category_id' => $catId,
            'slug' => "{$categorySlug}-lesson-{$sortOrder}",
            'title' => $titleEn,
            'description' => $descEn,
            'translations' => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level' => $level,
            'sort_order' => $sortOrder,
            'estimated_duration_minutes' => $duration,
            'tags' => $tags,
            'skills_trained' => $skills,
            'config_json' => $config,
        ];
    }

    private function makeRhythm(string $categorySlug, int $catId, array $l): array
    {
        [$sortOrder, $level, $duration, $timeSigs, $noteValues, $tempo, $bars, $excludeCells,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        $config = [
            'practice_type' => 'rhythm-practice',
            'time_signatures' => $timeSigs,
            'allowed_note_values' => $noteValues,
            // Distractor difficulty follows the lesson level — same mapping as the
            // Exercise Setup Studio flow (PracticeRhythm::mapRhythmDifficulty).
            'rhythm_difficulty' => match ($level) {
                'beginner' => 'easy',
                'advanced' => 'hard',
                default => 'medium',
            },
            'tempo_range' => [$tempo - 4, $tempo + 4],
            'bars' => $bars,
            'question_counts' => ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
        ];
        if (! empty($excludeCells)) {
            $config['exclude_cells'] = $excludeCells;
        }

        return [
            'category_id' => $catId,
            'slug' => "{$categorySlug}-lesson-{$sortOrder}",
            'title' => $titleEn,
            'description' => $descEn,
            'translations' => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level' => $level,
            'sort_order' => $sortOrder,
            'estimated_duration_minutes' => $duration,
            'tags' => $tags,
            'skills_trained' => $skills,
            'config_json' => $config,
        ];
    }

    private function makeDictation(string $categorySlug, int $catId, array $l): array
    {
        [$sortOrder, $level, $duration, $config,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        return [
            'category_id' => $catId,
            'slug' => "{$categorySlug}-lesson-{$sortOrder}",
            'title' => $titleEn,
            'description' => $descEn,
            'translations' => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level' => $level,
            'sort_order' => $sortOrder,
            'estimated_duration_minutes' => $duration,
            'tags' => $tags,
            'skills_trained' => $skills,
            'config_json' => array_merge([
                'practice_type' => 'melodic-dictation',
                'clef' => 'treble',
                'include_rhythm' => true,
                'time_signature' => '4/4',
                'bars' => 2,
                'question_counts' => ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
            ], $config),
        ];
    }

    /**
     * Single-note lessons follow the Exercise Setup Studio rules: the config
     * carries a clef (generator keeps every note inside CLEF_RANGES) instead
     * of hardcoded octaves. answer_mode rides on each generated question so
     * the practice blade can label the piano keys per lesson.
     */
    private function makeSingleNote(string $categorySlug, int $catId, array $l): array
    {
        [$sortOrder, $level, $duration, $notes, $clef, $answerMode,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        return [
            'category_id' => $catId,
            'slug' => "{$categorySlug}-lesson-{$sortOrder}",
            'title' => $titleEn,
            'description' => $descEn,
            'translations' => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level' => $level,
            'sort_order' => $sortOrder,
            'estimated_duration_minutes' => $duration,
            'tags' => $tags,
            'skills_trained' => $skills,
            'config_json' => [
                'practice_type' => 'single-note-practice',
                'target_type' => 'note',
                'allowed_notes' => $notes,
                'clef' => $clef,
                'answer_mode' => $answerMode,
                'distractor_count' => 3,
                'question_counts' => ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
            ],
        ];
    }
}
