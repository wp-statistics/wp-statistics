<?php

namespace WP_Statistics\Service\Consent;

class ConsentStatus implements \JsonSerializable
{
    public readonly ?string $consentLevel;

    public function __construct(
        public readonly bool $hasConsent,
        public readonly bool $trackAnonymously,
        ?string $consentLevel = null,
    ) {
        $this->consentLevel = $consentLevel !== '' ? $consentLevel : null;
    }

    public function jsonSerialize(): array
    {
        $data = [
            'has_consent'       => $this->hasConsent,
            'track_anonymously' => $this->trackAnonymously,
        ];

        if ($this->consentLevel !== null) {
            $data['consent_level'] = $this->consentLevel;
        }

        return $data;
    }
}
