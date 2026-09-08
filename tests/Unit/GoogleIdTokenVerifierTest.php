<?php

namespace Tests\Unit;

use App\Exceptions\Api\ApiException;
use App\Services\Auth\GoogleIdTokenVerifier;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * The real verifier against a fake Google: a throwaway RSA key pair stands in
 * for Google's signing key, its public half served as the JWKS document. Each
 * of the four checks (signature, issuer, audience, email_verified) gets a
 * token that fails only that check, plus the case that once produced a 500
 * in production — a token that is not a JWT at all.
 */
class GoogleIdTokenVerifierTest extends TestCase
{
    private \OpenSSLAsymmetricKey $key;

    private string $kid = 'test-key-1';

    /** @var array<string, mixed> */
    private array $jwks;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
        config()->set('services.google.client_id', 'web-client-id');
        config()->set('services.google.mobile_client_ids', ['ios-client-id']);

        $this->key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        $details = openssl_pkey_get_details($this->key);

        $b64 = static fn (string $bin) => rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');

        $this->jwks = ['keys' => [[
            'kty' => 'RSA',
            'alg' => 'RS256',
            'use' => 'sig',
            'kid' => $this->kid,
            'n' => $b64($details['rsa']['n']),
            'e' => $b64($details['rsa']['e']),
        ]]];
    }

    /**
     * Http::fake() merges stubs and the first match wins, so the outage test
     * must not have the healthy stub registered first — hence a helper rather
     * than a fake in setUp().
     */
    private function verifier(): GoogleIdTokenVerifier
    {
        Http::fake(['https://www.googleapis.com/oauth2/v3/certs' => Http::response($this->jwks)]);

        return app(GoogleIdTokenVerifier::class);
    }

    private function token(array $overrides = [], ?\OpenSSLAsymmetricKey $signWith = null): string
    {
        $claims = array_merge([
            'iss' => 'https://accounts.google.com',
            'aud' => 'web-client-id',
            'sub' => '1234567890',
            'email' => 'Ada@Example.com',
            'email_verified' => true,
            'name' => 'Ada Lovelace',
            'picture' => 'https://lh3.example/a.png',
            'iat' => time(),
            'exp' => time() + 3600,
        ], $overrides);

        $claims = array_filter($claims, static fn ($v) => $v !== null);

        return JWT::encode($claims, $signWith ?? $this->key, 'RS256', $this->kid);
    }

    private function expectRejected(): void
    {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('That Google sign-in could not be verified.');
    }

    public function test_a_genuine_token_yields_the_identity(): void
    {
        $identity = $this->verifier()->verify($this->token());

        $this->assertSame('1234567890', $identity->id);
        $this->assertSame('ada@example.com', $identity->email);
        $this->assertSame('Ada Lovelace', $identity->name);
        $this->assertSame('https://lh3.example/a.png', $identity->avatar);
    }

    public function test_the_ios_client_id_is_an_accepted_audience(): void
    {
        $identity = $this->verifier()->verify($this->token(['aud' => 'ios-client-id']));

        $this->assertSame('1234567890', $identity->id);
    }

    public function test_a_token_for_another_app_is_refused(): void
    {
        $this->expectRejected();
        $this->verifier()->verify($this->token(['aud' => 'somebody-elses-app']));
    }

    public function test_a_token_from_another_issuer_is_refused(): void
    {
        $this->expectRejected();
        $this->verifier()->verify($this->token(['iss' => 'https://evil.example']));
    }

    public function test_an_unverified_address_is_refused(): void
    {
        $this->expectRejected();
        $this->verifier()->verify($this->token(['email_verified' => false]));
    }

    public function test_an_expired_token_is_refused(): void
    {
        $this->expectRejected();
        $this->verifier()->verify($this->token(['iat' => time() - 7200, 'exp' => time() - 3600]));
    }

    public function test_a_token_signed_by_another_key_is_refused(): void
    {
        $other = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);

        $this->expectRejected();
        $this->verifier()->verify($this->token(signWith: $other));
    }

    /**
     * Not a JWT at all. php-jwt reports this with a DomainException rather
     * than the UnexpectedValueException family, which once slipped past the
     * verifier and surfaced as a 500.
     */
    public function test_garbage_is_a_rejection_not_a_server_error(): void
    {
        $this->expectRejected();
        $this->verifier()->verify('aaa.bbb.ccc');
    }

    public function test_an_unreachable_google_is_reported_as_unavailable(): void
    {
        Http::fake(['https://www.googleapis.com/oauth2/v3/certs' => Http::response('', 503)]);

        try {
            app(GoogleIdTokenVerifier::class)->verify($this->token());
            $this->fail('Expected an ApiException.');
        } catch (ApiException $e) {
            $this->assertSame('google_unavailable', $e->errorCode);
            $this->assertSame(503, $e->status);
        }
    }
}
