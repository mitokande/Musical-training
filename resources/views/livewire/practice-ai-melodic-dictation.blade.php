{{--
    AI Melodic Dictation — the /practice-ai/melodic-dictation flow.

    Same engine as practice-melodic-dictation: PracticeAiMelodicDictation
    extends PracticeMelodicDictation, and the screen itself now comes from the
    shared partial below. Only the indigo/AI skin, the /ai-exercises routes and
    the AI-specific copy live here, so the two screens can no longer drift out
    of sync the way the old duplicated files did.

    Tailwind note: mdBeatBarClass / mdNextBtnClass hold literal class strings on
    purpose. They must stay written out here so the Tailwind content scanner
    still sees them — building them dynamically would get them purged.
--}}
@include('livewire.partials.melodic-dictation', [
    // Routes
    'mdBackUrl' => '/ai-exercises',
    'mdEmptyUrl' => '/ai-exercises',

    // Empty state
    'mdEmptyDesc' => __('app.practice_ui.dictation.ai_empty'),
    'mdEmptyIcon' => 'sparkles',
    'mdEmptyCta' => __('app.practice_ui.dictation.ai_back'),

    // Header title — carries the AI sparkle
    'mdTitleHtml' => '<h1 class="text-xl font-bold text-white inline-flex items-center gap-2">'."\n"
        .'                            <i data-lucide="sparkles" class="w-5 h-5 text-amber-300"></i>'."\n"
        .'                            '.e(__('app.practice_ui.dictation.ai_title'))."\n"
        .'                        </h1>',

    // Theme — indigo / violet
    'mdDurBg' => '#4f46e5',
    'mdDurHover' => '#4338ca',
    'mdDurActive' => '#312e81',
    'mdDurSelBg' => '#1e1b4b',
    'mdDurSelBorder' => '#818cf8',
    'mdDurSelRing' => 'rgba(129,140,248,0.35)',
    'mdNoteHoverBorder' => '#818cf8',
    'mdNoteHoverBg' => '#eef2ff',
    'mdNoteFlash' => '#4f46e5',
    'mdAccActiveBg' => '#4f46e5',
    'mdAccActiveBorder' => '#818cf8',
    'mdAccActiveRing' => 'rgba(129,140,248,0.35)',
    'mdSegActive' => '#6366f1',
    'mdPlayGrad' => 'linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%)',
    'mdNextSegGrad' => 'linear-gradient(135deg,#4f46e5,#6366f1)',
    'mdFullGrad' => 'linear-gradient(135deg,#4338ca,#818cf8)',
    'mdBeatBarClass' => 'bg-indigo-400',
    'mdNextBtnClass' => 'bg-indigo-100 text-indigo-700 border-2 border-indigo-300 hover:bg-indigo-200',

    // JS entry point — the partial calls window.<name>()
    'mdInitFn' => 'initPracticeAiMelodicDictation',
])
