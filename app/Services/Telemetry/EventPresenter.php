<?php

namespace App\Services\Telemetry;

/**
 * One app event as a line a person can read in a journey: an icon, a tone,
 * a short title and the detail that matters.
 *
 * The vocabulary is the app's (`src/telemetry/events.ts`); an event this does
 * not know yet still reads fine — its name and its properties, as they came.
 */
final class EventPresenter
{
    /** Tailwind classes per tone: the dot, and the title. */
    public const TONES = [
        'tap' => ['bg-gray-100 text-gray-500', 'text-gray-700'],
        'nav' => ['bg-purple-100 text-purple-600', 'text-purple-800'],
        'learn' => ['bg-blue-100 text-blue-600', 'text-blue-800'],
        'money' => ['bg-green-100 text-green-600', 'text-green-800'],
        'account' => ['bg-teal-100 text-teal-600', 'text-teal-800'],
        'setting' => ['bg-amber-100 text-amber-600', 'text-amber-800'],
        'error' => ['bg-red-100 text-red-600', 'text-red-700'],
        'system' => ['bg-gray-50 text-gray-400', 'text-gray-500'],
    ];

    /**
     * @return array{icon: string, tone: string, title: string, detail: ?string}
     */
    public static function describe(string $name, array $props): array
    {
        $p = fn (string $key) => $props[$key] ?? null;
        $seconds = fn (?int $ms) => $ms === null ? null : self::duration($ms);

        return match ($name) {
            'tap' => [
                'icon' => $p('kind') === 'long' ? 'hand' : 'mouse-pointer-click',
                'tone' => 'tap',
                'title' => $p('id') ?? 'unnamed control',
                'detail' => self::extras($props, ['id', 'kind']) ?: ($p('kind') === 'long' ? 'long press' : null),
            ],
            'app_open' => [
                'icon' => 'power',
                'tone' => 'system',
                'title' => $p('cold') ? 'Launched the app' : 'Came back to the app',
                'detail' => $p('backgroundMs') !== null ? 'after '.$seconds((int) $p('backgroundMs')).' away' : null,
            ],
            'app_background' => [
                'icon' => 'moon',
                'tone' => 'system',
                'title' => 'Left the app',
                'detail' => $p('foregroundMs') !== null ? 'after '.$seconds((int) $p('foregroundMs')).' in front' : null,
            ],
            'onboarding_complete' => [
                'icon' => 'sparkles',
                'tone' => 'nav',
                'title' => 'Finished onboarding',
                'detail' => self::join([
                    implode(', ', (array) $p('goals')),
                    implode(', ', (array) $p('instruments')),
                    $p('minutesPerDay') ? $p('minutesPerDay').' min/day' : null,
                    $p('signedIn') ? null : 'not signed in yet',
                ]),
            ],
            'auth_success' => [
                'icon' => 'user-check',
                'tone' => 'account',
                'title' => $p('created') ? 'Created an account' : 'Signed in',
                'detail' => 'with '.$p('method'),
            ],
            'auth_failed' => [
                'icon' => 'user-x',
                'tone' => 'error',
                'title' => ($p('action') === 'sign_up' ? 'Sign-up' : 'Sign-in').' failed',
                'detail' => self::join([$p('method'), $p('code')]),
            ],
            'sign_out' => ['icon' => 'log-out', 'tone' => 'account', 'title' => 'Signed out', 'detail' => null],
            'paywall_view' => [
                'icon' => 'credit-card',
                'tone' => 'money',
                'title' => $p('state') === 'unavailable' ? 'Paywall had nothing to sell' : 'Saw the paywall',
                'detail' => self::join(['from '.$p('from'), $p('offers') !== null ? $p('offers').' offers' : null]),
            ],
            'paywall_plan_selected' => ['icon' => 'list-checks', 'tone' => 'money', 'title' => 'Picked a plan', 'detail' => $p('offerId')],
            'purchase_start' => ['icon' => 'shopping-cart', 'tone' => 'money', 'title' => 'Tapped buy', 'detail' => $p('offerId')],
            'purchase_result' => [
                'icon' => $p('outcome') === 'unlocked' ? 'badge-check' : 'circle-slash',
                'tone' => in_array($p('outcome'), ['error', 'not-unlocked'], true) ? 'error' : 'money',
                'title' => match ($p('outcome')) {
                    'unlocked' => 'Bought Premium',
                    'cancelled' => 'Cancelled the purchase',
                    'pending' => 'Purchase pending',
                    default => 'Purchase failed',
                },
                'detail' => self::join([$p('offerId'), $p('error')]),
            ],
            'restore_result' => ['icon' => 'rotate-ccw', 'tone' => 'money', 'title' => 'Restore: '.$p('outcome'), 'detail' => null],
            'paywall_dismiss' => [
                'icon' => 'x',
                'tone' => 'money',
                'title' => $p('via') === 'keep_free' ? 'Chose to stay free' : 'Closed the paywall',
                'detail' => 'from '.$p('from'),
            ],
            'lesson_start' => [
                'icon' => 'play',
                'tone' => 'learn',
                'title' => ucfirst((string) $p('kind')).' started',
                'detail' => self::join([$p('nodeId'), $p('attempt') > 1 ? 'attempt '.$p('attempt') : null]),
            ],
            'task_answered' => [
                'icon' => $p('correct') ? 'check' : 'x',
                'tone' => $p('correct') ? 'learn' : 'error',
                'title' => ($p('correct') ? 'Right' : 'Wrong').': '.$p('conceptId').($p('correct') ? '' : ' → picked '.$p('chosen')),
                'detail' => self::join([
                    $p('kind'),
                    $p('attemptNo') > 1 ? 'try '.$p('attemptNo') : null,
                    $p('elapsedMs') !== null ? $seconds((int) $p('elapsedMs')) : null,
                    $p('replays') ? $p('replays').' replays' : null,
                ]),
            ],
            'lesson_end' => [
                'icon' => $p('reason') === 'finished' ? 'flag' : 'log-out',
                'tone' => $p('reason') === 'finished' ? 'learn' : 'error',
                'title' => match ($p('reason')) {
                    'finished' => 'Finished '.$p('nodeId'),
                    'out-of-hearts' => 'Ran out of hearts in '.$p('nodeId'),
                    'abandoned' => 'Quit '.$p('nodeId'),
                    default => 'Reviewed '.$p('nodeId'),
                },
                'detail' => self::join([
                    $p('correct').'/'.$p('total').' right',
                    $p('score') !== null ? $p('score').'%' : null,
                    $p('reason') === 'abandoned' ? 'at step '.$p('step').' of '.$p('steps') : null,
                    $p('seconds') !== null ? $seconds((int) $p('seconds') * 1000) : null,
                ]),
            ],
            'heart_lost' => ['icon' => 'heart-crack', 'tone' => 'learn', 'title' => 'Lost a heart', 'detail' => $p('remaining').' left'],
            'hearts_zero' => ['icon' => 'heart-off', 'tone' => 'error', 'title' => 'Out of hearts', 'detail' => $p('nodeId')],
            'practice_start' => [
                'icon' => 'headphones',
                'tone' => 'learn',
                'title' => 'Started practice: '.$p('slug'),
                'detail' => self::join([$p('source'), $p('questionCount').' questions']),
            ],
            'practice_limit_hit' => ['icon' => 'lock', 'tone' => 'money', 'title' => 'Hit the free limit', 'detail' => self::join([$p('slug'), $p('wall')])],
            'coach_plan_generated' => ['icon' => 'brain', 'tone' => $p('ok') ? 'learn' : 'error', 'title' => 'AI plan '.($p('ok') ? 'generated' : 'failed'), 'detail' => $p('code')],
            'coach_message_sent' => ['icon' => 'message-circle', 'tone' => $p('ok') ? 'learn' : 'error', 'title' => 'Asked the AI coach', 'detail' => self::join([$p('length').' chars', $p('code')])],
            'setting_changed' => ['icon' => 'settings', 'tone' => 'setting', 'title' => 'Changed '.$p('key'), 'detail' => 'to '.self::scalar($p('value'))],
            'api_error' => ['icon' => 'server-crash', 'tone' => 'error', 'title' => 'Server error '.$p('status'), 'detail' => self::join([$p('method').' '.$p('path'), $p('code')])],
            'js_error' => ['icon' => 'bug', 'tone' => 'error', 'title' => ($p('fatal') ? 'Crash: ' : 'Error: ').$p('name'), 'detail' => $p('message')],
            'screen' => ['icon' => 'smartphone', 'tone' => 'nav', 'title' => (string) $p('name'), 'detail' => $p('path')],
            'onboarding_step' => ['icon' => 'footprints', 'tone' => 'nav', 'title' => 'onboarding/'.$p('beat'), 'detail' => ($p('index') + 1).' of '.$p('total')],
            default => ['icon' => 'circle-dot', 'tone' => 'system', 'title' => $name, 'detail' => self::extras($props, [])],
        };
    }

    /** `1.4s`, `12s`, `3m 05s`, `2h 10m`. */
    public static function duration(int $ms): string
    {
        if ($ms < 10_000) {
            return round($ms / 1000, 1).'s';
        }
        $s = intdiv($ms, 1000);
        if ($s < 60) {
            return $s.'s';
        }
        if ($s < 3600) {
            return intdiv($s, 60).'m '.str_pad((string) ($s % 60), 2, '0', STR_PAD_LEFT).'s';
        }

        return intdiv($s, 3600).'h '.str_pad((string) intdiv($s % 3600, 60), 2, '0', STR_PAD_LEFT).'m';
    }

    private static function join(array $parts): ?string
    {
        $parts = array_filter($parts, fn ($part) => $part !== null && $part !== '' && $part !== 'from ');

        return $parts === [] ? null : implode(' · ', $parts);
    }

    private static function scalar(mixed $value): string
    {
        return match (true) {
            $value === null => 'nothing',
            is_bool($value) => $value ? 'on' : 'off',
            is_scalar($value) => (string) $value,
            default => json_encode($value),
        };
    }

    /** The properties not already in the title, as `key=value`. */
    private static function extras(array $props, array $skip): ?string
    {
        $parts = [];
        foreach ($props as $key => $value) {
            if (! in_array($key, $skip, true)) {
                $parts[] = $key.'='.self::scalar($value);
            }
        }

        return $parts === [] ? null : implode(' · ', $parts);
    }
}
