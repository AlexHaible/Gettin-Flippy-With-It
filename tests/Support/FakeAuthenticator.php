<?php

namespace Tests\Support;

use CBOR\ByteStringObject;
use CBOR\MapItem;
use CBOR\MapObject;
use CBOR\NegativeIntegerObject;
use CBOR\TextStringObject;
use CBOR\UnsignedIntegerObject;
use OpenSSLAsymmetricKey;
use RuntimeException;

/**
 * A software WebAuthn authenticator that produces genuine ES256 (P-256) registration
 * and assertion responses, so tests can drive the real passkey ceremony end to end.
 */
final class FakeAuthenticator
{
    private const FLAG_USER_PRESENT = 0x01;

    private const FLAG_USER_VERIFIED = 0x04;

    private const FLAG_ATTESTED_CREDENTIAL_DATA = 0x40;

    public readonly string $credentialId;

    private OpenSSLAsymmetricKey $privateKey;

    private int $signCount = 0;

    /** Raw user handle (the decoded `user.id` from the registration options). */
    private ?string $userHandle = null;

    public function __construct()
    {
        $this->credentialId = random_bytes(32);
        $this->privateKey = self::newKeyPair();
    }

    /**
     * Same credential id and user handle, but a different private key: an impostor
     * that knows the public identifiers of the credential but not its secret.
     */
    public function withDifferentKeyPair(): self
    {
        $clone = clone $this;
        $clone->privateKey = self::newKeyPair();

        return $clone;
    }

    /**
     * Build a navigator.credentials.create() response for the given creation options.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function register(array $options): array
    {
        $this->userHandle = self::base64UrlDecode($options['user']['id']);

        $rpId = $options['rp']['id'];
        $clientDataJson = $this->clientDataJson('webauthn.create', $options['challenge'], $rpId);

        $authenticatorData = hash('sha256', $rpId, true)
            .chr(self::FLAG_USER_PRESENT | self::FLAG_USER_VERIFIED | self::FLAG_ATTESTED_CREDENTIAL_DATA)
            .pack('N', $this->signCount)
            .$this->attestedCredentialData();

        $attestationObject = MapObject::create([
            MapItem::create(TextStringObject::create('fmt'), TextStringObject::create('none')),
            MapItem::create(TextStringObject::create('attStmt'), MapObject::create()),
            MapItem::create(TextStringObject::create('authData'), ByteStringObject::create($authenticatorData)),
        ]);

        return [
            'id' => self::base64Url($this->credentialId),
            'rawId' => self::base64Url($this->credentialId),
            'type' => 'public-key',
            'authenticatorAttachment' => 'platform',
            'clientExtensionResults' => (object) [],
            'response' => [
                'clientDataJSON' => self::base64Url($clientDataJson),
                'attestationObject' => self::base64Url((string) $attestationObject),
                'transports' => ['internal'],
            ],
        ];
    }

    /**
     * Build a navigator.credentials.get() response for the given request options.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>
     */
    public function login(array $options): array
    {
        if ($this->userHandle === null) {
            throw new RuntimeException('Register the credential before using it to log in.');
        }

        $this->signCount++;

        $rpId = $options['rpId'];
        $clientDataJson = $this->clientDataJson('webauthn.get', $options['challenge'], $rpId);

        $authenticatorData = hash('sha256', $rpId, true)
            .chr(self::FLAG_USER_PRESENT | self::FLAG_USER_VERIFIED)
            .pack('N', $this->signCount);

        // ES256 WebAuthn signatures are ASN.1 DER encoded, which is what openssl_sign emits.
        openssl_sign(
            $authenticatorData.hash('sha256', $clientDataJson, true),
            $signature,
            $this->privateKey,
            OPENSSL_ALGO_SHA256,
        ) || throw new RuntimeException('Unable to sign the assertion.');

        return [
            'id' => self::base64Url($this->credentialId),
            'rawId' => self::base64Url($this->credentialId),
            'type' => 'public-key',
            'authenticatorAttachment' => 'platform',
            'clientExtensionResults' => (object) [],
            'response' => [
                'clientDataJSON' => self::base64Url($clientDataJson),
                'authenticatorData' => self::base64Url($authenticatorData),
                'signature' => self::base64Url($signature),
                'userHandle' => self::base64Url($this->userHandle),
            ],
        ];
    }

    public static function base64Url(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    public static function base64UrlDecode(string $encoded): string
    {
        return base64_decode(strtr($encoded, '-_', '+/'), true)
            ?: throw new RuntimeException("Invalid base64url: {$encoded}");
    }

    /**
     * The browser's CollectedClientData. The origin is the HTTPS origin of the relying party:
     * webauthn-lib's default CheckOrigin step rejects any non-https origin, even for localhost.
     */
    private function clientDataJson(string $type, string $challenge, string $rpId): string
    {
        return json_encode([
            'type' => $type,
            'challenge' => $challenge,
            'origin' => "https://{$rpId}",
            'crossOrigin' => false,
        ], JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    }

    /**
     * AAGUID (16 zero bytes) + credential id length (2 bytes BE) + credential id + COSE public key.
     */
    private function attestedCredentialData(): string
    {
        return str_repeat("\0", 16)
            .pack('n', strlen($this->credentialId))
            .$this->credentialId
            .$this->cosePublicKey();
    }

    /**
     * COSE_Key for an EC2 P-256 key: {1: 2 (EC2), 3: -7 (ES256), -1: 1 (P-256), -2: x, -3: y}.
     */
    private function cosePublicKey(): string
    {
        $ec = openssl_pkey_get_details($this->privateKey)['ec'];

        return (string) MapObject::create([
            MapItem::create(UnsignedIntegerObject::create(1), UnsignedIntegerObject::create(2)),
            MapItem::create(UnsignedIntegerObject::create(3), NegativeIntegerObject::create(-7)),
            MapItem::create(NegativeIntegerObject::create(-1), UnsignedIntegerObject::create(1)),
            MapItem::create(NegativeIntegerObject::create(-2), ByteStringObject::create(str_pad($ec['x'], 32, "\0", STR_PAD_LEFT))),
            MapItem::create(NegativeIntegerObject::create(-3), ByteStringObject::create(str_pad($ec['y'], 32, "\0", STR_PAD_LEFT))),
        ]);
    }

    private static function newKeyPair(): OpenSSLAsymmetricKey
    {
        return openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]) ?: throw new RuntimeException('Unable to generate a P-256 key pair.');
    }
}
