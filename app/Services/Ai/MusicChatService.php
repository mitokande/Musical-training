<?php

namespace App\Services\Ai;

use App\Models\SystemSetting;
use App\Models\User;

/**
 * The music assistant behind "Ask Harmoniva" in the app.
 *
 * Same scope and same house rules as the website's AiChatController, with one
 * structural difference: the mobile API is token-authenticated and stateless,
 * so there is no PHP session to keep the thread in. The device owns its history
 * and replays the recent turns with each message.
 *
 * That means the history arriving here is client-supplied and cannot be trusted
 * as a record of what was actually said — a modified client could forge an
 * assistant turn to steer the reply. The mitigations are that the system prompt
 * is always built here and always sent first, roles and lengths are validated
 * at the controller, and the daily quota caps what any of it can cost. A user
 * jailbreaking their own chat session is the whole blast radius.
 */
class MusicChatService
{
    /** How many prior turns are replayed, newest kept. */
    public const HISTORY_LIMIT = 20;

    private const LOCALES = [
        'tr' => 'Turkish', 'en' => 'English', 'de' => 'German', 'fr' => 'French',
        'es' => 'Spanish', 'pt' => 'Portuguese', 'it' => 'Italian',
    ];

    private const MAX_WORDS_FREE = 200;

    private const MAX_WORDS_PREMIUM = 400;

    public function __construct(private readonly ChatCompletionClient $client) {}

    /**
     * @param  array<int, array{role: string, content: string}>  $history
     */
    public function reply(User $user, string $message, array $history = []): string
    {
        $premium = $user->isEffectivelyPremium();
        $locale = $this->localeFor($user);

        $messages = [[
            'role' => 'system',
            'content' => $this->systemPrompt($premium ? self::MAX_WORDS_PREMIUM : self::MAX_WORDS_FREE)
                ."\n\n".$this->userContext($user, $locale),
        ]];

        foreach (array_slice($history, -self::HISTORY_LIMIT) as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => $turn['content']];
        }

        $messages[] = ['role' => 'user', 'content' => $message];

        return trim($this->client->complete($messages, [
            'feature' => 'mobile_ai_chat',
            'model' => (string) SystemSetting::get('ai_model', 'gpt-4.1-mini'),
            'max_tokens' => $premium ? 600 : 320,
            'temperature' => 0.5,
        ])->content);
    }

    public function localeFor(User $user): string
    {
        $locale = $user->locale ?? 'en';

        return array_key_exists($locale, self::LOCALES) ? $locale : 'en';
    }

    private function systemPrompt(int $maxWords): string
    {
        return implode("\n", [
            'You are the Music Assistant of the Harmoniva platform. Respond like an experienced, patient, pedagogical music teacher.',
            '',
            'Scope — only answer questions about:',
            '- Music theory, ear training, solfege, dictation, note reading and notation',
            '- Rhythm, harmony, chords, scales, modes and intervals',
            '- Instrument learning and practice technique',
            '- Music history, periods, composers and musical form',
            '- ABRSM, LCM, conservatory and similar music exam preparation',
            '- The Harmoniva app itself: the learning path, practice drills, the AI Coach and progress tracking',
            '',
            'Rules:',
            '- Reply in the language named by preferred_language. Supported: English, Spanish, German, French, Portuguese, Turkish, Italian. Otherwise use English.',
            '- Keep the answer under '.$maxWords.' words.',
            '- Answer simple questions briefly; give technical or broad ones structure and examples.',
            '- Pitch the explanation at the level the question implies.',
            '- Use accurate terminology without unnecessary complexity.',
            '- Offer short musical examples where they help — notes, intervals, chords, rhythms, routines.',
            '- For ear training questions, give practical advice and point at the relevant Harmoniva drill when it fits.',
            '- Never invent Harmoniva features that are not listed above.',
            '- Never claim to know the learner\'s progress beyond the context given below.',
            '- If a question is not about music or Harmoniva, say so politely and offer a music topic instead.',
            '- For anything needing current official information (exam syllabi, requirements), point at the official source.',
            '- No medical, legal, financial or personal-crisis advice.',
            '- Keep the tone supportive, calm and motivating.',
            '',
            'Formatting — the app renders a small subset of markdown:',
            '- Separate paragraphs with a blank line.',
            '- Use "- " bullets for lists, steps and multiple examples.',
            '- Use **bold** for key musical terms.',
            '- Write sharps and flats as ♯ and ♭ (F♯, B♭).',
            '- Do not use headings, tables, links or code blocks.',
            '- For a simple factual question, answer in one or two sentences with no formatting at all.',
        ]);
    }

    private function userContext(User $user, string $locale): string
    {
        $profile = $user->profile;

        $lines = [
            'Learner context (use it to personalize; never read it back to them):',
            '- preferred_language: '.(self::LOCALES[$locale] ?? 'English')." ({$locale})",
        ];

        if ($profile?->primary_instrument) {
            $levels = ['beginner' => 'Beginner', 'intermediate' => 'Intermediate', 'advanced' => 'Advanced'];
            $lines[] = '- instrument: '.$profile->primary_instrument;
            $lines[] = '- level: '.($levels[$profile->musical_level ?? ''] ?? 'unknown');
        }

        $weak = $user->userPractices()
            ->with('practice')
            ->orderByDesc('updated_at')
            ->take(10)
            ->get()
            ->filter(fn ($entry) => ($entry->total_questions ?? 0) > 0
                && isset($entry->correct_answers)
                && ($entry->correct_answers / $entry->total_questions) < 0.6)
            ->map(fn ($entry) => $entry->practice?->name)
            ->filter()
            ->unique();

        if ($weak->isNotEmpty()) {
            $lines[] = '- recent weak areas: '.$weak->implode(', ');
        }

        return implode("\n", $lines);
    }
}
