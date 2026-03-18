<?php

namespace WP_Statistics\Tests\ShortInitEndpoint;

use WP_UnitTestCase;
use WP_Statistics\Service\Tracking\Core\Hits;
use WP_Statistics\Service\Tracking\Core\HitRequest;
use WP_Statistics\Utils\Signature;
use WP_Statistics\Components\Ip;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Consent\TrackingLevel;

/**
 * Integration tests for the SHORTINIT endpoint behavior.
 *
 * Verifies that the hit pipeline works correctly without the full
 * Bootstrap container — simulating the direct file endpoint conditions.
 *
 * Runs in separate processes because Exclusion::check() caches its
 * result in a static property that persists across tests.
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Test_ShortInitEndpoint extends WP_UnitTestCase
{
    private $requestKeys = [
        'resource_uri_id', 'resource_id', 'resource_uri',
        'resource_type', 'referrer', 'timezone',
        'language_code', 'language_name', 'screen_width',
        'screen_height', 'user_id', 'signature', 'tracking_level',
    ];

    public function tearDown(): void
    {
        foreach ($this->requestKeys as $key) {
            unset($_REQUEST[$key]);
        }
        parent::tearDown();
    }

    private function setValidRequest(array $overrides = []): void
    {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
        }

        $defaults = [
            'resource_uri_id' => '1',
            'resource_id'     => '1',
            'resource_uri'    => base64_encode('/test'),
            'resource_type'   => 'post',
            'referrer'        => base64_encode('https://example.com'),
            'timezone'        => 'UTC',
            'language_code'   => 'en',
            'language_name'   => 'English',
            'screen_width'    => '1920',
            'screen_height'   => '1080',
            'user_id'         => '0',
        ];

        $params = array_merge($defaults, $overrides);

        $resourceType = $params['resource_type'] ?? '';
        $resourceId   = isset($params['resource_id']) ? (int) $params['resource_id'] : null;
        $userId       = (int) ($params['user_id'] ?? 0);

        $params['signature'] = Signature::generate([$resourceType, $resourceId, $userId]);

        foreach ($params as $key => $value) {
            $_REQUEST[$key] = $value;
        }
    }

    public function test_hit_records_successfully()
    {
        $this->setValidRequest();

        $hits = new Hits();
        $exclusion = $hits->record();

        $this->assertFalse($exclusion['exclusion_match']);
    }

    public function test_ip_get_storable_ip_returns_null_when_store_ip_disabled()
    {
        Option::updateValue('store_ip', false);

        $result = Ip::getStorableIp();

        $this->assertNull($result);
    }

    public function test_ip_get_storable_ip_returns_ip_when_store_ip_enabled()
    {
        Option::updateValue('store_ip', true);

        $result = Ip::getStorableIp();

        $this->assertNotNull($result);
    }

    public function test_signature_verification_works()
    {
        $payload   = ['post', 1, 0];
        $signature = Signature::generate($payload);

        $this->assertTrue(Signature::check($payload, $signature));
    }

    public function test_invalid_signature_throws_403()
    {
        $this->setValidRequest();
        $_REQUEST['signature'] = 'tampered';

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(403);
        HitRequest::create();
    }

    public function test_missing_required_param_throws_400()
    {
        $this->setValidRequest();
        unset($_REQUEST['timezone']);

        $this->expectException(\ErrorException::class);
        HitRequest::create();
    }

    public function test_salt_rotation_works()
    {
        Option::updateValue('daily_salt', [
            'date' => '2020-01-01',
            'salt' => 'old_salt',
        ]);

        $salt = Ip::getSalt();

        $this->assertNotSame('old_salt', $salt);

        $updated = Option::getValue('daily_salt');
        $this->assertSame(date('Y-m-d'), $updated['date']);
    }

    public function test_exclusion_ip_match_works()
    {
        $this->setValidRequest();

        $currentIp = Ip::getCurrent();
        Option::updateValue('exclude_ip', $currentIp);

        $hits      = new Hits();
        $exception = null;

        try {
            $hits->record();
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertNotNull($exception);
        $this->assertSame(200, $exception->getCode());
    }

    // ─── Tracking Level ──────────────────────────────────────────────

    public function test_tracking_level_full_does_not_anonymize()
    {
        Option::updateValue('consent_integration', true);
        $this->setValidRequest(['tracking_level' => 'full']);

        $hit = HitRequest::create();

        $this->assertSame('full', $hit->getTrackingLevel());
        $this->assertFalse($hit->getTrackingLevel() !== TrackingLevel::FULL);
    }

    public function test_tracking_level_anonymous_should_anonymize()
    {
        Option::updateValue('consent_integration', true);
        $this->setValidRequest(['tracking_level' => 'anonymous']);

        $hit = HitRequest::create();

        $this->assertSame('anonymous', $hit->getTrackingLevel());
        $this->assertTrue($hit->getTrackingLevel() !== TrackingLevel::FULL);
    }

    public function test_tracking_level_defaults_to_full_without_consent_provider()
    {
        Option::updateValue('consent_integration', false);
        $this->setValidRequest();

        $hit = HitRequest::create();

        $this->assertSame('full', $hit->getTrackingLevel());
    }

    public function test_tracking_level_defaults_to_none_with_consent_provider()
    {
        Option::updateValue('consent_integration', true);
        $this->setValidRequest();

        $hit = HitRequest::create();

        $this->assertSame('none', $hit->getTrackingLevel());
        $this->assertTrue($hit->getTrackingLevel() !== TrackingLevel::FULL);
    }

    public function test_invalid_tracking_level_is_rejected()
    {
        Option::updateValue('consent_integration', false);
        $this->setValidRequest(['tracking_level' => 'garbage']);

        $hit = HitRequest::create();

        $this->assertSame('full', $hit->getTrackingLevel());
    }
}
