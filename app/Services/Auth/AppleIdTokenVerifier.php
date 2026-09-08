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
 * Verifies the identity token a native Sign in with Apple hands the mobile app.
 *
 * Deliberately the same shape as GoogleIdTokenVerifier — same four checks, same
 * cached JWKS, same single refusal message — because the two are the same
 * security boundary and drifting apart is how one of them quietly stops
 * checking something. It is kept as its own class rather than folded into a
 * shared base: the differences are small but they are all in the claims, and a
 * base class would have to be parameterised by exactly the parts worth reading
 * side by side.
 *
 * What makes a token acceptable:
 *   • the RS256 signature, against Apple's published keys;
 *   • `iss`, which for Apple is one exact string;
 *   • `aud`, the bundle identifier. This is the check that matters most here:
 *     Apple will happily mint a valid token for *any* app, so without it a
 *     token obtained by some other developer's app could be spent on an
 *     account of ours. The dev build ships under a different bundle id, which
 *     is why the config takes a list;
 *   • `exp`/`nbf`, free with the decode, with a minute of leeway for drift.
 *
 * Not checked, on purpose: `nonce`. The app does not send one, and a nonce is
 * only worth having if it is generated server-side and remembered — checking a
 * value the caller chose would prove nothing. The audience check is what stops
 * a token being replayed from elsewhere.
 */
class AppleIdTokenVerifier
{
    private const KEYS_URL = 'https://appleid.apple.com/auth/keys';

    private const CACHE_KEY = 'apple.oauth.jwks';

    private const ISSUER = 'https://appleid.apple.com';

    public function verify(string $identityToken): AppleIdentity
    {
        $audiences = $this->audiences();

        if ($audiences === []) {
            throw new ApiException(
                'apple_unavailable',
                __('Apple sign-in is not configured on this server.'),
                503,
            );
        }

        $claims = $this->decode($identityToken, refresh: false);

        if (($claims['iss'] ?? '') !== self::ISSUER) {
            throw $this->rejected();
        }

        // `aud` is a single string on an app token. Apple's web flow can send a
        // list, and accepting either costs one line.
        $aud = $claims['aud'] ?? null;
        $presented = is_array($aud) ? $aud : [$aud];

        if (array_intersect($presented, $audiences) === []) {
            throw $this->rejected();
        }

        $subject = $claims['sub'] ?? null;

        if (! $subject) {
            throw $this->rejected();
        }

        // Apple sends these as the strings "true"/"false" as often as booleans.
        $verified = filter_var($claims['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $private = filter_var($claims['is_private_email'] ?? false, FILTER_VALIDATE_BOOLEAN);

        $email = $claims['email'] ?? null;

        // An unverified address is dropped rather than refused, which is the
        // one place this parts company with Google. There the address is the
        // only identifier, so an unverified one is useless; here `sub` already
        // identifies the account, so a returning learner signs in fine and only
        // a brand-new account — which needs an address — is turned away, by the
        // controller, with a message that says so.
        return new AppleIdentity(
            id: (string) $subject,
            email: $email && $verified ? strtolower((string) $email) : null,
            isPrivateEmail: $private,
        );
    }

    /**
     * Every bundle identifier that may legitimately have minted the token.
     *
     * The production app and the dev build are two ids and both are ours; a
     * Services ID would go here too if the web ever offers Apple sign-in.
     *
     * @return list<string>
     */
    private function audiences(): array
    {
        return array_values(array_unique(array_filter(array_map(
            static fn ($id) => is_string($id) ? trim($id) : null,
            config('services.apple.client_ids', []),
        ))));
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(string $identityToken, bool $refresh): array
    {
        $leeway = JWT::$leeway;
        JWT::$leeway = 60;

        try {
            return (array) JWT::decode($identityToken, JWK::parseKeySet($this->keys($refresh)));
        } catch (ExpiredException|BeforeValidException) {
            // Apple's tokens live ten minutes. Fetching the keys again cannot
            // make a stale one fresh.
            throw $this->rejected();
        } catch (UnexpectedValueException) {
            // Apple rotates its signing keys, so an unknown `kid` is as likely
            // to be our cached key set going stale as it is to be a forgery.
            // Refetch once before calling it one.
            if (! $refresh) {
                return $this->decode($identityToken, refresh: true);
            }

            throw $this->rejected();
        } catch (DomainException|InvalidArgumentException) {
            // Malformed JSON in a segment, an unsupported `alg`, an empty key
            // set: none of these is a key-rotation problem, so no refetch —
            // but every one of them is still just a bad token to the caller,
            // never a server error.
            throw $this->rejected();
        } finally {
            JWT::$leeway = $leeway;
        }
    }

    /**
     * Apple's public signing keys, cached so a sign-in is not an extra round
     * trip. Six hours, and `$refresh` covers a rotation inside the window.
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

    /**
     * One message for every way a token can fail, on purpose: which check it
     * tripped is a detail an attacker would enjoy and a learner cannot use.
     */
    private function rejected(): ApiException
    {
        return new ApiException(
            'apple_token_invalid',
            __('That Apple sign-in could not be verified. Please try again.'),
            422,
        );
    }
}
