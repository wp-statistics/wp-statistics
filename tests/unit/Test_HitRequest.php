<?php

namespace WP_Statistics\Tests\HitRequest;

use WP_UnitTestCase;
use WP_Statistics\Service\Tracking\HitRequest;
use WP_Statistics\Utils\Signature;

class Test_HitRequest extends WP_UnitTestCase
{
    private $requestKeys = [
        'resource_uri_id', 'resourceUriId',
        'resource_id', 'source_id',
        'resource_uri', 'page_uri',
        'resource_type', 'source_type',
        'referrer', 'referred',
        'timezone',
        'language_code',
        'language_name',
        'screen_width',
        'screen_height',
        'user_id',
        'signature',
    ];

    public function tearDown(): void
    {
        foreach ($this->requestKeys as $key) {
            unset($_REQUEST[$key]);
        }
        parent::tearDown();
    }

    /**
     * Set all required params to valid defaults.
     */
    private function setValidRequest(array $overrides = []): void
    {
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

        // Generate a valid signature from the params that will be parsed
        $resourceType = $params['source_type'] ?? $params['resource_type'] ?? '';
        $resourceId   = isset($params['resource_id']) ? (int) $params['resource_id'] : (isset($params['source_id']) ? (int) $params['source_id'] : null);
        $userId       = (int) ($params['user_id'] ?? 0);

        $params['signature'] = Signature::generate([$resourceType, $resourceId, $userId]);

        foreach ($params as $key => $value) {
            $_REQUEST[$key] = $value;
        }
    }

    // ─── Parsing ──────────────────────────────────────────────────────

    public function test_create_parses_all_params()
    {
        $this->setValidRequest([
            'resource_uri_id' => '42',
            'resource_id'     => '7',
            'resource_uri'    => base64_encode('/hello-world?foo=bar'),
            'referrer'        => base64_encode('https://google.com/search?q=test'),
            'timezone'        => 'America/New_York',
            'language_code'   => 'en-US',
            'language_name'   => 'English',
            'screen_width'    => '1920',
            'screen_height'   => '1080',
            'user_id'         => '5',
        ]);

        $hit = HitRequest::create();

        $this->assertSame(42, $hit->getResourceUriId());
        $this->assertSame(7, $hit->getResourceId());
        $this->assertSame('/hello-world?foo=bar', $hit->getResourceUri());
        $this->assertSame('post', $hit->getResourceType());
        $this->assertSame('https://google.com/search?q=test', $hit->getReferrer());
        $this->assertSame('America/New_York', $hit->getTimezone());
        $this->assertSame('en-US', $hit->getLanguageCode());
        $this->assertSame('English', $hit->getLanguageName());
        $this->assertSame('1920', $hit->getScreenWidth());
        $this->assertSame('1080', $hit->getScreenHeight());
        $this->assertSame(5, $hit->getUserId());
    }

    public function test_resource_id_zero_is_valid()
    {
        $this->setValidRequest(['resource_id' => '0']);

        $hit = HitRequest::create();

        $this->assertSame(0, $hit->getResourceId());
    }

    public function test_referrer_is_url_decoded()
    {
        $this->setValidRequest([
            'referrer' => base64_encode('https://example.com/search?q=hello%20world'),
        ]);

        $hit = HitRequest::create();

        $this->assertSame('https://example.com/search?q=hello world', $hit->getReferrer());
    }

    public function test_empty_referrer_returns_empty_string()
    {
        $this->setValidRequest(['referrer' => '']);

        $hit = HitRequest::create();

        $this->assertSame('', $hit->getReferrer());
    }

    // ─── Backward Compat Fallbacks ────────────────────────────────────

    public function test_falls_back_to_old_resource_uri_id()
    {
        $this->setValidRequest();
        unset($_REQUEST['resource_uri_id']);
        $_REQUEST['resourceUriId'] = '42';

        $hit = HitRequest::create();

        $this->assertSame(42, $hit->getResourceUriId());
    }

    public function test_falls_back_to_source_type()
    {
        // Signature must match what HitRequest will parse: source_type='page'
        $this->setValidRequest(['resource_type' => 'page']);
        unset($_REQUEST['resource_type']);
        $_REQUEST['source_type'] = 'page';

        $hit = HitRequest::create();

        $this->assertSame('page', $hit->getResourceType());
    }

    public function test_falls_back_to_source_id()
    {
        // Signature must match what HitRequest will parse: source_id=3
        $this->setValidRequest(['resource_id' => '3']);
        unset($_REQUEST['resource_id']);
        $_REQUEST['source_id'] = '3';

        $hit = HitRequest::create();

        $this->assertSame(3, $hit->getResourceId());
    }

    public function test_falls_back_to_referred()
    {
        $this->setValidRequest();
        unset($_REQUEST['referrer']);
        $_REQUEST['referred'] = base64_encode('https://old.com');

        $hit = HitRequest::create();

        $this->assertSame('https://old.com', $hit->getReferrer());
    }

    public function test_falls_back_to_page_uri()
    {
        $this->setValidRequest();
        unset($_REQUEST['resource_uri']);
        $_REQUEST['page_uri'] = base64_encode('/from-page-uri');

        $hit = HitRequest::create();

        $this->assertSame('/from-page-uri', $hit->getResourceUri());
    }

    public function test_new_param_takes_precedence_over_old()
    {
        $this->setValidRequest([
            'resource_uri_id' => '10',
            'referrer'        => base64_encode('https://new.com'),
        ]);
        $_REQUEST['resourceUriId'] = '99';
        $_REQUEST['referred']      = base64_encode('https://old.com');

        $hit = HitRequest::create();

        $this->assertSame(10, $hit->getResourceUriId());
        $this->assertSame('https://new.com', $hit->getReferrer());
    }

    // ─── Validation ───────────────────────────────────────────────────

    public function test_throws_when_resource_uri_id_missing()
    {
        $this->setValidRequest();
        unset($_REQUEST['resource_uri_id']);

        $this->expectException(\ErrorException::class);
        HitRequest::create();
    }

    public function test_throws_when_resource_id_missing()
    {
        $this->setValidRequest();
        unset($_REQUEST['resource_id']);

        $this->expectException(\ErrorException::class);
        HitRequest::create();
    }

    public function test_throws_when_timezone_missing()
    {
        $this->setValidRequest();
        unset($_REQUEST['timezone']);

        $this->expectException(\ErrorException::class);
        HitRequest::create();
    }

    public function test_throws_when_language_code_missing()
    {
        $this->setValidRequest();
        unset($_REQUEST['language_code']);

        $this->expectException(\ErrorException::class);
        HitRequest::create();
    }

    public function test_throws_when_screen_width_missing()
    {
        $this->setValidRequest();
        unset($_REQUEST['screen_width']);

        $this->expectException(\ErrorException::class);
        HitRequest::create();
    }

    public function test_throws_when_signature_invalid()
    {
        $this->setValidRequest();
        $_REQUEST['signature'] = 'tampered';

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(403);
        HitRequest::create();
    }

    public function test_throws_when_signature_missing()
    {
        $this->setValidRequest();
        unset($_REQUEST['signature']);

        $this->expectException(\Exception::class);
        $this->expectExceptionCode(403);
        HitRequest::create();
    }
}
