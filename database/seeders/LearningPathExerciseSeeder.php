<?php

namespace Database\Seeders;

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
        $catId = $categories['melodic-intervals']->id ?? null;
        if ($catId) {
            $lessons = [
                [1, 'beginner',     5,  ['Perfect Unison','Perfect Octave'],           ['C','D','E','F','G'],        ['3','4'],
                    'Unison & Octave Recognition', 'Identify the two most extreme intervals — the unison (same note) and the octave. They form the foundation of all interval training.',
                    'Unison ve Oktav Tanıma', 'En uç iki aralığı tanıyın — unison (aynı nota) ve oktav. Tüm aralık eğitiminin temelini oluştururlar.',
                    ['perfect-intervals','unison','octave'], ['interval-recognition','audiation']],
                [2, 'beginner',     5,  ['Perfect 4th','Perfect 5th'],                 ['C','D','E','F','G'],        ['3','4'],
                    'Perfect Intervals', 'Train your ear on the two most open and resonant intervals — the perfect 4th and perfect 5th. These are the backbone of harmony.',
                    'Tam Aralıklar', 'En açık ve rezonant iki aralığı — tam dörtlü ve tam beşliyi — kulağınıza işleyin. Bunlar armoninin temel taşlarıdır.',
                    ['perfect-intervals'], ['interval-recognition']],
                [3, 'beginner',     5,  ['Major 2nd','Minor 2nd'],                     ['C','D','E','F','G','A','B'], ['3','4'],
                    'Major & Minor 2nds', 'Distinguish the whole step (major 2nd) from the half step (minor 2nd) — the two smallest intervals in Western music.',
                    'Büyük ve Küçük İkili', 'Tam adımı (büyük ikili) yarım adımdan (küçük ikili) ayırt edin — Batı müziğindeki en küçük iki aralık.',
                    ['seconds','stepwise-motion'], ['interval-recognition','half-step','whole-step']],
                [4, 'beginner',     5,  ['Major 3rd','Minor 3rd'],                     ['C','D','E','F','G','A','B'], ['3','4'],
                    'Major & Minor 3rds', 'Master the core chord-quality intervals. The major 3rd sounds bright and happy; the minor 3rd sounds darker and more introspective.',
                    'Büyük ve Küçük Üçlü', 'Akor kalitesini belirleyen temel aralıkları öğrenin. Büyük üçlü parlak ve neşeli; küçük üçlü daha koyu ve içe dönük gelir.',
                    ['thirds','chord-quality'], ['interval-recognition','major-minor-distinction']],
                [5, 'beginner',     5,  ['Major 6th','Minor 6th'],                     ['C','D','E','F','G'],        ['3','4'],
                    'Major & Minor 6ths', 'The 6th intervals are inversions of the 3rds. A major 6th inverts to a minor 3rd. Training these strengthens your overall interval network.',
                    'Büyük ve Küçük Altılı', 'Altılı aralıklar üçlülerin tersçevrimidir. Büyük altılı, küçük üçlüye dönüşür. Bu aralıklar genel aralık ağınızı güçlendirir.',
                    ['sixths','inversions'], ['interval-recognition']],
                [6, 'intermediate', 8,  ['Major 7th','Minor 7th'],                     ['C','D','E','F','G'],        ['3','4'],
                    'Major & Minor 7ths', 'The 7th intervals create strong tension that demands resolution. The major 7th is very dissonant; the minor 7th is gentler but still pulls strongly.',
                    'Büyük ve Küçük Yedili', 'Yedili aralıklar güçlü gerilim yaratır ve çözünme ister. Büyük yedili çok disonant; küçük yedili daha yumuşak ama yine de güçlü çeker.',
                    ['sevenths','tension','resolution'], ['interval-recognition','dissonance']],
                [7, 'intermediate', 8,  ['Augmented 4th'],                             ['C','D','F','G'],            ['3','4'],
                    'The Tritone', 'The tritone (augmented 4th / diminished 5th) divides the octave exactly in half. It is the most dissonant interval and was historically called "diabolus in musica".',
                    'Triton Aralığı', 'Triton (artık dörtlü / eksik beşli) oktavı tam ortadan böler. En disonant aralıktır ve tarihsel olarak "müziğin şeytanı" olarak adlandırılmıştır.',
                    ['tritone','dissonance'], ['interval-recognition','chromatic-awareness']],
                [8, 'intermediate', 8,  ['Perfect Unison','Major 2nd','Minor 2nd','Major 3rd','Minor 3rd','Perfect 4th','Perfect 5th','Major 6th','Minor 6th'], ['C','D','E','F','G'], ['3','4'],
                    'All Diatonic Intervals', 'Bring together all diatonic intervals in a single session. From unison to major 6th — no chromatic alterations, just white-key intervals.',
                    'Tüm Diyatonik Aralıklar', 'Tüm diyatonik aralıkları tek bir oturumda bir araya getirin. Unison\'dan büyük altılıya — kromatik değişiklik yok, sadece beyaz tuş aralıkları.',
                    ['diatonic','mixed'], ['interval-recognition','comprehensive']],
                [9, 'intermediate', 8,  ['Major 2nd','Minor 2nd','Major 3rd','Minor 3rd'], ['C','D','E','F','G'],    ['3','4'],
                    'Ascending vs Descending: 2nds & 3rds', 'Practice 2nds and 3rds in both ascending and descending directions. Directionality is a crucial skill for melodic dictation.',
                    'Yükselen ve Alçalan: İkili & Üçlü', 'İkili ve üçlüleri hem yükselen hem de alçalan yönlerde çalışın. Yönsellik, melodik dikte için çok önemli bir beceridir.',
                    ['ascending','descending','direction'], ['interval-recognition','directional-hearing']],
                [10, 'intermediate', 8,  ['Major 2nd','Major 3rd','Perfect 4th','Perfect 5th','Major 6th','Major 7th'], ['C','D','E','F','G'], ['4','4'],
                    'Intervals in C Major Context', 'All intervals heard within C major scale context. This helps internalize how intervals function inside a key.',
                    'C Majör Bağlamında Aralıklar', 'C majör gam bağlamı içinde duyulan tüm aralıklar. Bu, aralıkların bir tonalite içinde nasıl işlev gördüğünü içselleştirmeye yardımcı olur.',
                    ['tonal-context','major-key'], ['interval-recognition','tonal-awareness']],
                [11, 'advanced',    10, ['Minor 2nd','Major 2nd','Minor 3rd','Major 3rd','Perfect 4th','Augmented 4th'], ['C','D','E','F','G','A','B'], ['3','4'],
                    'Chromatic Intervals I (2nds–4ths)', 'Full chromatic interval set from minor 2nd through augmented 4th. This requires precise discrimination between half-step gradations.',
                    'Kromatik Aralıklar I (2.–4.)', 'Küçük ikili\'den artık dörtlüye kadar tam kromatik aralık seti. Bu, yarım adım kademeleri arasında hassas ayrım gerektirir.',
                    ['chromatic','lower-register'], ['interval-recognition','chromatic-precision']],
                [12, 'advanced',    10, ['Diminished 5th','Perfect 5th','Minor 6th','Major 6th','Minor 7th','Major 7th'], ['C','D','E','F','G'], ['3','4'],
                    'Chromatic Intervals II (5ths–7ths)', 'The upper half of the chromatic interval spectrum — from diminished 5th through major 7th. Focus on subtle distinctions.',
                    'Kromatik Aralıklar II (5.–7.)', 'Kromatik aralık spektrumunun üst yarısı — eksik beşliden büyük yediliye. İnce ayrımlara odaklanın.',
                    ['chromatic','upper-register'], ['interval-recognition','chromatic-precision']],
                [13, 'advanced',    10, ['Minor 2nd','Major 2nd','Minor 3rd','Major 3rd','Perfect 4th','Augmented 4th','Diminished 5th','Perfect 5th','Minor 6th','Major 6th','Minor 7th','Major 7th'], ['C','D','E','F','G'], ['3','4'],
                    'Full 12-Interval Pool', 'All twelve intervals in a single session. This is the complete chromatic interval set — the capstone of interval recognition training.',
                    '12 Aralığın Tamamı', 'Tek bir oturumda tüm on iki aralık. Bu, tam kromatik aralık setidir — aralık tanıma eğitiminin doruk noktası.',
                    ['chromatic','comprehensive'], ['interval-recognition','mastery']],
                [14, 'advanced',    10, ['Major 3rd','Perfect 5th','Minor 7th','Major 2nd','Perfect 4th'], ['C','D','E','F','G','A'], ['3','4'],
                    'Speed Drill: Common Pairs', 'Fast-paced recognition of the five most commonly tested intervals. Develop automatic, instantaneous recognition under time pressure.',
                    'Hız Egzersizi: Yaygın Çiftler', 'En sık test edilen beş aralığın hızlı tanınması. Zaman baskısı altında otomatik, anlık tanıma geliştirin.',
                    ['speed','common-intervals'], ['interval-recognition','speed-training']],
                [15, 'advanced',    10, ['Minor 2nd','Major 2nd','Minor 3rd','Major 3rd','Perfect 4th','Augmented 4th','Diminished 5th','Perfect 5th','Minor 6th','Major 6th','Minor 7th','Major 7th'], ['C','D','E','F','G','A','B'], ['2','3','4','5'],
                    'Master: Mixed Chromatic & Register', 'The ultimate melodic interval challenge — all twelve intervals across a four-octave range. True mastery requires recognizing intervals regardless of register.',
                    'Usta Seviye: Kromatik & Register', 'Nihai melodik aralık zorluğu — dört oktav aralığında tüm on iki aralık. Gerçek ustalık, aralıkları register\'dan bağımsız olarak tanımayı gerektirir.',
                    ['master','chromatic','multi-octave'], ['interval-recognition','mastery','register-independence']],
            ];
            foreach ($lessons as $l) {
                $data[] = $this->makeInterval('melodic-intervals', $catId, $l, 'melodic-interval-practice');
            }
        }

        // ── INTERVAL DIRECTION ───────────────────────────────────────────────
        $catId = $categories['interval-direction']->id ?? null;
        if ($catId) {
            $lessons = [
                [1, 'beginner',     5,  [1,2],       ['C','D','E','F','G'],        '4', 'treble',
                    'Steps Up or Down', 'Identify whether a melodic step goes up (ascending) or down (descending). Start with the smallest intervals — half steps and whole steps.',
                    'Adım Yukarı mı Aşağı mı', 'Melodik bir adımın yukarı (yükselen) mi yoksa aşağı (alçalan) mı gittiğini belirleyin. En küçük aralıklarla başlayın — yarım ve tam adımlar.',
                    ['steps','ascending','descending'], ['directional-hearing','stepwise']],
                [2, 'beginner',     5,  [3,4],       ['C','D','E','F','G'],        '4', 'treble',
                    '3rd Leaps: Up or Down', 'Identify the direction of minor and major 3rd leaps. Leaps are more dramatic than steps and easier to distinguish directionally.',
                    'Üçlü Sıçramalar', 'Küçük ve büyük üçlü sıçramalarının yönünü belirleyin. Sıçramalar adımlardan daha dramatiktir ve yön olarak ayırt etmesi daha kolaydır.',
                    ['thirds','leaps'], ['directional-hearing']],
                [3, 'beginner',     5,  [5,7],       ['C','D','E','F','G'],        '4', 'treble',
                    'Perfect Intervals: Direction', 'Identify ascending vs descending perfect 4ths and 5ths. These open intervals have a very characteristic sound in each direction.',
                    'Tam Aralıklarda Yön', 'Yükselen ve alçalan tam dörtlü ve beşlileri ayırt edin. Bu açık aralıklar her yönde çok karakteristik bir sese sahiptir.',
                    ['perfect-intervals','direction'], ['directional-hearing']],
                [4, 'beginner',     5,  [1,2],       ['C','D','E','F','G'],        '3', 'bass',
                    '2nds in Bass Clef', 'Practice directional hearing in the bass clef register. Lower pitches can challenge your sense of direction.',
                    'Bas Anahtarında İkililere', 'Bas anahtarı registerında yön dinlemeyi pratik yapın. Düşük perdeler yön duygınızı zorlayabilir.',
                    ['bass-clef','seconds'], ['directional-hearing','clef-reading']],
                [5, 'beginner',     5,  [1,2,3,4],   ['C','D','E','F','G'],        '4', 'treble',
                    'Mixed 2nds and 3rds', 'Combine half steps, whole steps, and thirds in both directions. Build fluency with the most common melodic intervals.',
                    'Karışık İkili ve Üçlü', 'Yarım adımları, tam adımları ve üçlüleri her iki yönde birleştirin. En yaygın melodik aralıklarda akıcılık geliştirin.',
                    ['mixed','seconds','thirds'], ['directional-hearing']],
                [6, 'intermediate', 8,  [8,9,10,11], ['C','D','E','F','G'],        '4', 'treble',
                    '6ths and 7ths: Direction', 'Large intervals — 6ths and 7ths — can be tricky to judge directionally. Their wide span creates strong melodic tension.',
                    'Altılı ve Yedili Yön', 'Büyük aralıklar — altılı ve yedililer — yön açısından değerlendirmesi zor olabilir. Geniş aralıkları güçlü melodik gerilim yaratır.',
                    ['sixths','sevenths','large-intervals'], ['directional-hearing']],
                [7, 'intermediate', 8,  [12],        ['C','D','E','F','G'],        '4', 'treble',
                    'Octave Leaps', 'The octave leap is dramatic and unmistakable, but identifying its direction remains an important skill. Focus on registral context.',
                    'Oktav Sıçramalar', 'Oktav sıçraması dramatik ve ayırt edilemezdir, ancak yönünü belirlemek önemli bir beceri olmaya devam eder. Register bağlamına odaklanın.',
                    ['octave','leaps'], ['directional-hearing','register']],
                [8, 'intermediate', 8,  [5,6,7],     ['C','D','E','F','G'],        '4', 'treble',
                    'Mixed 4ths and 5ths', 'Practice direction recognition with 4ths, tritones, and 5ths — the mid-range leaping intervals central to melodic writing.',
                    'Karışık Dörtlü ve Beşli', 'Dörtlü, triton ve beşlilerle yön tanımayı pratik yapın — melodik yazıda merkezi olan orta aralık sıçrama aralıkları.',
                    ['fourths','fifths','tritone'], ['directional-hearing']],
                [9, 'intermediate', 8,  [1,2,3,4],   ['C','D','E','F','G'],        '4', 'alto',
                    'Alto Clef Introduction', 'Apply directional hearing in the alto clef. Viola and trombone players need fluency in this clef.',
                    'Alto Anahtarı Giriş', 'Alto anahtarında yön dinlemeyi uygulayın. Viyola ve trombon oyuncuları bu anahtarda akıcılığa ihtiyaç duyar.',
                    ['alto-clef'], ['directional-hearing','clef-reading']],
                [10, 'intermediate', 8, [1,2,3,4,5,6,7,8,9,10,11,12], ['C','D','E','F','G'], '4', 'treble',
                    'All Intervals: Treble Clef', 'Full twelve-interval direction test in treble clef. Every semitone from minor 2nd to octave, ascending and descending.',
                    'Tüm Aralıklar: Violin Anahtarı', 'Violin anahtarında tam on iki aralık yön testi. Küçük ikili\'den oktava kadar her yarım ton, yükselen ve alçalan.',
                    ['treble-clef','comprehensive'], ['directional-hearing','mastery']],
                [11, 'advanced',    10, [1,2,3,4,5,6,7,8,9,10,11,12], ['C','D','E','F','G'], '3', 'bass',
                    'All Intervals: Bass Clef', 'Complete directional interval recognition in the bass clef — same pool as lesson 10 but in the lower register.',
                    'Tüm Aralıklar: Bas Anahtarı', 'Bas anahtarında tam yönlü aralık tanıma — ders 10 ile aynı havuz ama daha pes register\'da.',
                    ['bass-clef','comprehensive'], ['directional-hearing','mastery']],
                [12, 'advanced',    10, [1,2,3,4,5,6,7,8,9,10,11,12], ['C','D','E','F','G'], '4', 'alto',
                    'All Intervals: Alto Clef', 'Complete directional recognition in the alto clef. The most challenging clef for most musicians.',
                    'Tüm Aralıklar: Alto Anahtarı', 'Alto anahtarında tam yönlü tanıma. Çoğu müzisyen için en zorlu anahtar.',
                    ['alto-clef','comprehensive'], ['directional-hearing','mastery']],
                [13, 'advanced',    10, [1,2,10,11], ['C','D','E','F','G'],        '4', 'treble',
                    'Speed Drill: 2nds vs 7ths', 'Fast discrimination between the two extremes of the interval spectrum — tiny 2nds vs huge 7ths. Build automatic response.',
                    'Hız: İkililer vs Yedililer', 'Aralık spektrumunun iki ucu arasında hızlı ayrım — küçük ikililere karşı büyük yedililer. Otomatik tepki geliştirin.',
                    ['speed','contrast'], ['directional-hearing','speed-training']],
                [14, 'advanced',    10, [1,2,3,4,5,6,7,8,9,10,11,12], ['C','D','E','F','G','A','B'], '4', 'treble',
                    'Mixed Clefs: Full Chromatic', 'Full chromatic interval set with both treble and bass clef questions interleaved. Real-world sight-singing demands clef fluency.',
                    'Karma Anahtar: Tam Kromatik', 'Tam kromatik aralık seti, violin ve bas anahtarı sorularıyla iç içe. Gerçek dünya deşifre şarkısı anahtar akıcılığı gerektirir.',
                    ['mixed-clef','chromatic'], ['directional-hearing','clef-fluency']],
                [15, 'advanced',    10, [1,2,3,4,5,6,7,8,9,10,11,12], ['C','D','E','F','G','A','B'], '4', 'treble',
                    'Master: All Clefs, Full Range', 'The definitive directional hearing challenge — all clefs, all twelve intervals, extended note range. No interval should surprise you.',
                    'Usta Seviye: Tüm Anahtarlar', 'Nihai yönsel işitme zorluğu — tüm anahtarlar, tüm on iki aralık, genişletilmiş nota aralığı. Hiçbir aralık sizi şaşırtmamalı.',
                    ['master','all-clefs'], ['directional-hearing','mastery']],
            ];
            foreach ($lessons as $l) {
                $data[] = $this->makeDirection('interval-direction', $catId, $l);
            }
        }

        // ── HARMONIC INTERVALS ───────────────────────────────────────────────
        $catId = $categories['harmonic-intervals']->id ?? null;
        if ($catId) {
            $lessons = [
                [1, 'beginner',     5,  ['Perfect Unison','Perfect Octave'],           ['C','D','E','F','G'],        ['3','4'],
                    'Unison & Octave Together', 'Hear two notes played simultaneously at unison or an octave apart. The most stable and clear of all harmonic intervals.',
                    'Birlikte Unison ve Oktav', 'Unison veya bir oktav aralıkla aynı anda çalınan iki notayı dinleyin. Tüm harmonik aralıkların en stabil ve netti.',
                    ['unison','octave'], ['harmonic-hearing']],
                [2, 'beginner',     5,  ['Perfect 4th','Perfect 5th'],                 ['C','D','E','F','G'],        ['3','4'],
                    'Perfect 5th vs Perfect 4th', 'The hollow, open sound of perfect intervals played simultaneously. The 5th is the basis of power chords and early harmony.',
                    'Tam Beşli vs Tam Dörtlü', 'Aynı anda çalınan tam aralıkların boş, açık sesi. Beşli, güç akorlarının ve erken armoninin temelidir.',
                    ['perfect-intervals'], ['harmonic-hearing']],
                [3, 'beginner',     5,  ['Major 3rd','Minor 3rd'],                     ['C','D','E','F','G'],        ['3','4'],
                    'Major vs Minor 3rd', 'The major 3rd sounds warm and bright; the minor 3rd sounds darker and more melancholic. This distinction underpins major/minor tonality.',
                    'Büyük vs Küçük Üçlü', 'Büyük üçlü sıcak ve parlak; küçük üçlü daha koyu ve melankolik. Bu ayrım majör/minör tonaliteyi destekler.',
                    ['thirds','major-minor'], ['harmonic-hearing','chord-quality']],
                [4, 'beginner',     5,  ['Major 2nd','Minor 2nd'],                     ['C','D','E','F','G'],        ['3','4'],
                    'Seconds: Tone vs Semitone', 'Dissonant seconds played as harmonic intervals. The minor 2nd is harsher; the major 2nd is gentler but still clashes.',
                    'İkililer: Ton vs Yarı Ton', 'Harmonik aralık olarak çalınan disonant ikilileri. Küçük ikili daha sert; büyük ikili daha yumuşak ama yine de çarpışır.',
                    ['seconds','dissonance'], ['harmonic-hearing']],
                [5, 'beginner',     5,  ['Major 6th','Minor 6th'],                     ['C','D','E','F','G'],        ['3','4'],
                    'Major & Minor 6ths', 'Sixth intervals are sweeter than thirds when heard harmonically. They appear frequently in parallel harmonization.',
                    'Büyük ve Küçük Altılı', 'Altılı aralıklar harmonik olarak duyulduğunda üçlülerden daha tatlıdır. Paralel armonizasyonda sıkça görülürler.',
                    ['sixths'], ['harmonic-hearing']],
                [6, 'intermediate', 8,  ['Major 7th','Minor 7th'],                     ['C','D','E','F','G'],        ['3','4'],
                    '7ths: Tension vs Resolution', 'Seventh intervals create strong harmonic tension. The major 7th is highly dissonant; the minor 7th appears in dominant 7th chords.',
                    'Yedililer: Gerilim vs Çözülüm', 'Yedili aralıklar güçlü harmonik gerilim yaratır. Büyük yedili son derece disonant; küçük yedili dominant 7\'li akorlarda görünür.',
                    ['sevenths','tension'], ['harmonic-hearing','dissonance']],
                [7, 'intermediate', 8,  ['Augmented 4th'],                             ['C','D','F','G'],            ['3','4'],
                    'Tritone Identification', 'The tritone is the most dissonant harmonic interval. Its ambiguous quality makes it essential for jazz harmony and chromatic music.',
                    'Triton Tanıma', 'Triton en disonant harmonik aralıktır. Belirsiz kalitesi, caz armonisi ve kromatik müzik için onu vazgeçilmez kılar.',
                    ['tritone','dissonance'], ['harmonic-hearing']],
                [8, 'intermediate', 8,  ['Perfect Unison','Major 2nd','Minor 2nd','Major 3rd','Minor 3rd','Perfect 4th','Perfect 5th'], ['C','D','E','F','G'], ['3','4'],
                    'Diatonic Pool Mix', 'Mix of all diatonic intervals heard harmonically. Build fluency recognizing intervals as simultaneous sounds rather than sequences.',
                    'Diyatonik Karışık Havuz', 'Harmonik olarak duyulan tüm diyatonik aralıkların karışımı. Aralıkları sıra değil, eş zamanlı sesler olarak tanımada akıcılık geliştirin.',
                    ['diatonic','mixed'], ['harmonic-hearing']],
                [9, 'intermediate', 8,  ['Perfect Unison','Perfect 4th','Perfect 5th','Perfect Octave','Minor 2nd','Major 2nd','Augmented 4th','Minor 7th'], ['C','D','E','F','G'], ['3','4'],
                    'Consonance vs Dissonance', 'Deliberately contrast consonant intervals (unison, 4th, 5th, octave) against dissonant ones (2nds, tritone, 7th). Sharpens your harmonic perception.',
                    'Konsonans vs Disonans', 'Konsonant aralıkları (unison, 4., 5., oktav) disonant olanlarla (ikili, triton, 7.) bilinçli olarak karşılaştırın. Harmonik algınızı keskinleştirir.',
                    ['consonance','dissonance'], ['harmonic-hearing','perception']],
                [10, 'intermediate', 8, ['Major 6th','Minor 6th','Major 7th','Minor 7th'], ['C','D','E','F','G'],    ['3','4'],
                    'Wide Intervals: 6ths & 7ths', 'Focus on the upper register of harmonic intervals. 6ths and 7ths span a full octave minus one or two semitones.',
                    'Geniş Aralıklar', '6. ve 7. aralıklar harmonik aralıkların üst registerına odaklanın. Altılı ve yedililer tam bir oktav eksi bir veya iki yarım ton kapsar.',
                    ['sixths','sevenths','wide-intervals'], ['harmonic-hearing']],
                [11, 'advanced',    10, ['Minor 2nd','Major 2nd','Minor 3rd','Major 3rd','Perfect 4th','Augmented 4th','Diminished 5th','Perfect 5th','Minor 6th','Major 6th','Minor 7th','Major 7th'], ['C','D','E','F','G'], ['3','4'],
                    'Full 12-Interval Harmonic Set', 'All twelve intervals as harmonic dyads. The complete test of your harmonic interval recognition.',
                    '12 Aralık: Harmonik Tam Set', 'Harmonik ikililer olarak tüm on iki aralık. Harmonik aralık tanımanızın tam testi.',
                    ['chromatic','comprehensive'], ['harmonic-hearing','mastery']],
                [12, 'advanced',    10, ['Minor 2nd','Major 2nd','Minor 3rd','Major 3rd','Perfect 4th','Augmented 4th','Diminished 5th','Perfect 5th','Minor 6th','Major 6th','Minor 7th','Major 7th'], ['C','D','E','F','G'], ['3','4','5'],
                    'Close-Voiced Chromatic', 'All twelve harmonic intervals with varied octave placement. Intervals sound different depending on their registral position.',
                    'Yakın Ses Kromatik', 'Çeşitli oktav yerleşimleriyle tüm on iki harmonik aralık. Aralıklar, register pozisyonlarına bağlı olarak farklı ses çıkarır.',
                    ['close-voicing','chromatic'], ['harmonic-hearing','register-awareness']],
                [13, 'advanced',    10, ['Minor 2nd','Major 2nd','Minor 3rd','Major 3rd','Augmented 4th','Diminished 5th'], ['C','D','E','F','G','A','B'], ['3','4'],
                    'Chromatic Discrimination', 'The hardest discrimination task — differentiating intervals that are only one semitone apart when heard as harmonic dyads.',
                    'Kromatik Ayrım', 'En zor ayrım görevi — harmonik ikililer olarak duyulduğunda sadece bir yarım ton farklı olan aralıkları ayırt etmek.',
                    ['chromatic','close-intervals'], ['harmonic-hearing','fine-discrimination']],
                [14, 'advanced',    10, ['Perfect 5th','Perfect 4th','Minor 2nd','Major 7th','Augmented 4th'], ['C','D','E','F','G'], ['3','4'],
                    'Speed Drill: Consonant vs Dissonant', 'Fast alternation between highly consonant and highly dissonant harmonic intervals. Train immediate categorical perception.',
                    'Hız: Konsonant vs Disonant', 'Son derece konsonant ve son derece disonant harmonik aralıklar arasında hızlı geçiş. Anlık kategorik algı eğitimi yapın.',
                    ['speed','consonance','dissonance'], ['harmonic-hearing','speed-training']],
                [15, 'advanced',    10, ['Minor 2nd','Major 2nd','Minor 3rd','Major 3rd','Perfect 4th','Augmented 4th','Diminished 5th','Perfect 5th','Minor 6th','Major 6th','Minor 7th','Major 7th'], ['C','D','E','F','G','A','B'], ['2','3','4','5'],
                    'Master: Full Chromatic Harmonic', 'The ultimate harmonic interval challenge — all twelve intervals across a four-octave range. Mastery of harmonic hearing.',
                    'Usta: Tam Kromatik Harmonik', 'Nihai harmonik aralık zorluğu — dört oktav aralığında tüm on iki aralık. Harmonik işitmenin ustalığı.',
                    ['master','chromatic'], ['harmonic-hearing','mastery']],
            ];
            foreach ($lessons as $l) {
                $data[] = $this->makeInterval('harmonic-intervals', $catId, $l, 'harmonic-interval-practice');
            }
        }

        // ── INTERVAL CONSTRUCTION ────────────────────────────────────────────
        $catId = $categories['interval-construction']->id ?? null;
        if ($catId) {
            $lessons = [
                [1, 'beginner',  5,  ['Major 2nd'],  ['C','D','E'], '4',
                    'Build Major 2nds', 'Given a root note, identify the note that is a major 2nd (whole step) above it. Start with simple roots: C, D, and E.',
                    'Büyük İkili İnşa', 'Verilen bir kök notadan bir büyük ikili (tam adım) yukarıdaki notayı belirleyin. Basit köklerle başlayın: C, D ve E.',
                    ['major-2nd','construction'], ['interval-building']],
                [2, 'beginner',  5,  ['Minor 2nd'],  ['C','D','E','F','G'], '4',
                    'Build Minor 2nds', 'Identify the note a half step above the given root. Half steps occur between E–F and B–C on the piano.',
                    'Küçük İkili İnşa', 'Verilen kökten bir yarım adım yukarıdaki notayı belirleyin. Yarım adımlar piyanoda E–F ve B–C arasında oluşur.',
                    ['minor-2nd','half-step'], ['interval-building']],
                [3, 'beginner',  5,  ['Perfect 4th'], ['C','D','G'], '4',
                    'Build Perfect 4ths', 'Build perfect 4ths from selected root notes. C–F, D–G, G–C are the natural perfect 4ths used most in melodies.',
                    'Tam Dörtlü İnşa', 'Seçili kök notalardan tam dörtlüler oluşturun. C–F, D–G, G–C melodilerde en çok kullanılan doğal tam dörtlülerdir.',
                    ['perfect-4th','construction'], ['interval-building']],
                [4, 'beginner',  5,  ['Perfect 5th'], ['C','F','G'], '4',
                    'Build Perfect 5ths', 'Build perfect 5ths from common root notes. The perfect 5th is the basis of power chords and is the most stable consonance after the unison/octave.',
                    'Tam Beşli İnşa', 'Yaygın kök notalardan tam beşliler oluşturun. Tam beşli, güç akorlarının temelini oluşturur ve unison/oktav\'dan sonra en stabil konsonanstır.',
                    ['perfect-5th','construction'], ['interval-building']],
                [5, 'beginner',  5,  ['Major 3rd','Minor 3rd'], ['C','D','E','F','G','A','B'], '4',
                    'Build Major & Minor 3rds', 'Build both major and minor 3rds from any natural note. This reinforces the major/minor quality distinction at the construction level.',
                    'Büyük & Küçük Üçlü İnşa', 'Herhangi bir doğal notadan hem büyük hem de küçük üçlüler oluşturun. Bu, yapım düzeyinde majör/minör kalite ayrımını pekiştirir.',
                    ['thirds','construction'], ['interval-building','major-minor']],
                [6, 'intermediate', 8, ['Major 6th','Minor 6th'], ['C','D','E','F','G'], '4',
                    'Build Major & Minor 6ths', 'Build 6th intervals above given roots. Recall that a major 6th = inverted minor 3rd. Use interval inversion as a mental shortcut.',
                    'Büyük & Küçük Altılı İnşa', 'Verilen köklerin üzerinde altılı aralıklar oluşturun. Büyük altılı = tersçevrilmiş küçük üçlü. Zihinsel kısayol olarak aralık tersçevrimini kullanın.',
                    ['sixths','construction','inversions'], ['interval-building']],
                [7, 'intermediate', 8, ['Major 7th','Minor 7th'], ['C','D','E'], '4',
                    'Build Major & Minor 7ths', 'Build 7th intervals — these come one semitone below the octave (major 7th) or two semitones below (minor 7th).',
                    'Büyük & Küçük Yedili İnşa', 'Yedili aralıklar oluşturun — bunlar oktavın bir yarım ton (büyük yedili) veya iki yarım ton (küçük yedili) altında gelir.',
                    ['sevenths','construction'], ['interval-building']],
                [8, 'intermediate', 8, ['Augmented 4th'], ['C','F','G'], '4',
                    'Build the Tritone', 'Build the augmented 4th (tritone) — exactly 6 semitones above the root. It divides the octave symmetrically.',
                    'Triton İnşa', 'Artık dörtlü (triton) oluşturun — tam 6 yarım ton kökün üzerinde. Oktavı simetrik olarak böler.',
                    ['tritone','construction'], ['interval-building','chromatic']],
                [9, 'intermediate', 8, ['Minor 2nd','Major 2nd','Minor 3rd','Major 3rd','Perfect 4th'], ['C','D','E','F','G','A','B'], '4',
                    '2nds–4ths from Any Natural Note', 'Build any interval from minor 2nd through perfect 4th starting from any natural note. Builds comprehensive lower-interval fluency.',
                    '2.–4. Aralıklar: Herhangi Doğal Nota', 'Herhangi bir doğal notadan başlayarak küçük ikilic den tam dörtlüye kadar herhangi bir aralık oluşturun.',
                    ['lower-intervals','construction'], ['interval-building','comprehensive']],
                [10, 'intermediate', 8, ['Perfect 5th','Minor 6th','Major 6th','Minor 7th','Major 7th'], ['C','D','E','F','G','A','B'], '4',
                    '5ths–7ths from Any Natural Note', 'Build intervals from perfect 5th through major 7th from any natural note. Master the upper half of the interval spectrum.',
                    '5.–7. Aralıklar: Herhangi Doğal Nota', 'Herhangi bir doğal notadan tam beşli\'den büyük yediliye kadar aralıklar oluşturun. Aralık spektrumunun üst yarısında ustalaşın.',
                    ['upper-intervals','construction'], ['interval-building','comprehensive']],
                [11, 'advanced', 10, ['Major 2nd','Minor 3rd','Perfect 4th','Perfect 5th'], ['C#','F#','G#'], '4',
                    'Build from Sharps', 'Build intervals starting from sharp root notes. Accidentals require extra attention to avoid enharmonic confusion.',
                    'Diyezli Notalardan İnşa', 'Diyezli kök notalardan aralıklar oluşturun. Arızalar, enarmonik karışıklığı önlemek için ekstra dikkat gerektirir.',
                    ['sharps','accidentals','construction'], ['interval-building','chromatic-roots']],
                [12, 'advanced', 10, ['Major 2nd','Minor 3rd','Perfect 4th','Perfect 5th'], ['Bb','Eb'], '4',
                    'Build from Flats', 'Build intervals starting from flat root notes. Flat keys are common in band and orchestral music.',
                    'Bemollü Notalardan İnşa', 'Bemollü kök notalardan aralıklar oluşturun. Bemol tonaliteler orkestra ve bando müziğinde yaygındır.',
                    ['flats','accidentals','construction'], ['interval-building','chromatic-roots']],
                [13, 'advanced', 10, ['Minor 2nd','Major 2nd','Minor 3rd','Major 3rd','Perfect 4th','Augmented 4th','Diminished 5th','Perfect 5th','Minor 6th','Major 6th','Minor 7th','Major 7th'], ['C','D','E','F','G','A','B'], '4',
                    'Full 12-Interval Construction', 'Build any of the twelve chromatic intervals from natural root notes. Complete construction mastery.',
                    '12 Aralık: Tam İnşa', 'Doğal kök notalardan on iki kromatik aralığın herhangi birini oluşturun. Tam yapım ustalığı.',
                    ['chromatic','comprehensive','construction'], ['interval-building','mastery']],
                [14, 'advanced', 10, ['Minor 2nd','Major 2nd','Minor 3rd','Major 3rd','Perfect 4th','Augmented 4th','Diminished 5th','Perfect 5th','Minor 6th','Major 6th','Minor 7th','Major 7th'], ['C','C#','D','Eb','E','F','F#','G','Ab','A','Bb','B'], '4',
                    'Construction with Accidentals', 'Build all twelve intervals starting from any chromatic root note. This is the professional-level construction challenge.',
                    'Arızalı Notalarla İnşa', 'Herhangi bir kromatik kök notadan tüm on iki aralığı oluşturun. Bu, profesyonel seviye yapım zorluğudur.',
                    ['chromatic-roots','all-intervals'], ['interval-building','mastery']],
                [15, 'advanced', 10, ['Minor 2nd','Major 2nd','Minor 3rd','Major 3rd','Perfect 4th','Augmented 4th','Diminished 5th','Perfect 5th','Minor 6th','Major 6th','Minor 7th','Major 7th'], ['C','C#','D','Eb','E','F','F#','G','Ab','A','Bb','B'], '4',
                    'Master: Any Root, Any Interval', 'The ultimate construction challenge — any of the twelve intervals from any of the twelve chromatic root notes. 144 possible combinations.',
                    'Usta: Herhangi Kök, Herhangi Aralık', 'Nihai yapım zorluğu — on iki kromatik kök notanın herhangi birinden on iki aralığın herhangi biri. 144 olası kombinasyon.',
                    ['master','chromatic-roots','all-intervals'], ['interval-building','mastery']],
            ];
            foreach ($lessons as $l) {
                $data[] = $this->makeConstruction('interval-construction', $catId, $l);
            }
        }

        // ── INTERVAL COMPARISON ──────────────────────────────────────────────
        $catId = $categories['interval-comparison']->id ?? null;
        if ($catId) {
            $compLessons = [
                [1, 'beginner',  5,  [['C,D','C,E'],['D,E','D,F#'],['E,F#','E,G#']],
                    '2nd vs 3rd: Which is Larger?', 'Compare a 2nd and a 3rd. The 3rd is always larger. This builds your sense of interval size hierarchy.',
                    '2. vs 3.: Hangisi Büyük?', 'Bir ikili ve bir üçlüyü karşılaştırın. Üçlü her zaman büyüktür. Bu, aralık büyüklüğü hiyerarşisi duygusunuzu geliştirir.',
                    ['seconds','thirds'], ['comparison','interval-size']],
                [2, 'beginner',  5,  [['C,E','C,F'],['D,F#','D,G'],['G,B','G,C5']],
                    '3rd vs 4th', 'Compare a 3rd and a 4th. The 4th is larger. Practice hearing the slight size difference between these adjacent interval categories.',
                    '3. vs 4.', 'Bir üçlü ve bir dörtlüyü karşılaştırın. Dörtlü daha büyüktür. Bu bitişik aralık kategorileri arasındaki küçük boyut farkını duymayı pratik yapın.',
                    ['thirds','fourths'], ['comparison','interval-size']],
                [3, 'beginner',  5,  [['C,F','C,G'],['D,G','D,A'],['F,Bb','F,C5']],
                    '4th vs 5th', 'Compare a 4th and a 5th. The 5th is one tone larger. These are the most important open intervals to distinguish.',
                    '4. vs 5.', 'Bir dörtlü ve bir beşliyi karşılaştırın. Beşli bir ton daha büyüktür. Bunlar ayırt edilecek en önemli açık aralıklardır.',
                    ['fourths','fifths'], ['comparison','interval-size']],
                [4, 'beginner',  5,  [['C,D','C,G'],['E,F','E,B'],['G,A','G,D5']],
                    '2nd vs 5th (Wide Contrast)', 'Compare intervals that are far apart in size. Wide contrasts are easier to judge — use these to build confidence.',
                    '2. vs 5. (Geniş Kontrast)', 'Boyut olarak birbirinden uzak aralıkları karşılaştırın. Geniş kontrastlar değerlendirmesi daha kolaydır — güven oluşturmak için bunları kullanın.',
                    ['wide-contrast'], ['comparison','confidence-building']],
                [5, 'beginner',  5,  [['C,E','C,Eb'],['D,F#','D,F'],['G,B','G,Bb']],
                    'Major vs Minor 3rd (Close Call)', 'Compare a major 3rd against a minor 3rd. This one-semitone difference demands careful listening.',
                    'Büyük vs Küçük Üçlü', 'Bir büyük üçlüyü küçük üçlüyle karşılaştırın. Bu bir yarım tonluk fark, dikkatli dinleme gerektirir.',
                    ['thirds','close-comparison'], ['comparison','fine-discrimination']],
                [6, 'intermediate', 8, [['C,G','C,A'],['D,A','D,B'],['F,C5','F,D5']],
                    '5th vs 6th', 'Compare a 5th and a 6th. The 6th is a tone larger. Practice distinguishing these commonly used intervals.',
                    '5. vs 6.', 'Bir beşli ve bir altılıyı karşılaştırın. Altılı bir ton daha büyüktür. Bu yaygın olarak kullanılan aralıkları ayırt etmeyi pratik yapın.',
                    ['fifths','sixths'], ['comparison','interval-size']],
                [7, 'intermediate', 8, [['C,A','C,Ab'],['D,B','D,Bb'],['E,C#5','E,C5']],
                    'Major vs Minor 6th', 'Compare a major 6th against a minor 6th — a one-semitone difference. Notoriously tricky for many students.',
                    'Büyük vs Küçük Altılı', 'Bir büyük altılıyı küçük altılıyla karşılaştırın — bir yarım tonluk fark. Birçok öğrenci için ünlü biçimde zor.',
                    ['sixths','close-comparison'], ['comparison','fine-discrimination']],
                [8, 'intermediate', 8, [['C,A','C,B'],['D,B','D,C#5'],['F,D5','F,Eb5']],
                    '6th vs 7th', 'Compare a 6th and a 7th. The 7th is larger and creates stronger tension.',
                    '6. vs 7.', 'Bir altılı ve bir yediliyi karşılaştırın. Yedili daha büyüktür ve daha güçlü gerilim yaratır.',
                    ['sixths','sevenths'], ['comparison','interval-size']],
                [9, 'intermediate', 8, [['C,D','C,C#'],['E,F#','E,F'],['G,A','G,Ab']],
                    'Major vs Minor 2nd', 'Compare whole step vs half step — the most fundamental interval size discrimination in music.',
                    'Büyük vs Küçük İkili', 'Tam adım ve yarım adımı karşılaştırın — müzikte en temel aralık boyutu ayrımı.',
                    ['seconds','half-step','whole-step'], ['comparison','fine-discrimination']],
                [10, 'intermediate', 8, [['C,F#','C,F'],['D,G#','D,G'],['G,C#5','G,C5']],
                    'Tritone vs Perfect 4th', 'Compare augmented 4th (tritone) vs perfect 4th — just one semitone apart. The tritone sounds restless; the 4th sounds stable.',
                    'Triton vs Tam Dörtlü', 'Artık dörtlü (triton) ile tam dörtlüyü karşılaştırın — sadece bir yarım ton fark. Triton huzursuz; dörtlü stabil gelir.',
                    ['tritone','perfect-4th','close-comparison'], ['comparison','fine-discrimination']],
                [11, 'advanced', 10, [['C,B','C,C5'],['D,C#5','D,D5'],['G,F#5','G,G5']],
                    '7th vs Octave', 'Compare a 7th and an octave — the final one or two semitones to the top. The octave sounds complete; the 7th sounds incomplete.',
                    '7. vs Oktav', 'Bir yedili ve bir oktavı karşılaştırın — zirveye giden son bir veya iki yarım ton. Oktav tam; yedili eksik gelir.',
                    ['sevenths','octave'], ['comparison','interval-size']],
                [12, 'advanced', 10, [['C,Bb','C,B'],['D,C5','D,C#5'],['F,Eb5','F,E5']],
                    'Minor 7th vs Major 7th', 'Compare minor and major 7th — the subtlest comparison in the upper register. One semitone difference, very close to the octave.',
                    'Küçük 7. vs Büyük 7.', 'Küçük ve büyük yediliyi karşılaştırın — üst registerde en ince karşılaştırma. Bir yarım ton fark, oktava çok yakın.',
                    ['sevenths','close-comparison'], ['comparison','fine-discrimination']],
                [13, 'advanced', 10, [['C,C#','C,D'],['C,Eb','C,E'],['C,F','C,F#'],['C,G','C,Ab'],['C,A','C,Bb']],
                    'Chromatic Pairs: Semitone Diffs', 'Compare pairs that are only one semitone apart throughout the chromatic scale. Maximum discrimination challenge.',
                    'Kromatik Çiftler: Yarıton Farklar', 'Kromatik gam boyunca sadece bir yarım ton farklı olan çiftleri karşılaştırın. Maksimum ayrım zorluğu.',
                    ['chromatic','close-comparison'], ['comparison','fine-discrimination']],
                [14, 'advanced', 10, [['C,D','C,G'],['C,E','C,A'],['C,F','C,B'],['C,Eb','C,Ab'],['C,F#','C,C5']],
                    'Speed Drill: All Common Pairs', 'Fast comparison across a wide variety of interval pairs. Build rapid, automatic judgement.',
                    'Hız: Tüm Yaygın Çiftler', 'Çok çeşitli aralık çiftleri arasında hızlı karşılaştırma. Hızlı, otomatik yargı geliştirin.',
                    ['speed','mixed'], ['comparison','speed-training']],
                [15, 'advanced', 10, [['C,C#','C,Bb'],['C,D','C,A'],['C,Eb','C,Ab'],['C,E','C,G'],['C,F','C,F#'],['C,Gb','C,B']],
                    'Master: Any Two Intervals', 'The ultimate comparison challenge — any combination of intervals from the full chromatic pool. True mastery of interval size perception.',
                    'Usta: Herhangi İki Aralık', 'Nihai karşılaştırma zorluğu — tam kromatik havuzdan herhangi bir aralık kombinasyonu. Aralık boyutu algısının gerçek ustalığı.',
                    ['master','comprehensive'], ['comparison','mastery']],
            ];
            foreach ($compLessons as $l) {
                $data[] = $this->makeComparison('interval-comparison', $catId, $l);
            }
        }

        // ── SCALES & MODES ───────────────────────────────────────────────────
        $catId = $categories['scales-modes']->id ?? null;
        if ($catId) {
            $scaleLessons = [
                [1, 'beginner',  5,  ['major'],               ['C','G','F'],  'ascending',  ['natural-minor','dorian','pentatonic-major'],
                    'Major Scale (C, G, F)', 'Identify the major scale — the most common scale in Western music. Its bright, happy character comes from the raised 3rd, 6th, and 7th degrees.',
                    'Majör Gam (C, G, F)', 'Batı müziğindeki en yaygın gam olan majör gamı tanıyın. Parlak, neşeli karakteri yükseltilmiş 3., 6. ve 7. derecelerden gelir.',
                    ['major-scale'], ['scale-recognition']],
                [2, 'beginner',  5,  ['natural-minor'],       ['A','E','D'],  'ascending',  ['major','dorian','harmonic-minor'],
                    'Natural Minor Scale', 'The natural minor scale — darker and more melancholic than major. Notice the lowered 3rd, 6th, and 7th degrees compared to major.',
                    'Doğal Minör Gam', 'Doğal minör gam — majörden daha koyu ve melankolik. Majöre kıyasla düşürülmüş 3., 6. ve 7. derecelere dikkat edin.',
                    ['natural-minor'], ['scale-recognition']],
                [3, 'beginner',  5,  ['major','natural-minor'], ['C','G','A','D'], 'ascending', ['dorian','harmonic-minor','mixolydian'],
                    'Major vs Natural Minor', 'The fundamental scale distinction in Western music. One scale sounds bright; the other dark. Train this categorical difference deeply.',
                    'Majör vs Doğal Minör', 'Batı müziğindeki temel gam ayrımı. Bir gam parlak; diğeri koyu gelir. Bu kategorik farkı derinlemesine eğitin.',
                    ['major','natural-minor','comparison'], ['scale-recognition','major-minor']],
                [4, 'beginner',  5,  ['harmonic-minor'],      ['A','D','E'],  'ascending',  ['natural-minor','major','melodic-minor'],
                    'Harmonic Minor Scale', 'The harmonic minor has a raised 7th degree, creating an augmented 2nd between the 6th and 7th — this exotic, tense sound is its hallmark.',
                    'Harmonik Minör Gam', 'Harmonik minörün yükseltilmiş 7. derecesi var ve 6. ile 7. arasında artık bir ikili yaratıyor — bu egzotik, gergin ses onun özelliğidir.',
                    ['harmonic-minor'], ['scale-recognition']],
                [5, 'beginner',  5,  ['melodic-minor'],       ['A','D','G'],  'ascending',  ['harmonic-minor','natural-minor','dorian'],
                    'Melodic Minor Scale', 'The melodic minor raises both the 6th and 7th degrees when ascending — smoother than harmonic minor, with a unique jazz quality.',
                    'Melodik Minör Gam', 'Melodik minör, çıkarken hem 6. hem de 7. dereceyi yükseltir — harmonik minörden daha akıcı, benzersiz bir caz kalitesiyle.',
                    ['melodic-minor'], ['scale-recognition']],
                [6, 'intermediate', 8, ['dorian'], ['D','G','A'], 'ascending', ['natural-minor','mixolydian','phrygian'],
                    'Dorian Mode', 'Dorian is a minor mode with a raised 6th. It has a unique "cool minor" quality used in jazz and rock — think of "So What" by Miles Davis.',
                    'Dorian Modu', 'Dorian, yükseltilmiş 6. dereceyle bir minör moddur. Caz ve rock\'ta kullanılan benzersiz bir "serin minör" kalitesine sahiptir.',
                    ['dorian','modes'], ['scale-recognition','modal']],
                [7, 'intermediate', 8, ['phrygian'], ['E','A','B'], 'ascending', ['natural-minor','dorian','locrian'],
                    'Phrygian Mode', 'Phrygian has a lowered 2nd degree — its dark, Spanish-flamenco sound is instantly recognizable. Common in metal and flamenco.',
                    'Frig Modu', 'Frig\'in düşürülmüş 2. derecesi var — koyu, İspanyol-flamenko sesi anında tanınabilir. Metal ve flamenkoda yaygın.',
                    ['phrygian','modes'], ['scale-recognition','modal']],
                [8, 'intermediate', 8, ['lydian'], ['F','C','G'], 'ascending', ['major','mixolydian','dorian'],
                    'Lydian Mode', 'Lydian is a major mode with a raised 4th — giving it a dreamy, floating quality. Used extensively in film music and progressive rock.',
                    'Lidya Modu', 'Lidya, yükseltilmiş 4. dereceyle bir majör moddur — ona rüya gibi, yüzen bir kalite verir. Film müziği ve prog rock\'ta yaygın kullanılır.',
                    ['lydian','modes'], ['scale-recognition','modal']],
                [9, 'intermediate', 8, ['mixolydian'], ['G','D','C'], 'ascending', ['major','dorian','lydian'],
                    'Mixolydian Mode', 'Mixolydian is a major mode with a lowered 7th — the "bluesy major" sound. Fundamental in blues, rock, and folk music.',
                    'Miksolidya Modu', 'Miksolidya, düşürülmüş 7. dereceyle bir majör moddur — "bluesy majör" sesi. Blues, rock ve folk müziğinde temel.',
                    ['mixolydian','modes'], ['scale-recognition','modal']],
                [10, 'intermediate', 8, ['aeolian','locrian'], ['A','B','D'], 'ascending', ['natural-minor','phrygian','dorian'],
                    'Aeolian & Locrian Modes', 'Aeolian = natural minor; Locrian has a lowered 2nd AND 5th — the only mode with a diminished fifth as its tonic triad.',
                    'Aeolian ve Lokriya Modları', 'Aeolian = doğal minör; Locrian\'ın hem 2. hem de 5. derecesi düşürülmüş — tonik triolu olarak eksik beşliyle tek mod.',
                    ['aeolian','locrian','modes'], ['scale-recognition','modal']],
                [11, 'advanced', 10, ['major','natural-minor','harmonic-minor','melodic-minor'], ['C','D','E','G','A'], 'ascending', ['dorian','phrygian','mixolydian'],
                    'Major vs All Minor Forms', 'Compare the four main scale types — major against all three minor variants. Distinguishing subtle differences requires deep listening.',
                    'Majör vs Tüm Minör Formlar', 'Dört ana gam türünü karşılaştırın — majöre karşı üç minör varyantın tamamı. İnce farkları ayırt etmek derin dinleme gerektirir.',
                    ['major','minor-forms','comparison'], ['scale-recognition','discrimination']],
                [12, 'advanced', 10, ['major','dorian','phrygian','lydian','mixolydian','aeolian','locrian'], ['C','D','E','F','G'], 'ascending', ['harmonic-minor','pentatonic-major','blues'],
                    'All Church Modes vs Major', 'Identify all seven church modes — the full modal system that forms the basis of Western music theory.',
                    'Tüm Kilise Modları vs Majör', 'Yedi kilise modunun tamamını tanıyın — Batı müzik teorisinin temelini oluşturan tam modal sistem.',
                    ['modes','comprehensive'], ['scale-recognition','modal-mastery']],
                [13, 'advanced', 10, ['pentatonic-major','pentatonic-minor'], ['C','G','D','A'], 'ascending', ['major','natural-minor','blues'],
                    'Pentatonic & Blues Scales', 'The pentatonic and blues scales are the most universally used scales in popular music worldwide. Recognize their open, simple quality.',
                    'Pentatonik ve Blues Gamları', 'Pentatonik ve blues gamları, dünya çapında pop müzikte en evrensel kullanılan gamlardır. Açık, basit kalitelerini tanıyın.',
                    ['pentatonic','blues'], ['scale-recognition','popular-music']],
                [14, 'advanced', 10, ['major','natural-minor','harmonic-minor','melodic-minor','dorian','phrygian','lydian','mixolydian','pentatonic-major'], ['C','D','G','A','E'], 'ascending', ['locrian','blues','aeolian'],
                    'Full Modal Pool', 'Identify any scale from a pool of nine scale and mode types. Comprehensive modal fluency.',
                    'Tam Modal Havuz', 'Dokuz gam ve mod türünden oluşan bir havuzdan herhangi bir gamı tanıyın. Kapsamlı modal akıcılık.',
                    ['comprehensive','modal','pool'], ['scale-recognition','mastery']],
                [15, 'advanced', 10, ['major','natural-minor','harmonic-minor','melodic-minor','dorian','phrygian','lydian','mixolydian','aeolian','locrian','pentatonic-major','pentatonic-minor'], ['C','D','E','F','G','A','B'], 'ascending', ['blues'],
                    'Master: All Scales, Any Root', 'The definitive scale and mode identification challenge — all 12 scale types from any of the 7 natural root notes.',
                    'Usta: Tüm Gamlar, Herhangi Kök', 'Nihai gam ve mod tanıma zorluğu — 7 doğal kök notanın herhangi birinden 12 gam türünün tamamı.',
                    ['master','all-scales','any-root'], ['scale-recognition','mastery']],
            ];
            foreach ($scaleLessons as $l) {
                $data[] = $this->makeScale('scales-modes', $catId, $l);
            }
        }

        // ── CHORDS ───────────────────────────────────────────────────────────
        $catId = $categories['chords']->id ?? null;
        if ($catId) {
            $chordLessons = [
                [1, 'beginner',  5,  ['major'],             ['C','G','F'],      'block',       false, ['minor','augmented','diminished'],
                    'Major Triads (Block)', 'Identify block major triads. The major triad is the brightest, most stable chord — the foundation of tonal harmony.',
                    'Majör Üçlü Akorlar (Blok)', 'Blok majör üçlüleri tanıyın. Majör üçlü, en parlak ve stabil akor — tonal armoninin temelidir.',
                    ['major-triad','block'], ['chord-recognition']],
                [2, 'beginner',  5,  ['minor'],             ['A','D','E'],      'block',       false, ['major','diminished','augmented'],
                    'Minor Triads (Block)', 'Identify block minor triads. The minor triad has a darker, more introspective quality compared to the major.',
                    'Minör Üçlü Akorlar (Blok)', 'Blok minör üçlüleri tanıyın. Minör üçlü, majöre kıyasla daha koyu, içe dönük bir kaliteye sahiptir.',
                    ['minor-triad','block'], ['chord-recognition']],
                [3, 'beginner',  5,  ['major','minor'],     ['C','D','E','G','A'], 'block',    false, ['augmented','diminished'],
                    'Major vs Minor', 'The most fundamental chord quality distinction. Train yourself to instantly identify happy vs sad chord quality.',
                    'Majör vs Minör', 'En temel akor kalitesi ayrımı. Kendinizi neşeli ve üzgün akor kalitesini anında tanımak için eğitin.',
                    ['major','minor','comparison'], ['chord-recognition','quality-distinction']],
                [4, 'beginner',  5,  ['augmented'],         ['C','D','E'],      'block',       false, ['major','minor','diminished'],
                    'Augmented Triads', 'The augmented triad is unsettling and tense — it consists of two stacked major 3rds. Rare in common practice, essential in 20th-century music.',
                    'Arttırılmış Üçlüler', 'Artırılmış üçlü rahatsız edici ve gergindir — iki üst üste bindirilmiş büyük üçlüden oluşur. Klasik müzikte nadir, 20. yüzyıl müziğinde temel.',
                    ['augmented','dissonance'], ['chord-recognition']],
                [5, 'beginner',  5,  ['diminished'],        ['B','D','F'],      'block',       false, ['minor','major','augmented'],
                    'Diminished Triads', 'The diminished triad consists of two minor 3rds — small, dark, tense. It occurs naturally on the 7th degree of the major scale.',
                    'Azaltılmış Üçlüler', 'Azaltılmış üçlü iki küçük üçlüden oluşur — küçük, koyu, gergin. Doğal olarak majör gamın 7. derecesinde oluşur.',
                    ['diminished','dissonance'], ['chord-recognition']],
                [6, 'intermediate', 8, ['major','minor','augmented','diminished'], ['C','D','E','G','A'], 'block', false, [],
                    'All Four Triads', 'Identify all four triadic qualities: major, minor, augmented, and diminished. The complete basic vocabulary of tonal harmony.',
                    'Dört Üçlü Akor Türü', 'Dört üçlü kaliteyi tanıyın: majör, minör, artırılmış ve azaltılmış. Tonal armoninin tam temel söz dağarcığı.',
                    ['triads','comprehensive'], ['chord-recognition','quality-discrimination']],
                [7, 'intermediate', 8, ['major','minor'], ['C','D','E','F','G','A'], 'arpeggiated', false, ['augmented','diminished'],
                    'Arpeggiated Major & Minor', 'Identify major and minor triads played as arpeggios. Arpeggiation reveals the chord quality through the sequence of individual notes.',
                    'Arpejli Majör ve Minör', 'Arpej olarak çalınan majör ve minör üçlüleri tanıyın. Arpejleme, bireysel notaların dizisi aracılığıyla akor kalitesini ortaya çıkarır.',
                    ['arpeggiated','major','minor'], ['chord-recognition']],
                [8, 'intermediate', 8, ['dominant7'], ['C','G','F','D'], 'block', false, ['major7','minor7','major'],
                    'Dominant 7th Chords', 'The dominant 7th chord (major triad + minor 7th) creates strong tension toward resolution. The most important 7th chord in tonal music.',
                    'Dominant 7\'li Akorlar', 'Dominant 7\'li akor (majör üçlü + küçük 7.) çözünmeye doğru güçlü gerilim yaratır. Tonal müzikte en önemli 7\'li akor.',
                    ['dominant-7th','tension'], ['chord-recognition','seventh-chords']],
                [9, 'intermediate', 8, ['major7'], ['C','F','G'], 'arpeggiated', false, ['minor7','dominant7','major'],
                    'Major 7th Chords', 'The major 7th chord has a lush, dreamy quality — major triad with a major 7th added. Hallmark of jazz ballads and neo-soul.',
                    'Majör 7\'li Akorlar', 'Majör 7\'li akor zengin, rüyamsı bir kaliteye sahip — eklenen majör 7. ile majör üçlü. Caz bale ve neo-soul\'un göstergesi.',
                    ['major-7th','jazz'], ['chord-recognition','seventh-chords']],
                [10, 'intermediate', 8, ['minor7'], ['A','D','E'], 'block', false, ['major7','dominant7','minor'],
                    'Minor 7th Chords', 'The minor 7th chord (minor triad + minor 7th) is smooth and versatile — fundamental to jazz, R&B, and funk.',
                    'Minör 7\'li Akorlar', 'Minör 7\'li akor (minör üçlü + küçük 7.) pürüzsüz ve çok yönlüdür — caz, R&B ve funk\'ın temelidir.',
                    ['minor-7th','jazz'], ['chord-recognition','seventh-chords']],
                [11, 'advanced', 10, ['dominant7','major7','minor7','half-diminished7'], ['C','D','G','A'], 'block', false, [],
                    'All Four 7th Chords', 'Identify dominant 7th, major 7th, minor 7th, and half-diminished 7th. The complete 7th chord vocabulary essential for jazz.',
                    'Dört 7\'li Akor Türü', 'Dominant 7., majör 7., minör 7. ve yarı azaltılmış 7.\'yi tanıyın. Caz için temel olan tam 7\'li akor söz dağarcığı.',
                    ['seventh-chords','comprehensive'], ['chord-recognition','jazz-harmony']],
                [12, 'advanced', 10, ['major','minor'], ['C','D','E','F','G'], 'block', true, ['augmented','diminished','major7'],
                    'First Inversion Triads', 'Identify major and minor triads in first inversion — the 3rd in the bass. Inversions alter the color and stability of a chord.',
                    'Birinci Çevrim Üçlüler', 'Birinci çevrimde majör ve minör üçlüleri tanıyın — basta 3. derece. Çevrimler, bir akorun rengini ve stabilitesini değiştirir.',
                    ['inversions','first-inversion'], ['chord-recognition','voice-leading']],
                [13, 'advanced', 10, ['major','minor'], ['C','D','E','F','G'], 'block', true, ['augmented','dominant7','major7'],
                    'Second Inversion Triads', 'Identify major and minor triads in second inversion — the 5th in the bass. The 6/4 chord is the most unstable triad inversion.',
                    'İkinci Çevrim Üçlüler', 'İkinci çevrimde majör ve minör üçlüleri tanıyın — basta 5. derece. 6/4 akoru en instabil üçlü çevrimidir.',
                    ['inversions','second-inversion'], ['chord-recognition','voice-leading']],
                [14, 'advanced', 10, ['major','minor','augmented','diminished','dominant7','major7','minor7'], ['C','D','E','G'], 'block', true, [],
                    'Mixed Inversions & 7ths', 'Identify any triad or 7th chord in any inversion. Combines chord quality recognition with inversion identification.',
                    'Karma Çevrim ve Yedililer', 'Herhangi bir çevrimde herhangi bir üçlü veya 7\'li akoru tanıyın. Akor kalitesi tanımasını çevrim tanımayla birleştirir.',
                    ['inversions','seventh-chords','mixed'], ['chord-recognition','advanced-harmony']],
                [15, 'advanced', 10, ['major','minor','augmented','diminished','dominant7','major7','minor7','half-diminished7'], ['C','C#','D','Eb','E','F','G','A'], 'block', true, [],
                    'Master: All Chords, Any Root', 'The ultimate chord recognition challenge — all 8 chord types, all possible inversions, from any root note. Professional-level harmonic hearing.',
                    'Usta: Tüm Akorlar, Herhangi Kök', 'Nihai akor tanıma zorluğu — 8 akor türü, tüm olası çevrimler, herhangi bir kök notadan. Profesyonel seviye harmonik işitme.',
                    ['master','comprehensive','any-root'], ['chord-recognition','mastery']],
            ];
            foreach ($chordLessons as $l) {
                $data[] = $this->makeChord('chords', $catId, $l);
            }
        }

        // ── RHYTHM ───────────────────────────────────────────────────────────
        $catId = $categories['rhythm']->id ?? null;
        if ($catId) {
            $rhythmLessons = [
                [1, 'beginner',  5,  '4/4', ['quarter'],                           80,  1,
                    'Quarter Notes Only (4/4)', 'All quarter notes in 4/4 time. The simplest possible rhythm — four even beats per measure. Focus on feeling the pulse.',
                    'Yalnızca Dörtlük Notalar', '4/4\'lük zamanda tüm dörtlük notalar. Mümkün olan en basit ritim — ölçü başına dört eşit vuruş. Nabzı hissetmeye odaklanın.',
                    ['quarter-notes','4/4'], ['rhythm-recognition','pulse']],
                [2, 'beginner',  5,  '4/4', ['quarter','half'],                    76,  1,
                    'Half Notes & Quarter Notes', 'Mix quarter and half notes. The half note lasts twice as long — practice feeling duration differences.',
                    'Yarım ve Dörtlük Notalar', 'Dörtlük ve yarım notaları karıştırın. Yarım nota iki kat daha uzun sürer — süre farklarını hissetmeyi pratik yapın.',
                    ['quarter-notes','half-notes','4/4'], ['rhythm-recognition','duration']],
                [3, 'beginner',  5,  '4/4', ['whole','half'],                      72,  1,
                    'Whole Notes Introduction', 'Introduce the whole note — four beats of duration. Contrast with half notes to feel the difference.',
                    'Tam Nota Giriş', 'Dört vuruş süren tam notayı tanıtın. Farkı hissetmek için yarım notalarla karşılaştırın.',
                    ['whole-notes','half-notes'], ['rhythm-recognition','long-durations']],
                [4, 'beginner',  5,  '3/4', ['quarter','dotted-half'],             88,  1,
                    'Waltz Rhythms (3/4)', 'Three beats per measure — the waltz feel. The dotted half note fills an entire 3/4 measure.',
                    'Vals Ritimleri (3/4)', 'Ölçü başına üç vuruş — vals hissi. Noktalı yarım nota tüm bir 3/4 ölçüsünü doldurur.',
                    ['3/4','waltz','dotted-rhythms'], ['rhythm-recognition','meter']],
                [5, 'beginner',  5,  '4/4', ['quarter','eighth'],                  80,  1,
                    'Eighth Notes Introduction', 'Eighth notes divide each beat in two. Feel the "1-and-2-and" subdivision.',
                    'Sekizlik Nota Giriş', 'Sekizlik notalar her vuruşu ikiye böler. "1-ve-2-ve" alt bölümünü hissedin.',
                    ['eighth-notes','subdivision'], ['rhythm-recognition','subdivision']],
                [6, 'intermediate', 8, '4/4', ['quarter','eighth','half'],         80,  1,
                    'Mixed Quarters & Eighths', 'Combine quarter, eighth, and half notes in 4/4. The most common rhythm combinations in popular music.',
                    'Karışık Dörtlük & Sekizlik', '4/4\'te dörtlük, sekizlik ve yarım notaları birleştirin. Pop müziğinde en yaygın ritim kombinasyonları.',
                    ['mixed-notes','4/4'], ['rhythm-recognition','combination']],
                [7, 'intermediate', 8, '4/4', ['quarter','quarter_rest'],          80,  1,
                    'Quarter Rests', 'A rest is silence — it is as musical as a note. Quarter rests punctuate the rhythm and create groove.',
                    'Dörtlük Suslar', 'Sus sessizliktir — bir nota kadar müzikaldir. Dörtlük suslar ritmi noktalandırır ve groove yaratır.',
                    ['quarter-rest','silence'], ['rhythm-recognition','rests']],
                [8, 'intermediate', 8, '4/4', ['eighth','eighth_rest'],            80,  1,
                    'Eighth Rests', 'Eighth rests create off-beat accents and syncopation. Common in funk, jazz, and Latin rhythms.',
                    'Sekizlik Suslar', 'Sekizlik suslar zayıf vuruş aksanları ve senkop yaratır. Funk, caz ve Latin ritimlerinde yaygın.',
                    ['eighth-rest','off-beat'], ['rhythm-recognition','rests','syncopation']],
                [9, 'intermediate', 8, '2/4', ['quarter','eighth'],                100, 1,
                    'March Rhythms (2/4)', 'Two beats per measure — the march feel. Crisp and military in character.',
                    'Marş Ritimleri (2/4)', 'Ölçü başına iki vuruş — marş hissi. Keskin ve askeri karakterde.',
                    ['2/4','march'], ['rhythm-recognition','meter']],
                [10, 'intermediate', 8, '6/8', ['eighth'],                         120, 1,
                    'Compound Meter (6/8)', '6/8 has six eighth notes grouped as two beats of three. The lilting, compound feel differs fundamentally from simple meters.',
                    'Bileşik Ölçü (6/8)', '6/8\'de altı sekizlik nota, üçer sekizlikten oluşan iki vuruş olarak gruplandırılır. Hafif, bileşik his, basit ölçülerden temel olarak farklıdır.',
                    ['6/8','compound-meter'], ['rhythm-recognition','compound-meter']],
                [11, 'advanced', 10, '4/4', ['eighth','quarter_rest'],             80,  1,
                    'Syncopation: Off-Beat', 'Syncopation accents the weak beats. The off-beat emphasis creates the driving energy of jazz, funk, and latin music.',
                    'Senkop: Zayıf Vuruş', 'Senkop zayıf vuruşları vurgular. Zayıf vuruş vurgusu caz, funk ve latin müziğinin itici enerjisini yaratır.',
                    ['syncopation','off-beat'], ['rhythm-recognition','groove']],
                [12, 'advanced', 10, '4/4', ['sixteenth','eighth','quarter'],      80,  1,
                    'Sixteenth Notes', 'Sixteenth notes divide each beat into four equal parts. They create dense, fast rhythmic textures.',
                    'On Altılık Notalar', 'On altılık notalar her vuruşu dört eşit parçaya böler. Yoğun, hızlı ritmik dokular yaratırlar.',
                    ['sixteenth-notes','subdivision'], ['rhythm-recognition','fast-subdivision']],
                [13, 'advanced', 10, '4/4', ['dotted-quarter','eighth'],           88,  1,
                    'Dotted Rhythms', 'Dotted rhythms (dotted-quarter + eighth) create the characteristic "long-short" feel of jigs, marches, and baroque dance.',
                    'Noktalı Ritimler', 'Noktalı ritimler (noktalı dörtlük + sekizlik) jig\'lerin, marşların ve barok dansın karakteristik "uzun-kısa" hissini yaratır.',
                    ['dotted-rhythms','baroque'], ['rhythm-recognition','articulation']],
                [14, 'advanced', 10, '4/4', ['quarter','eighth','half','quarter_rest','eighth_rest'], 80, 2,
                    'Two-Bar Phrases', 'Identify rhythmic patterns spanning two measures. Longer patterns test rhythmic memory and phrasing sense.',
                    'İki Ölçülük Cümleler', 'İki ölçü kaplayan ritmik kalıpları tanıyın. Daha uzun kalıplar ritmik belleği ve cümleleme duygusunu test eder.',
                    ['two-bar','phrasing'], ['rhythm-recognition','memory']],
                [15, 'advanced', 10, '4/4', ['whole','half','quarter','eighth','sixteenth','quarter_rest','eighth_rest','dotted-half','dotted-quarter'], 80, 2,
                    'Master: All Values, Any Time Sig', 'All note values, rests, and dotted notes in two-bar phrases. The complete rhythm recognition challenge.',
                    'Usta: Tüm Değerler, Herhangi Ölçü', 'İki ölçülük cümlelerde tüm nota değerleri, suslar ve noktalı notalar. Tam ritim tanıma zorluğu.',
                    ['master','all-note-values'], ['rhythm-recognition','mastery']],
            ];
            foreach ($rhythmLessons as $l) {
                $data[] = $this->makeRhythm('rhythm', $catId, $l);
            }
        }

        // ── MELODIC DICTATION ────────────────────────────────────────────────
        $catId = $categories['melodic-dictation']->id ?? null;
        if ($catId) {
            $dictLessons = [
                [1, 'beginner',  5,  ['C4','D4','E4'],                              3, 'treble', 'C', 60,  false,
                    'Do-Re-Mi: First Three Notes', 'Write down the first three notes of the C major scale. Begin your melodic dictation journey with the most familiar sounds.',
                    'Do-Re-Mi: İlk Üç Nota', 'C majör gamının ilk üç notasını yazın. Melodic dikte yolculuğunuza en tanıdık seslerle başlayın.',
                    ['c-major','beginner-dictation'], ['melodic-dictation','note-writing']],
                [2, 'beginner',  5,  ['C4','D4','E4','F4','G4'],                    4, 'treble', 'C', 58,  false,
                    'C Major Pentachord', 'The first five notes of C major — the pentachord. These five notes appear in almost every beginner melody ever written.',
                    'C Majör Pentatonik', 'C majörün ilk beş notası — pentatonik. Bu beş nota neredeyse her başlangıç seviyesi melodisinde görülür.',
                    ['pentachord','c-major'], ['melodic-dictation']],
                [3, 'beginner',  5,  ['C4','D4','E4','F4','G4','A4','B4','C5'],     5, 'treble', 'C', 55,  false,
                    'Full C Major Scale', 'Dictate complete melodic patterns from the full C major scale. A single octave, all white keys.',
                    'Tam C Majör Gamı', 'Tam C majör gamından eksiksiz melodik kalıpları not edin. Tek bir oktav, tüm beyaz tuşlar.',
                    ['full-scale','c-major'], ['melodic-dictation']],
                [4, 'beginner',  5,  ['G4','A4','B4','C5','D5','E5','F#5','G5'],   5, 'treble', 'G', 55,  false,
                    'G Major Scale', 'Dictate melodies in G major — one sharp (F#). The new note F# adds color that distinguishes it from C major.',
                    'G Majör Gamı', 'G majörde melodiler not edin — bir diyez (F#). Yeni nota F#, onu C majörden ayıran bir renk katıyor.',
                    ['g-major','one-sharp'], ['melodic-dictation']],
                [5, 'beginner',  5,  ['F4','G4','A4','Bb4','C5','D5','E5','F5'],   5, 'treble', 'F', 55,  false,
                    'F Major Scale', 'Dictate melodies in F major — one flat (Bb). The Bb gives F major its characteristic mellow quality.',
                    'F Majör Gamı', 'F majörde melodiler not edin — bir bemol (Bb). Bb, F majöre karakteristik yumuşak kalitesini veriyor.',
                    ['f-major','one-flat'], ['melodic-dictation']],
                [6, 'intermediate', 8, ['C4','D4','E4','F4','G4','A4','B4','C5'], 6, 'treble', 'C', 52,  false,
                    'C Major: Two-Bar Melody', 'Dictate two-measure melodies in C major. Longer patterns test working memory and melodic shape recognition.',
                    'C Majör: İki Ölçülük Melodi', 'C majörde iki ölçülük melodiler not edin. Daha uzun kalıplar çalışma belleğini ve melodik şekil tanımayı test eder.',
                    ['two-bar','c-major'], ['melodic-dictation','memory']],
                [7, 'intermediate', 8, ['A3','B3','C4','D4','E4','F4','G4','A4'], 5, 'treble', 'Am', 55, false,
                    'A Natural Minor', 'Dictate melodies in A natural minor. The minor mode creates a different emotional landscape from major.',
                    'La Doğal Minör', 'La doğal minörde melodiler not edin. Minör mod, majörden farklı bir duygusal peyzaj yaratır.',
                    ['a-minor','natural-minor'], ['melodic-dictation']],
                [8, 'intermediate', 8, ['D4','E4','F#4','G4','A4','B4','C#5','D5'], 5, 'treble', 'D', 55, false,
                    'D Major', 'Dictate melodies in D major — two sharps (F#, C#). Common key for violin and guitar music.',
                    'Re Majör', 'Re majörde melodiler not edin — iki diyez (F#, C#). Keman ve gitar müziği için yaygın ton.',
                    ['d-major','two-sharps'], ['melodic-dictation']],
                [9, 'intermediate', 8, ['A3','B3','C4','D4','E4','F#4','G#4','A4'], 5, 'treble', 'Am', 52, false,
                    'Melodic Minor Patterns', 'Dictate ascending melodic minor patterns — the raised 6th and 7th create a smooth, jazz-inflected quality.',
                    'Melodik Minör Kalıplar', 'Yükselen melodik minör kalıpları not edin — yükseltilmiş 6. ve 7. pürüzsüz, caz katkılı bir kalite yaratır.',
                    ['melodic-minor','jazz-flavor'], ['melodic-dictation']],
                [10, 'intermediate', 8, ['G4','A4','B4','C5','D5','E5','F#5','G5'], 7, 'treble', 'G', 50, false,
                    'G Major: Two-Bar', 'Two-measure melodies in G major. Introduces longer phrase recognition in a key with one sharp.',
                    'G Majör: İki Ölçü', 'G majörde iki ölçülük melodiler. Bir diyezli bir tonda daha uzun cümle tanımayı tanıtır.',
                    ['g-major','two-bar'], ['melodic-dictation','memory']],
                [11, 'advanced', 10, ['C4','C#4','D4','D#4','E4','F4','F#4','G4','A4','B4','C5'], 6, 'treble', 'C', 50, false,
                    'Chromatic Passing Tones', 'Melodies that include chromatic passing tones. Chromaticism adds color but demands sharper interval discrimination.',
                    'Kromatik Geçici Notalar', 'Kromatik geçici notalar içeren melodiler. Kromatizm renk katar ama daha keskin aralık ayrımı talep eder.',
                    ['chromatic','passing-tones'], ['melodic-dictation','chromatic-hearing']],
                [12, 'advanced', 10, ['D4','E4','F4','G4','A4','Bb4','C#5','D5'], 6, 'treble', 'Dm', 50, false,
                    'D Minor: Natural & Harmonic', 'Melodies combining natural and harmonic minor — expect both C♮ and C# to appear. Tests your chromatic flexibility.',
                    'Re Minör: Doğal & Harmonik', 'Doğal ve harmonik minörü birleştiren melodiler — hem C♮ hem de C# görünmeyi bekleyin. Kromatik esnekliğinizi test eder.',
                    ['d-minor','harmonic-minor','mixed'], ['melodic-dictation','chromatic-flexibility']],
                [13, 'advanced', 10, ['C4','D4','E4','F4','G4','A4','B4','C5'], 6, 'treble', 'C', 52, true,
                    'Include Rhythm: C Major', 'Dictate both pitches AND rhythm simultaneously. Rhythmic dictation doubles the cognitive demand.',
                    'Ritimle Birlikte: C Majör', 'Hem perde hem de ritmi aynı anda not edin. Ritmik dikte bilişsel talebi iki katına çıkarır.',
                    ['rhythm-and-pitch','c-major'], ['melodic-dictation','rhythmic-dictation']],
                [14, 'advanced', 10, ['G4','A4','B4','C5','D5','E5','F#5','G5'], 10, 'treble', 'G', 48, false,
                    'Four-Bar Melody: G Major', 'Dictate a four-bar melody in G major. Long-form dictation tests sustained attention and phrase memory.',
                    'Dört Ölçülük Melodi: G Majör', 'G majörde dört ölçülük bir melodi not edin. Uzun form dikte, sürekli dikkat ve cümle belleğini test eder.',
                    ['four-bar','long-form'], ['melodic-dictation','sustained-attention']],
                [15, 'advanced', 10, ['C4','D4','E4','F4','F#4','G4','A4','Bb4','B4','C5'], 10, 'treble', 'C', 48, true,
                    'Master: Mixed Keys with Rhythm', 'Four-bar dictation with pitch AND rhythm, including chromatic passing tones. The ultimate melodic dictation challenge.',
                    'Usta: Karma Tonalite & Ritim', 'Kromatik geçici notalar dahil, perde ve ritimli dört ölçülük dikte. Nihai melodik dikte zorluğu.',
                    ['master','rhythm-and-pitch','chromatic'], ['melodic-dictation','mastery']],
            ];
            foreach ($dictLessons as $l) {
                $data[] = $this->makeDictation('melodic-dictation', $catId, $l);
            }
        }

        // ── SINGLE NOTE ──────────────────────────────────────────────────────
        $catId = $categories['single-note']->id ?? null;
        if ($catId) {
            $noteLessons = [
                [1, 'beginner',  5,  ['C','G'], '4',
                    'C and G: The Anchor Notes', 'Identify C and G — two of the most fundamental pitches in music. C is the tonal center; G a perfect 5th above.',
                    'C ve G: Temel Notalar', 'C ve G\'yi tanıyın — müziğin en temel seslerinden ikisi. C tonal merkezdir; G bir tam beşli yukarıda.',
                    ['anchor-notes'], ['pitch-recognition']],
                [2, 'beginner',  5,  ['C','D','E','F','G'], '4',
                    'C Major Pentachord', 'Identify the five notes of the C major pentachord. These form the core of most beginner melodies.',
                    'C Majör Pentatonik', 'C majör pentatoniğinin beş notasını tanıyın. Bunlar çoğu başlangıç melodisinin çekirdeğini oluşturur.',
                    ['pentachord','c-major'], ['pitch-recognition']],
                [3, 'beginner',  5,  ['C','D','E','F','G','A','B'], '4',
                    'Full C Major Scale', 'Identify all seven notes of the C major scale — the white keys of the piano.',
                    'Tam C Majör Gamı', 'C majör gamının yedi notasının tamamını tanıyın — piyanonun beyaz tuşları.',
                    ['c-major','natural-notes'], ['pitch-recognition']],
                [4, 'beginner',  5,  ['G','A','B','C','D','E'], '4',
                    'G Major Notes', 'Identify the notes of G major, which includes F# instead of F. Listen carefully for the new #.',
                    'G Majör Notaları', 'F yerine F# içeren G majörün notalarını tanıyın. Yeni diyez için dikkatli dinleyin.',
                    ['g-major','one-sharp'], ['pitch-recognition']],
                [5, 'beginner',  5,  ['F','G','A','Bb','C','D','E'], '4',
                    'F Major Notes', 'Identify the notes of F major, which includes Bb instead of B. The flat changes the character of the scale.',
                    'F Majör Notaları', 'B yerine Bb içeren F majörün notalarını tanıyın. Bemol gamın karakterini değiştirir.',
                    ['f-major','one-flat'], ['pitch-recognition']],
                [6, 'intermediate', 8, ['C','Db','D','Eb','E','F'], '4',
                    'Chromatic Scale I: C to F', 'Identify every chromatic pitch from C through F — six pitches including half steps.',
                    'Kromatik Gam I: Do\'dan Fa\'ya', 'C\'den F\'ye kadar her kromatik perdeyi tanıyın — yarım adımlar dahil altı perde.',
                    ['chromatic','lower-half'], ['pitch-recognition','chromatic']],
                [7, 'intermediate', 8, ['F#','G','Ab','A','Bb','B'], '4',
                    'Chromatic Scale II: F# to B', 'Identify every chromatic pitch from F# through B — the upper half of the chromatic scale.',
                    'Kromatik Gam II: Fa#\'dan Si\'ye', 'F#\'dan B\'ye kadar her kromatik perdeyi tanıyın — kromatik gamın üst yarısı.',
                    ['chromatic','upper-half'], ['pitch-recognition','chromatic']],
                [8, 'intermediate', 8, ['C','Db','D','Eb','E','F','F#','G','Ab','A','Bb','B'], '4',
                    'All 12 Chromatic Notes', 'Identify all twelve chromatic pitches in a single session. Complete chromatic pitch recognition.',
                    '12 Kromatik Notanın Tamamı', 'Tek bir oturumda tüm on iki kromatik perdeyi tanıyın. Tam kromatik perde tanıma.',
                    ['all-12','chromatic','comprehensive'], ['pitch-recognition','mastery']],
                [9, 'intermediate', 8, ['C','D','E','F','G','A','B'], '3',
                    'Lower Register: Octave 3', 'Identify natural notes in the lower register (octave 3). Lower pitches require more careful listening.',
                    'Pes Oktav: 3. Oktav', 'Alt registerdaki (3. oktav) doğal notaları tanıyın. Düşük perdeler daha dikkatli dinleme gerektirir.',
                    ['lower-register','octave-3'], ['pitch-recognition','register']],
                [10, 'intermediate', 8, ['C','D','E','F','G','A','B'], '5',
                    'Higher Register: Octave 5', 'Identify natural notes in the higher register (octave 5). High pitches have a brighter, clearer quality.',
                    'Tiz Oktav: 5. Oktav', 'Üst registerdaki (5. oktav) doğal notaları tanıyın. Yüksek perdeler daha parlak, net bir kaliteye sahiptir.',
                    ['higher-register','octave-5'], ['pitch-recognition','register']],
                [11, 'advanced', 10, ['C','Db','D','Eb','E','F','F#','G','Ab','A','Bb','B'], '3',
                    'All 12 Notes: Octave 3', 'All twelve chromatic pitches in the lower register. Tests pitch recognition without the security of the middle octave.',
                    'Tüm 12 Nota: 3. Oktav', 'Alt registerdaki tüm on iki kromatik perde. Perde tanımayı orta oktavın güvenliği olmadan test eder.',
                    ['all-12','octave-3'], ['pitch-recognition','register-independence']],
                [12, 'advanced', 10, ['C','Db','D','Eb','E','F','F#','G','Ab','A','Bb','B'], '5',
                    'All 12 Notes: Octave 5', 'All twelve chromatic pitches in the higher register. Tests pitch recognition in the bright, challenging upper range.',
                    'Tüm 12 Nota: 5. Oktav', 'Üst registerdaki tüm on iki kromatik perde. Perde tanımayı parlak, zorlu üst aralıkta test eder.',
                    ['all-12','octave-5'], ['pitch-recognition','register-independence']],
                [13, 'advanced', 10, ['C','Db','D','Eb','E','F','F#','G','Ab','A','Bb','B'], '4',
                    'Speed Drill: All Chromatic Notes', 'Rapid identification of all twelve pitches under time pressure. Build instantaneous pitch recognition.',
                    'Hız: Tüm Kromatik Notalar', 'Zaman baskısı altında tüm on iki perdenin hızlı tanımlanması. Anlık perde tanımayı geliştirin.',
                    ['speed','all-12'], ['pitch-recognition','speed-training']],
                [14, 'advanced', 10, ['C','D','E','F','G','A','B'], ['3','4'],
                    'Mixed Octaves: Natural Notes', 'Natural notes across octaves 3 and 4. Register independence for the diatonic scale.',
                    'Karma Oktav: Doğal Notalar', '3. ve 4. oktavlardaki doğal notalar. Diyatonik gam için register bağımsızlığı.',
                    ['mixed-octave','natural-notes'], ['pitch-recognition','register-independence']],
                [15, 'advanced', 10, ['C','Db','D','Eb','E','F','F#','G','Ab','A','Bb','B'], ['3','4','5'],
                    'Master: All Notes, 3 Octaves', 'All twelve chromatic pitches across three octaves. The ultimate absolute pitch and pitch recognition challenge.',
                    'Usta: Tüm Notalar, 3 Oktav', 'Üç oktav boyunca tüm on iki kromatik perde. Nihai mutlak perde ve perde tanıma zorluğu.',
                    ['master','all-12','three-octaves'], ['pitch-recognition','mastery']],
            ];
            foreach ($noteLessons as $l) {
                $data[] = $this->makeSingleNote('single-note', $catId, $l);
            }
        }

        return $data;
    }

    // ── BUILDER HELPERS ──────────────────────────────────────────────────────

    private function makeInterval(string $categorySlug, int $catId, array $l, string $practiceType): array
    {
        [$sortOrder, $level, $duration, $intervals, $notes, $octaveRange,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        return [
            'category_id'                 => $catId,
            'slug'                        => "{$categorySlug}-lesson-{$sortOrder}",
            'title'                       => $titleEn,
            'description'                 => $descEn,
            'translations'                => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level'                       => $level,
            'sort_order'                  => $sortOrder,
            'estimated_duration_minutes'  => $duration,
            'tags'                        => $tags,
            'skills_trained'              => $skills,
            'config_json'                 => [
                'practice_type'    => $practiceType,
                'allowed_intervals'=> $intervals,
                'allowed_notes'    => $notes,
                'octave_range'     => $octaveRange,
                'question_counts'  => ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
            ],
        ];
    }

    private function makeDirection(string $categorySlug, int $catId, array $l): array
    {
        [$sortOrder, $level, $duration, $semitones, $notes, $octave, $clef,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        return [
            'category_id'                 => $catId,
            'slug'                        => "{$categorySlug}-lesson-{$sortOrder}",
            'title'                       => $titleEn,
            'description'                 => $descEn,
            'translations'                => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level'                       => $level,
            'sort_order'                  => $sortOrder,
            'estimated_duration_minutes'  => $duration,
            'tags'                        => $tags,
            'skills_trained'              => $skills,
            'config_json'                 => [
                'practice_type'              => 'interval-direction-practice',
                'allowed_intervals_semitones'=> $semitones,
                'allowed_notes'              => $notes,
                'octave'                     => $octave,
                'clef'                       => $clef,
                'question_counts'            => ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
            ],
        ];
    }

    private function makeConstruction(string $categorySlug, int $catId, array $l): array
    {
        [$sortOrder, $level, $duration, $intervals, $roots, $octave,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        return [
            'category_id'                 => $catId,
            'slug'                        => "{$categorySlug}-lesson-{$sortOrder}",
            'title'                       => $titleEn,
            'description'                 => $descEn,
            'translations'                => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level'                       => $level,
            'sort_order'                  => $sortOrder,
            'estimated_duration_minutes'  => $duration,
            'tags'                        => $tags,
            'skills_trained'              => $skills,
            'config_json'                 => [
                'practice_type'     => 'interval-construction-practice',
                'allowed_intervals' => $intervals,
                'allowed_root_notes'=> $roots,
                'octave'            => $octave,
                'question_counts'   => ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
            ],
        ];
    }

    private function makeComparison(string $categorySlug, int $catId, array $l): array
    {
        [$sortOrder, $level, $duration, $pairs,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        return [
            'category_id'                 => $catId,
            'slug'                        => "{$categorySlug}-lesson-{$sortOrder}",
            'title'                       => $titleEn,
            'description'                 => $descEn,
            'translations'                => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level'                       => $level,
            'sort_order'                  => $sortOrder,
            'estimated_duration_minutes'  => $duration,
            'tags'                        => $tags,
            'skills_trained'              => $skills,
            'config_json'                 => [
                'practice_type'          => 'interval-comparison-practice',
                'allowed_interval_pairs' => $pairs,
                'clef'                   => 'treble',
                'octave'                 => '4',
                'question_counts'        => ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
            ],
        ];
    }

    private function makeScale(string $categorySlug, int $catId, array $l): array
    {
        [$sortOrder, $level, $duration, $scaleTypes, $roots, $direction, $distractors,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        return [
            'category_id'                 => $catId,
            'slug'                        => "{$categorySlug}-lesson-{$sortOrder}",
            'title'                       => $titleEn,
            'description'                 => $descEn,
            'translations'                => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level'                       => $level,
            'sort_order'                  => $sortOrder,
            'estimated_duration_minutes'  => $duration,
            'tags'                        => $tags,
            'skills_trained'              => $skills,
            'config_json'                 => [
                'practice_type'       => 'scale-practice',
                'allowed_scale_types' => $scaleTypes,
                'allowed_root_notes'  => $roots,
                'direction'           => $direction,
                'octave'              => '4',
                'distractor_pool'     => $distractors,
                'question_counts'     => ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
            ],
        ];
    }

    private function makeChord(string $categorySlug, int $catId, array $l): array
    {
        [$sortOrder, $level, $duration, $chordTypes, $roots, $voicing, $inversions, $distractors,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        return [
            'category_id'                 => $catId,
            'slug'                        => "{$categorySlug}-lesson-{$sortOrder}",
            'title'                       => $titleEn,
            'description'                 => $descEn,
            'translations'                => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level'                       => $level,
            'sort_order'                  => $sortOrder,
            'estimated_duration_minutes'  => $duration,
            'tags'                        => $tags,
            'skills_trained'              => $skills,
            'config_json'                 => [
                'practice_type'       => 'chord-practice',
                'allowed_chord_types' => $chordTypes,
                'allowed_root_notes'  => $roots,
                'voicing'             => $voicing,
                'include_inversions'  => $inversions,
                'distractor_pool'     => $distractors,
                'question_counts'     => ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
            ],
        ];
    }

    private function makeRhythm(string $categorySlug, int $catId, array $l): array
    {
        [$sortOrder, $level, $duration, $timeSig, $noteValues, $tempo, $bars,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        return [
            'category_id'                 => $catId,
            'slug'                        => "{$categorySlug}-lesson-{$sortOrder}",
            'title'                       => $titleEn,
            'description'                 => $descEn,
            'translations'                => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level'                       => $level,
            'sort_order'                  => $sortOrder,
            'estimated_duration_minutes'  => $duration,
            'tags'                        => $tags,
            'skills_trained'              => $skills,
            'config_json'                 => [
                'practice_type'       => 'rhythm-practice',
                'time_signatures'     => [$timeSig],
                'allowed_note_values' => $noteValues,
                'tempo_range'         => [$tempo - 4, $tempo + 4],
                'bars'                => $bars,
                'question_counts'     => ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
            ],
        ];
    }

    private function makeDictation(string $categorySlug, int $catId, array $l): array
    {
        [$sortOrder, $level, $duration, $notePool, $melodyLength, $clef, $keySig, $tempo, $includeRhythm,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        return [
            'category_id'                 => $catId,
            'slug'                        => "{$categorySlug}-lesson-{$sortOrder}",
            'title'                       => $titleEn,
            'description'                 => $descEn,
            'translations'                => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level'                       => $level,
            'sort_order'                  => $sortOrder,
            'estimated_duration_minutes'  => $duration,
            'tags'                        => $tags,
            'skills_trained'              => $skills,
            'config_json'                 => [
                'practice_type'  => 'melodic-dictation',
                'note_pool'      => $notePool,
                'melody_length'  => $melodyLength,
                'clef'           => $clef,
                'key_signatures' => [$keySig],
                'tempo_range'    => [$tempo - 3, $tempo + 3],
                'include_rhythm' => $includeRhythm,
                'bars'           => $melodyLength <= 5 ? 1 : ($melodyLength <= 8 ? 2 : 4),
                'question_counts'=> ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
            ],
        ];
    }

    private function makeSingleNote(string $categorySlug, int $catId, array $l): array
    {
        [$sortOrder, $level, $duration, $notes, $octave,
            $titleEn, $descEn, $titleTr, $descTr, $tags, $skills] = $l;

        $octaveRange = is_array($octave) ? $octave : [$octave];

        return [
            'category_id'                 => $catId,
            'slug'                        => "{$categorySlug}-lesson-{$sortOrder}",
            'title'                       => $titleEn,
            'description'                 => $descEn,
            'translations'                => ['tr' => ['title' => $titleTr, 'description' => $descTr]],
            'level'                       => $level,
            'sort_order'                  => $sortOrder,
            'estimated_duration_minutes'  => $duration,
            'tags'                        => $tags,
            'skills_trained'              => $skills,
            'config_json'                 => [
                'practice_type'  => 'single-note-practice',
                'target_type'    => 'note',
                'allowed_notes'  => $notes,
                'octave_range'   => $octaveRange,
                'distractor_count'=> 3,
                'question_counts'=> ['free' => 5, 'premium_mid' => 10, 'premium_full' => 15],
            ],
        ];
    }
}
