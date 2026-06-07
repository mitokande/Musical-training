<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;
use OpenAI;

class AiChatController extends Controller
{
    private const SUPPORTED_LOCALES = [
        'tr' => 'Turkish',
        'en' => 'English',
        'de' => 'German',
        'fr' => 'French',
        'es' => 'Spanish',
        'pt' => 'Portuguese',
        'it' => 'Italian',
    ];

    private const DAILY_LIMIT_FREE    = 3;
    private const DAILY_LIMIT_PREMIUM = 10;
    private const MAX_WORDS_FREE      = 200;
    private const MAX_WORDS_PREMIUM   = 400;

    private const LIMIT_MESSAGES = [
        'tr' => 'Günlük soru limitinize ulaştınız. Daha fazla soru sormak için Premium\'a geçin.',
        'en' => 'You have reached your daily question limit. Upgrade to Premium for more questions.',
        'de' => 'Sie haben Ihr tägliches Fragenlimit erreicht. Upgrade auf Premium für mehr Fragen.',
        'fr' => 'Vous avez atteint votre limite quotidienne de questions. Passez à Premium pour plus.',
        'es' => 'Has alcanzado tu límite diario de preguntas. Actualiza a Premium para más preguntas.',
        'pt' => 'Você atingiu seu limite diário de perguntas. Atualize para Premium para mais perguntas.',
        'it' => 'Hai raggiunto il limite giornaliero di domande. Passa a Premium per più domande.',
    ];

    public function index(Request $request): View
    {
        $history = session('ai_chat_history', []);
        return view('ai-chat.index', compact('history'));
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate(['message' => ['required', 'string', 'max:500']]);

        $apiKey = config('services.openai.key');
        if (!$apiKey) {
            return redirect()->route('ai-chat.index')->with('chat_error', 'OpenAI API anahtarı yapılandırılmamış.');
        }

        $user     = $request->user();
        $locale   = $this->resolveLocale($user->locale ?? 'en');
        $cacheKey = "ai_chat:{$user->id}:" . now()->toDateString();
        $used     = (int) Cache::get($cacheKey, 0);
        $limit    = $user->isPremium() ? self::DAILY_LIMIT_PREMIUM : self::DAILY_LIMIT_FREE;

        if ($used >= $limit) {
            $msg = self::LIMIT_MESSAGES[$locale] ?? self::LIMIT_MESSAGES['en'];
            return redirect()->route('ai-chat.index')->with('chat_error', $msg);
        }

        $maxWords  = $user->isPremium() ? self::MAX_WORDS_PREMIUM : self::MAX_WORDS_FREE;
        $maxTokens = $user->isPremium() ? 600 : 320;

        $history = session('ai_chat_history', []);
        $userMsg = $request->input('message');

        $messages = [
            [
                'role'    => 'system',
                'content' => $this->buildSystemPrompt($maxWords) . "\n\n" . $this->buildUserContext($user, $locale),
            ],
        ];

        foreach ($history as $msg) {
            $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
        }
        $messages[] = ['role' => 'user', 'content' => $userMsg];

        try {
            $client   = OpenAI::client($apiKey);
            $response = $client->chat()->create([
                'model'       => 'gpt-4.1-mini',
                'messages'    => $messages,
                'max_tokens'  => $maxTokens,
                'temperature' => 0.5,
            ]);

            $answer = $response->choices[0]->message->content;

        } catch (\Exception $e) {
            return redirect()->route('ai-chat.index')->with('chat_error', 'Bir hata oluştu. Lütfen tekrar deneyin.');
        }

        Cache::put($cacheKey, $used + 1, now()->endOfDay());

        $history[] = ['role' => 'user',      'content' => $userMsg, 'time' => now()->format('H:i')];
        $history[] = ['role' => 'assistant', 'content' => $answer,  'time' => now()->format('H:i')];

        if (count($history) > 20) {
            $history = array_slice($history, -20);
        }

        session(['ai_chat_history' => $history]);

        return redirect()->route('ai-chat.index');
    }

    public function clear(Request $request): RedirectResponse
    {
        session()->forget('ai_chat_history');
        return redirect()->route('ai-chat.index');
    }

    private function resolveLocale(string $locale): string
    {
        return array_key_exists($locale, self::SUPPORTED_LOCALES) ? $locale : 'en';
    }

    private function buildSystemPrompt(int $maxWords): string
    {
        return implode("\n", [
            'You are the Music Assistant of the Harmoniva platform. Respond like an experienced, patient, and pedagogical music teacher.',
            '',
            'Scope — only answer questions related to:',
            '- Music theory, ear training, solfege, dictation, note reading, and notation',
            '- Rhythm, harmony, chords, scales, modes, and intervals',
            '- Instrument learning and practice techniques',
            '- Music history, periods, composers, and musical form',
            '- ABRSM, LCM, conservatory, and similar music exam preparation',
            '- Harmoniva platform: Learning Path, AI Exercises, AI Coach, Progress, and practice recommendations',
            '',
            'Rules:',
            '- Reply in the language specified by the user\'s preferred_language field. Supported languages: English, Spanish, German, French, Portuguese, Turkish, Italian. If not supported, use English.',
            '- Keep your response under ' . $maxWords . ' words.',
            '- For simple questions, answer briefly and clearly.',
            '- For technical, broad, or important questions, give a more detailed, structured, and example-based answer.',
            '- Adapt the explanation to the user\'s apparent level.',
            '- Use accurate music terminology without unnecessary complexity.',
            '- Give short musical examples when useful (notes, intervals, chords, rhythms, practice routines).',
            '- For ear training questions, provide practical advice and suggest relevant Harmoniva exercise types when appropriate.',
            '- Do not invent Harmoniva features not listed in this prompt.',
            '- Do not claim knowledge of the user\'s progress or profile unless it is provided below.',
            '- If a question is unrelated to music or Harmoniva, politely say it is outside your scope and offer help with a music topic.',
            '- For questions requiring current official information (exam syllabi, official requirements), suggest checking the latest official source.',
            '- Do not provide medical, legal, financial, or personal crisis advice.',
            '- Keep the tone supportive, calm, teacher-like, and motivating.',
            '',
            'Response formatting:',
            '- Separate paragraphs with a blank line.',
            '- Use bullet points (- item) for lists, steps, or multiple examples.',
            '- Use **bold** for key music terms.',
            '- Use ♯ for sharp and ♭ for flat in note examples (e.g. F♯, B♭).',
            '- For very simple factual questions, reply in 1-2 sentences without extra formatting.',
        ]);
    }

    private function buildUserContext(\App\Models\User $user, string $locale): string
    {
        $language = self::SUPPORTED_LOCALES[$locale] ?? 'English';
        $profile  = $user->profile;

        $lines = [
            'User music profile context (use to personalize responses — do not repeat this back to the user):',
            "- preferred_language: {$language} ({$locale})",
        ];

        if ($profile?->primary_instrument) {
            $levelMap = ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'];
            $lines[]  = '- instrument: ' . $profile->primary_instrument;
            $lines[]  = '- level: ' . ($levelMap[$profile->musical_level ?? ''] ?? 'unknown');
        }

        $weakAreas = $user->userPractices()
            ->with('practice')
            ->orderByDesc('updated_at')
            ->take(10)
            ->get()
            ->filter(fn($up) =>
                isset($up->total_questions, $up->correct_answers)
                && $up->total_questions > 0
                && ($up->correct_answers / $up->total_questions) < 0.6
            )
            ->map(fn($up) => $up->practice?->name)
            ->filter()
            ->unique()
            ->values();

        if ($weakAreas->isNotEmpty()) {
            $lines[] = '- recent weak areas: ' . $weakAreas->implode(', ');
        }

        return implode("\n", $lines);
    }
}
