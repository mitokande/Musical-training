<?php

namespace App\Services\Auth;

use App\Exceptions\Api\ApiException;

/**
 * Verifies the identity token a native Sign in with Apple hands the mobile app.
 *
 * Deliberately the same shape as GoogleIdTokenVerifier — same checks, same
 * single refusal message — because the two are the same security boundary and
 * drifting apart is how one of them quietly stops checking something.
 *
 * What makes a token acceptable:
 *   • the signature, against Apple's published keys, and `exp`/`nbf` with it.
 *     AppleJwks owns all of that, and the notification endpoint leans on the
 *     same class;
 *   • `iss`, which for Apple is one exact string;
 *   • `aud`, the bundle identifier — the check that stops a token minted for
 *     another developer's app being spent on an account of ours.
 *
 * Not checked, on purpose: `nonce`. The app does not send one, and a nonce is
 * only worth having if it is generated server-side and remembered — checking a
 * value the caller chose would prove nothing. The audience check is what stops
 * a token being replayed from elsewhere.
 */
class AppleIdTokenVerifier
{
    public function __construct(private readonly AppleJwks $jwks) {}

    public function verify(string $identityToken): AppleIdentity
    {
        if ($this->jwks->audiences() === []) {
            throw new ApiException(
                'apple_unavailable',
                __('Apple sign-in is not configured on this server.'),
                503,
            );
        }

        $claims = $this->jwks->decode($identityToken);

        if ($claims === null) {
            throw $this->rejected();
        }

        if (($claims['iss'] ?? '') !== AppleJwks::ISSUER) {
            throw $this->rejected();
        }

        if (! $this->jwks->accepts($claims['aud'] ?? null)) {
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
