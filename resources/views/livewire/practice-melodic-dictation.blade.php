{{--
    Melodic Dictation — Exercise Setup Studio / Learning Path flow.

    The entire screen (markup + the ~600-line dictation engine JS) lives in
    partials/melodic-dictation.blade.php. This file only supplies what actually
    differs from the AI-themed twin: colours, routes, the header title and the
    empty-state copy. Previously the two were 97% duplicated files that had to
    be kept in sync by hand — see practice-ai-melodic-dictation.blade.php.

    Tailwind note: mdBeatBarClass / mdNextBtnClass hold literal class strings on
    purpose. They must stay written out here so the Tailwind content scanner
    still sees them — building them dynamically would get them purged.
--}}
@include('livewire.partials.melodic-dictation', [
    // Routes
    'mdBackUrl' => '/learn',
    'mdEmptyUrl' => '/exercise-setup',

    // Empty state
    'mdEmptyDesc' => __('app.practice_ui.dictation.empty'),
    'mdEmptyIcon' => 'settings-2',
    'mdEmptyCta' => __('app.practice_ui.common.adjust_settings'),

    // Header title
    'mdTitleHtml' => '<h1 class="text-xl font-bold text-white">'.e(__('app.practice_ui.dictation.title')).'</h1>',

    // Theme — teal / green / violet
    'mdDurBg' => '#0d9488',
    'mdDurHover' => '#065f46',
    'mdDurActive' => '#022c22',
    'mdDurSelBg' => '#042f2e',
    'mdDurSelBorder' => '#34d399',
    'mdDurSelRing' => 'rgba(52,211,153,0.3)',
    'mdNoteHoverBorder' => '#a78bfa',
    'mdNoteHoverBg' => '#f5f3ff',
    'mdNoteFlash' => '#7c3aed',
    'mdAccActiveBg' => '#7c3aed',
    'mdAccActiveBorder' => '#a78bfa',
    'mdAccActiveRing' => 'rgba(167,139,250,0.3)',
    'mdSegActive' => '#0d9488',
    'mdPlayGrad' => 'linear-gradient(135deg,#16a34a 0%,#22c55e 100%)',
    'mdNextSegGrad' => 'linear-gradient(135deg,#0d9488,#14b8a6)',
    'mdFullGrad' => 'linear-gradient(135deg,#7c3aed,#a78bfa)',
    'mdBeatBarClass' => 'bg-amber-400',
    'mdNextBtnClass' => 'bg-blue-100 text-blue-700 border-2 border-blue-300 hover:bg-blue-200',

    // JS entry point — the partial calls window.<name>()
    'mdInitFn' => 'initPracticeMelodicDictation',
])
