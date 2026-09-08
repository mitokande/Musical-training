<?php

namespace App\Services\Auth;

/**
 * The claims of a Google ID token, once verified.
 *
 * Deliberately narrow: only what the account needs. Everything here is
 * Google's word about the person, and is trusted only because
 * GoogleIdTokenVerifier checked the signature, issuer and audience first.
 */
readonly class GoogleIdentity
{
    public function __construct(
        /** Google's stable account id — what users.google_id stores. */
        public string $id,
        public string $email,
        public ?string $name,
        public ?string $avatar,
    ) {}
}
