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
 * Verifies the ID token a native Google Sign-In hands the mobile app.
 *
 * The web app never needs this: Socialite's redirect flow talks to Google
 * server-to-server, so the browser is never trusted with anything. The app
 * signs in on the device and posts the resulting ID token here, which means
 * this class is the whole security boundary — without the checks below, any
 * caller could claim any address and be handed a session for it.
 *
 * Four things make a token acceptable, and all four matter:
 *   • the RS256 signature, against Google's published keys;
 *   • `iss`, so a token from somewhere else cannot be replayed here;
 *   • `aud`, so a token minted for a *different* app — one the attacker
 *     controls — cannot be spent against this account. This is the check
 *     that is easy to leave out and expensive to leave out;
 *   • `email_verified`, since the account is matched by address.
 *
 * `exp`/`nbf` come free with the decode, with a minute of leeway for clock
 * drift between the phone and this server.
 */
class GoogleIdTokenVerifier
{
    private const CERTS_URL = 'https://www.googleapis.com/oauth2/v3/certs';

    private const CACHE_KEY = 'google.oauth.jwks';

    /** Both spellings are current; Google still mints tokens with each. */
    private const ISSUERS = ['https://accounts.google.com', 'accounts.google.com'];

    public function verify(string $idToken): GoogleIdentity
    {
        $audiences = $this->audiences();

        if ($audiences === []) {
            throw new ApiException(
                'google_unavailable',
                __('Google sign-in is not configured on this server.'),
                503,
            );
        }

        $claims = $this->decode($idToken, refresh: false);

        if (! in_array($claims['iss'] ?? '', self::ISSUERS, true)) {
            throw $this->rejected();
        }

        if (! in_array($claims['aud'] ?? '', $audiences, true)) {
            throw $this->rejected();
        }

        $email = $claims['email'] ?? null;
        $subject = $claims['sub'] ?? null;

        // An unverified address would let someone claim an account they only
        // typed the address of — the one Google claim that is not a fact.
        $verified = filter_var($claims['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (! $subject || ! $email || ! $verified) {
            throw $this->rejected();
        }

        return new GoogleIdentity(
            id: (string) $subject,
            email: strtolower((string) $email),
            name: $claims['name'] ?? null,
            avatar: $claims['picture'] ?? null,
        );
    }

    /**
     * Every client id that may legitimately have minted the token.
     *
     * The web client id is included because that is what the app passes as its
     * `webClientId`/`serverClientId`, which is the audience Android puts in the
     * token. iOS signs in against its own client id, so that one is listed
     * separately in GOOGLE_MOBILE_CLIENT_IDS.
     *
     * @return list<string>
     */
    private function audiences(): array
    {
        $ids = array_merge(
            [config('services.google.client_id')],
            config('services.google.mobile_client_ids', []),
        );

        return array_values(array_unique(array_filter(array_map(
            static fn ($id) => is_string($id) ? trim($id) : null,
            $ids,
        ))));
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(string $idToken, bool $refresh): array
    {
        $leeway = JWT::$leeway;
        JWT::$leeway = 60;

        try {
            return (array) JWT::decode($idToken, JWK::parseKeySet($this->keys($refresh)));
        } catch (ExpiredException|BeforeValidException) {
            // Fetching the keys again cannot make a stale token fresh.
            throw $this->rejected();
        } catch (UnexpectedValueException) {
            // Google rotates its signing keys, so an unknown `kid` or a
            // signature that will not check out is as likely to be our cached
            // key set going stale as it is to be a bad token. Refetch once
            // before calling it a forgery.
            if (! $refresh) {
                return $this->decode($idToken, refresh: true);
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
     * Google's public signing keys, cached so a sign-in is not an extra round
     * trip. Six hours is well inside their rotation period, and `$refresh`
     * covers the window where it is not.
     *
     * @return array<string, mixed>
     */
    private function keys(bool $refresh): array
    {
        if ($refresh) {
            Cache::forget(self::CACHE_KEY);
        }

        return Cache::remember(self::CACHE_KEY, now()->addHours(6), function (): array {
            // throw: false so a Google outage lands in the 503 branch below
            // instead of escaping as a RequestException (a 500 to the app).
            $response = Http::timeout(5)->retry(2, 200, throw: false)->get(self::CERTS_URL);

            if (! $response->successful()) {
                throw new ApiException(
                    'google_unavailable',
                    __('Could not reach Google to verify your sign-in. Please try again.'),
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
            'google_token_invalid',
            __('That Google sign-in could not be verified. Please try again.'),
            422,
        );
    }
}
