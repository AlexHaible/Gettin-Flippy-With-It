<?php

namespace App\Actions\Auth;

use Cose\Algorithms;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Support\Serializer;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialParameters;

/**
 * Spatie's action sends an empty `pubKeyCredParams` list, which browsers reject
 * ("No entry in pubKeyCredParams was of type public-key"). Advertise the two
 * algorithms the server-side verifier accepts by default.
 */
class GeneratePasskeyRegisterOptions extends GeneratePasskeyRegisterOptionsAction
{
    public function execute(
        HasPasskeys $authenticatable,
        bool $asJson = true,
    ): string|PublicKeyCredentialCreationOptions {
        $options = new PublicKeyCredentialCreationOptions(
            rp: $this->relatedPartyEntity(),
            user: $this->generateUserEntity($authenticatable),
            challenge: $this->challenge(),
            pubKeyCredParams: [
                PublicKeyCredentialParameters::createPk(Algorithms::COSE_ALGORITHM_ES256),
                PublicKeyCredentialParameters::createPk(Algorithms::COSE_ALGORITHM_RS256),
            ],
            authenticatorSelection: $this->authenticatorSelection(),
            attestation: PublicKeyCredentialCreationOptions::ATTESTATION_CONVEYANCE_PREFERENCE_NONE,
        );

        return $asJson ? Serializer::make()->toJson($options) : $options;
    }
}
