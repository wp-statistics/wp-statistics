<?php

namespace WP_Statistics\Tests\HitRequest;

use WP_UnitTestCase;
use WP_Statistics\Service\Tracking\HitRequest;

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
    ];

    public function tearDown(): void
    {
        foreach ($this->requestKeys as $key) {
            unset($_REQUEST[$key]);
        }
        parent::tearDown();
    }

    public function test_create_parses_new_snake_case_params()
    {
        $_REQUEST['resource_uri_id'] = '42';
        $_REQUEST['resource_id']     = '7';
        $_REQUEST['resource_uri']    = base64_encode('/hello-world?foo=bar');
        $_REQUEST['resource_type']   = 'post';
        $_REQUEST['referrer']        = base64_encode('https://google.com/search?q=test');
        $_REQUEST['timezone']        = 'America/New_York';
        $_REQUEST['language_code']   = 'en-US';
        $_REQUEST['language_name']   = 'English';
        $_REQUEST['screen_width']    = '1920';
        $_REQUEST['screen_height']   = '1080';
        $_REQUEST['user_id']         = '5';

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

    public function test_create_falls_back_to_old_param_names()
    {
        $_REQUEST['resourceUriId']    = '42';
        $_REQUEST['source_type']      = 'page';
        $_REQUEST['source_id']        = '3';
        $_REQUEST['referred']         = base64_encode('https://example.com');

        $hit = HitRequest::create();

        $this->assertSame(42, $hit->getResourceUriId());
        $this->assertSame('page', $hit->getResourceType());
        $this->assertSame(3, $hit->getResourceId());
        $this->assertSame('https://example.com', $hit->getReferrer());
    }

    public function test_new_param_takes_precedence_over_old()
    {
        $_REQUEST['resource_uri_id'] = '10';
        $_REQUEST['resourceUriId']   = '99';
        $_REQUEST['referrer']        = base64_encode('https://new.com');
        $_REQUEST['referred']        = base64_encode('https://old.com');

        $hit = HitRequest::create();

        $this->assertSame(10, $hit->getResourceUriId());
        $this->assertSame('https://new.com', $hit->getReferrer());
    }

    public function test_page_uri_fallback_for_resource_uri()
    {
        $_REQUEST['page_uri'] = base64_encode('/from-page-uri');

        $hit = HitRequest::create();

        $this->assertSame('/from-page-uri', $hit->getResourceUri());
    }

    public function test_create_returns_defaults_when_params_missing()
    {
        $hit = HitRequest::create();

        $this->assertSame(0, $hit->getResourceUriId());
        $this->assertNull($hit->getResourceId());
        $this->assertSame('', $hit->getResourceUri());
        $this->assertSame('', $hit->getResourceType());
        $this->assertSame('', $hit->getReferrer());
        $this->assertSame('', $hit->getTimezone());
        $this->assertSame('', $hit->getLanguageCode());
        $this->assertSame('', $hit->getLanguageName());
        $this->assertSame('', $hit->getScreenWidth());
        $this->assertSame('', $hit->getScreenHeight());
        $this->assertSame(0, $hit->getUserId());
    }

    public function test_resource_id_zero_is_valid_not_null()
    {
        $_REQUEST['resource_id'] = '0';

        $hit = HitRequest::create();

        $this->assertSame(0, $hit->getResourceId());
        $this->assertNotNull($hit->getResourceId());
    }

    public function test_referrer_is_url_decoded()
    {
        $_REQUEST['referrer'] = base64_encode('https://example.com/search?q=hello%20world');

        $hit = HitRequest::create();

        $this->assertSame('https://example.com/search?q=hello world', $hit->getReferrer());
    }
}
