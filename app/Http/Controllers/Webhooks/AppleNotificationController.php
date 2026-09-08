<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Account\AccountDeletionService;
use App\Services\Auth\AppleJwks;
use App\Services\EmailCenter\SuppressionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Throwable;

/**
 * Apple's server-to-server notifications for Sign in with Apple — the endpoint
 * configured in the Apple Developer portal under the primary App ID.
 *
 * Everything here happens while nobody is signed in and often long after the
 * app was last opened: an Apple ID deleted, consent withdrawn from the iOS
 * Settings screen, a relay address switched off. The app cannot report any of
 * it, and none of it shows up on the next sign-in — a revoked consent means
 * there *is* no next sign-in.
 *
 * Authenticated by Apple's own signature and nothing else. There is no shared
 * secret and no header to check: the body carries a JWS, and `AppleJwks`
 * proves Apple signed it. The audience check matters as much as it does at
 * sign-in — Apple signs for every app on the store, so a notification whose
 * `aud` is not one of ours is somebody else's account being tampered with.
 *
 * Apple retries anything that is not a 2xx, so the two failure shapes are
 * chosen deliberately: a payload that will never become valid is a 400 and is
 * dropped, while anything that might work later — Apple's keys unreachable, a
 * handler that threw — is a 5xx and comes back.
 */
class AppleNotificationController extends Controller
{
    /**
     * Apple's four event types. `email-disabled` / `email-enabled` are the
     * relay alias being switched off and on; the other two end the account's
     * relationship with us in one direction or the other.
     */
    private const TYPES = ['email-disabled', 'email-enabled', 'consent-revoked', 'account-delete'];

    public function __construct(
        private readonly AppleJwks $jwks,
        private readonly AccountDeletionService $deletions,
        private readonly SuppressionService $suppressions,
    ) {}

    public function notifications(Request $request): JsonResponse
    {
        if ($this->jwks->audiences() === []) {
            // Nothing to check an audience against, which means we cannot tell
            // Apple's notification from anyone else's. A 5xx rather than a
            // shrug: Apple will retry, and by then someone will have noticed
            // the log line and set APPLE_CLIENT_IDS.
            Log::error('Apple notification received but APPLE_CLIENT_IDS is empty.');

            return response()->json(['error' => 'not configured'], 503);
        }

        $payload = $request->input('payload');

        if (! is_string($payload) || $payload === '') {
            return response()->json(['error' => 'invalid payload'], 400);
        }

        $claims = $this->jwks->decode($payload);

        if (
            $claims === null
            || ($claims['iss'] ?? '') !== AppleJwks::ISSUER
            || ! $this->jwks->accepts($claims['aud'] ?? null)
        ) {
            return response()->json(['error' => 'invalid payload'], 400);
        }

        foreach ($this->events($claims) as $event) {
            $this->handle($event);
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * The events inside a verified payload.
     *
     * `events` is a JSON *string* nested in the JWT rather than an object —
     * Apple's own shape, not a mistake in the caller. It has always held a
     * single event in practice and is documented as one, but it is read as a
     * list either way: a payload that suddenly carries two is not worth
     * dropping one of.
     *
     * @param  array<string, mixed>  $claims
     * @return list<array<string, mixed>>
     */
    private function events(array $claims): array
    {
        $raw = $claims['events'] ?? null;

        $decoded = is_string($raw) ? json_decode($raw, true) : $raw;

        if (! is_array($decoded)) {
            return [];
        }

        // One event arrives as a map with a `type`; a list arrives as a list.
        return array_values(array_filter(
            array_is_list($decoded) ? $decoded : [$decoded],
            static fn ($event) => is_array($event),
        ));
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private function handle(array $event): void
    {
        $type = $event['type'] ?? null;
        $subject = $event['sub'] ?? null;

        if (! is_string($type) || ! in_array($type, self::TYPES, true) || ! is_string($subject) || $subject === '') {
            // An unknown type is not an error — Apple adds them, and a 5xx
            // would have it retry something we will never understand.
            return;
        }

        // A retry that arrives after the first delivery succeeded must not do
        // the work twice. A cache claim rather than a ledger table like
        // Adapty's: these events are rare, every handler below is idempotent
        // on its own, and losing a claim to a flushed cache costs a repeat of
        // work that was harmless to begin with.
        $claim = 'apple.notification:'.sha1($type.'|'.$subject.'|'.($event['event_time'] ?? ''));

        if (! Cache::add($claim, true, now()->addDays(7))) {
            return;
        }

        try {
            $this->apply($type, $subject, $event);
        } catch (Throwable $e) {
            // Release the claim so Apple's retry can try again, then let the
            // 5xx happen — the delivery is not lost, it is deferred.
            Cache::forget($claim);

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private function apply(string $type, string $subject, array $event): void
    {
        // Matched on apple_id, never on the address: the address in an event is
        // usually the relay alias, and matching on it would let anyone who
        // guessed one act on someone else's account.
        $user = User::where('apple_id', $subject)->first();

        match ($type) {
            'email-disabled' => $this->stopEmailing($event, $user),
            'email-enabled' => $this->resumeEmailing($event, $user),
            'consent-revoked' => $this->revokeConsent($user),
            'account-delete' => $this->deleteAccount($user),
            default => null,
        };
    }

    /**
     * The learner turned off forwarding for their relay alias. Mail to it now
     * bounces, so it goes on the suppression list the rest of the email centre
     * already respects.
     *
     * Note what this does not stop: transactional mail deliberately skips the
     * suppression check, so a password reset will still be attempted and still
     * bounce. That is the existing rule for suppressed addresses and not this
     * endpoint's to change — but it is why `revokeConsent` below does not lean
     * on mail reaching anyone.
     *
     * @param  array<string, mixed>  $event
     */
    private function stopEmailing(array $event, ?User $user): void
    {
        $email = $this->addressIn($event, $user);

        if (! $email) {
            return;
        }

        $this->suppressions->suppress($email, 'apple_relay_disabled', 'apple', 'Apple relay forwarding disabled by the account holder.');
    }

    /**
     * @param  array<string, mixed>  $event
     */
    private function resumeEmailing(array $event, ?User $user): void
    {
        $email = $this->addressIn($event, $user);

        if (! $email) {
            return;
        }

        // Only lifts our own entry. A hard bounce or a complaint suppressed the
        // same address for a different reason and Apple has nothing to say
        // about those.
        $this->suppressions->unsuppressReason($email, 'apple_relay_disabled');
    }

    /**
     * The learner told Apple to stop using their Apple ID with this app.
     *
     * Their sessions end here — the account stays, because this is not a
     * deletion and their progress is not Apple's to take away. An account with
     * no password has just lost its only way back in, so it gets a reset link
     * before the door closes; best effort, since a disabled relay means that
     * mail may never land.
     */
    private function revokeConsent(?User $user): void
    {
        if (! $user) {
            return;
        }

        $user->tokens()->delete();

        if (! $user->hasPassword()) {
            try {
                Password::sendResetLink(['email' => $user->email]);
            } catch (Throwable $e) {
                // Not worth failing the delivery over: Apple would retry, and
                // the retry would kill the sessions again to no purpose.
                report($e);
            }
        }

        activity('account')
            ->performedOn($user)
            ->withProperties(['source' => 'apple_notification'])
            ->log('apple_consent_revoked');
    }

    /**
     * The Apple ID itself was deleted. Same path as a learner deleting their
     * account in the app, so subscriptions are cancelled, sessions killed and
     * the identity columns released — including `apple_id`, which is why a
     * repeat delivery finds nobody and quietly does nothing.
     */
    private function deleteAccount(?User $user): void
    {
        if (! $user) {
            return;
        }

        $this->deletions->delete($user, 'Apple ID deleted by the account holder.');
    }

    /**
     * The address an email event is about.
     *
     * Apple names the alias it is talking about, which is the one that matters:
     * an account may have been re-addressed since, and it is the alias that
     * stops delivering. The account's own address is the fallback for a payload
     * that omits it.
     *
     * @param  array<string, mixed>  $event
     */
    private function addressIn(array $event, ?User $user): ?string
    {
        $email = $event['email'] ?? null;

        if (is_string($email) && $email !== '') {
            return mb_strtolower($email);
        }

        return $user?->email;
    }
}
