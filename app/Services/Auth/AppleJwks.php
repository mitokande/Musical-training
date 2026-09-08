<?php

namespace App\Services\Auth;

use App\Exceptions\Api\ApiException;
use DomainException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * Apple's public signing keys, and the one way this codebase opens anything
 * Apple signed.
 *
 * Two things arrive signed by Apple and both are checked here: the identity
 * token a phone brings back from Sign in with Apple (AppleIdTokenVerifier),
 * and the payload of a server-to-server notification
 * (AppleNotificationController). They were one class until the second one
 * turned up; splitting the key handling out is what stops the two drifting —
 * a cache that is refreshed on rotation in one place and not the other would
 * fail intermittently, months apart, in whichever half was written second.
 *
 * What this class does *not* do is decide what the claims mean. It returns
 * them, or null; `iss`, `aud` and everything after that belongs to the caller,
 * because the two callers do not agree on what an acceptable claim set looks
 * like.
 */
class AppleJwks
{
    private const KEYS_URL = 'https://appleid.apple.com/auth/keys';

    private const CACHE_KEY = 'apple.oauth.jwks';

    /** Apple's one issuer string, for callers to check `iss` against. */
    public const ISSUER = 'https://appleid.apple.com';

    /**
     * The claims of a JWT Apple actually signed, or null if it could not be
     * verified — expired, forged, malformed, signed with a key Apple does not
     * publish. Null covers every one of those on purpose: which check a token
     * tripped is a detail an attacker would enjoy and a caller cannot use.
     *
     * Throws (503) only when Apple's keys could not be fetched at all, which
     * is not the token's fault and is worth telling the caller apart from a
     * refusal — one is worth retrying and the other never will be.
     *
     * @return array<string, mixed>|null
     */
    public function decode(string $jwt): ?array
    {
        return $this->attempt($jwt, refresh: false);
    }

    /**
     * Every audience this server accepts — the bundle identifiers of the App
     * Store build and the dev variant, from APPLE_CLIENT_IDS.
     *
     * @return list<string>
     */
    public function audiences(): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn ($id) => is_string($id) ? trim($id) : null,
            config('services.apple.client_ids', []),
        ))));
    }

    /**
     * Whether an `aud` claim is one of ours.
     *
     * The check that matters most on anything Apple signs: Apple will happily
     * sign for *any* app, so without this a token minted for someone else's
     * app could be spent here. A single string on an app token, occasionally a
     * list on the web flow's; accepting either costs one line.
     */
    public function accepts(mixed $aud): bool
    {
        $presented = is_array($aud) ? $aud : [$aud];

        return array_intersect($presented, $this->audiences()) !== [];
    }

    /**
     * @return array<string, mixed>|null
     */
    private function attempt(string $jwt, bool $refresh): ?array
    {
        $leeway = JWT::$leeway;
        JWT::$leeway = 60;

        try {
            return (array) JWT::decode($jwt, JWK::parseKeySet($this->keys($refresh)));
        } catch (ExpiredException|BeforeValidException) {
            // Apple's tokens live ten minutes. Fetching the keys again cannot
            // make a stale one fresh.
            return null;
        } catch (UnexpectedValueException) {
            // Apple rotates its signing keys, so an unknown `kid` is as likely
            // to be our cached key set going stale as it is to be a forgery.
            // Refetch once before calling it one.
            return $refresh ? null : $this->attempt($jwt, refresh: true);
        } catch (DomainException|InvalidArgumentException) {
            // Malformed JSON in a segment, an unsupported `alg`, an empty key
            // set: none of these is a key-rotation problem, so no refetch —
            // but every one of them is still just a bad token to the caller,
            // never a server error.
            return null;
        } finally {
            JWT::$leeway = $leeway;
        }
    }

    /**
     * Cached so a sign-in is not an extra round trip. Six hours, and the one
     * refresh above covers a rotation inside the window.
     *
     * @return array<string, mixed>
     */
    private function keys(bool $refresh): array
    {
        if ($refresh) {
            Cache::forget(self::CACHE_KEY);
        }

        return Cache::remember(self::CACHE_KEY, now()->addHours(6), function (): array {
            $response = Http::timeout(5)->retry(2, 200)->get(self::KEYS_URL);

            if (! $response->successful()) {
                throw new ApiException(
                    'apple_unavailable',
                    __('Could not reach Apple to verify your sign-in. Please try again.'),
                    503,
                );
            }

            return $response->json();
        });
    }
}
