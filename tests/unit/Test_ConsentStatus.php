<?php

use WP_Statistics\Service\Consent\ConsentStatus;

/**
 * @group consent
 */
class Test_ConsentStatus extends WP_UnitTestCase
{
    public function test_properties_are_set()
    {
        $status = new ConsentStatus(true, false, 'functional');

        $this->assertTrue($status->hasConsent);
        $this->assertFalse($status->trackAnonymously);
        $this->assertSame('functional', $status->consentLevel);
    }

    public function test_consent_level_defaults_to_null()
    {
        $status = new ConsentStatus(false, true);

        $this->assertNull($status->consentLevel);
    }

    public function test_empty_string_consent_level_normalized_to_null()
    {
        $status = new ConsentStatus(true, false, '');

        $this->assertNull($status->consentLevel);
    }

    public function test_json_serialize_without_consent_level()
    {
        $status = new ConsentStatus(true, false);
        $json   = json_encode($status);

        $this->assertSame(
            '{"has_consent":true,"track_anonymously":false}',
            $json
        );
    }

    public function test_json_serialize_with_consent_level()
    {
        $status = new ConsentStatus(false, true, 'statistics');
        $json   = json_encode($status);

        $this->assertSame(
            '{"has_consent":false,"track_anonymously":true,"consent_level":"statistics"}',
            $json
        );
    }

    public function test_json_serialize_omits_consent_level_when_empty_string()
    {
        $status = new ConsentStatus(true, false, '');
        $json   = json_encode($status);

        $this->assertSame(
            '{"has_consent":true,"track_anonymously":false}',
            $json
        );
    }
}
