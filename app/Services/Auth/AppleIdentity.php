<?php

namespace App\Services\Auth;

/**
 * The claims of an Apple identity token, once verified.
 *
 * The same narrow shape as GoogleIdentity, minus two things Apple does not
 * give us: there is no avatar, and there is no name — Apple hands the name to
 * the *app*, once, on the first authorisation, and never puts it in a token.
 * That is why AuthController::apple() takes the name from the request body
 * instead of from here.
 */
readonly class AppleIdentity
{
    public function __construct(
        /** Apple's stable account id — what users.apple_id stores. */
        public string $id,

        /**
         * The address, and null when there is not one we are allowed to trust:
         * the learner did not share it, or Apple flagged it unverified. Null
         * is not an error by itself — an account already matched on `id` never
         * needs it — but it is the one thing that stops a *new* account being
         * created, since every account here is keyed on an address.
         *
         * May be an `@privaterelay.appleid.com` alias when the learner chose
         * to hide theirs. That is a real, deliverable address; mail to it
         * reaches them, and it must not be treated as a second-class one.
         */
        public ?string $email,

        /** True when that address is a relay alias rather than their own. */
        public bool $isPrivateEmail,
    ) {}
}
