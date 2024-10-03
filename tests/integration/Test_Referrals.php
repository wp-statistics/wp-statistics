<?php

use WP_Statistics\Service\Analytics\Referrals\Referrals;
use WP_Statistics\Service\Analytics\Referrals\SourceDetector;

class Test_Referrals extends WP_UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set home URL to example.com for testing
        add_filter('home_url', function () {
            return 'http://example.com';
        });
    }

    /**
     * Test getRawUrl() with internal referrer
     */
    public function test_getRawUrl_with_internal_referrer()
    {
        // Mocking $_SERVER['HTTP_REFERER']
        $_SERVER['HTTP_REFERER'] = 'http://example.com/referral';

        // Call the method and assert the result
        $result = Referrals::getRawUrl();
        $this->assertEquals('http://example.com/referral', $result);
    }

    /**
     * Test getRawUrl() with external referrer
     */
    public function test_getRawUrl_with_external_referrer()
    {
        // Mocking $_SERVER['HTTP_REFERER']
        $_SERVER['HTTP_REFERER'] = 'http://external.com';

        // Call the method and assert the result
        $result = Referrals::getRawUrl();
        $this->assertEquals('http://external.com', $result);
    }

    /**
     * Test getUrl() with an external referrer
     */
    public function test_getUrl_with_external_referrer()
    {
        $_SERVER['HTTP_REFERER'] = 'http://external.com';

        $result = Referrals::getUrl();

        $this->assertEquals('external.com', $result);
    }

    /**
     * Test getUrl() with an internal referrer
     */
    public function test_getUrl_with_internal_referrer()
    {
        $_SERVER['HTTP_REFERER'] = 'http://example.com/internal';

        $result = Referrals::getUrl();
        $this->assertEquals('', $result);
    }

    /**
     * Test getUrl() with missing referrer
     */
    public function test_getUrl_with_missing_referrer()
    {
        $_SERVER['HTTP_REFERER'] = '';

        $result = Referrals::getUrl();
        $this->assertEquals('', $result);
    }

    /**
     * Test getUrl() with query params
     */
    public function test_getUrl_with_query_params()
    {
        $_SERVER['HTTP_REFERER'] = 'https://external.com?test=1';

        $result = Referrals::getUrl();

        $this->assertEquals('external.com', $result);
    }


    /**
     * Test getUrl() without protocol
     */
    public function test_getUrl_without_protocol()
    {
        $_SERVER['HTTP_REFERER'] = 'external.com';

        $result = Referrals::getUrl();

        $this->assertEquals('external.com', $result);
    }

    /**
     * Test getUrl() with non-http protocol
     */
    public function test_getUrl_with_non_http_protocol()
    {
        $result = Referrals::getUrl('android-app://com.google.android.gm');

        $this->assertEquals('android-app://com.google.android.gm', $result);
    }

}
