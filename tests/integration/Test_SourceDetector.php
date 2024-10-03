<?php

use WP_Statistics\Service\Analytics\Referrals\SourceDetector;

class Test_SourceDetector extends WP_UnitTestCase
{
    protected function setUp(): void
    {
        // Set up necessary preconditions, if any.
        parent::setUp();

        // Set home URL to example.com for testing
        add_filter('home_url', function () {
            return 'http://example.com';
        });
    }

    /**
     * Test that the constructor correctly parses direct referrals.
     */
    public function test_detects_direct_referrals()
    {
        $referrerUrl    = '';
        $pageUrl        = 'http://example.com/';

        // Inject the mock parser into the SourceDetector.
        $sourceDetector = new SourceDetector($referrerUrl, $pageUrl);

        // Test if the referral was parsed correctly.
        $this->assertEquals('Direct', $sourceDetector->getName());
        $this->assertEquals('direct', $sourceDetector->getIdentifier());
        $this->assertEquals('direct', $sourceDetector->getChannel());
    }

    /**
     * Test that the constructor correctly parses the referral from search engines.
     */
    public function test_detects_search_engine_referrals()
    {
        $referrerUrl    = 'https://google.com';
        $pageUrl        = 'http://example.com/?utm_source=test';

        // Inject the mock parser into the SourceDetector.
        $sourceDetector = new SourceDetector($referrerUrl, $pageUrl);

        // Test if the referral was parsed correctly.
        $this->assertEquals('Google', $sourceDetector->getName());
        $this->assertEquals('google', $sourceDetector->getIdentifier());
        $this->assertEquals('search', $sourceDetector->getChannel());
    }

    /**
     * Test that the constructor correctly parses the referral from search engines.
     */
    public function test_detects_paid_search_engine_referrals()
    {
        $referrerUrl    = 'https://google.com';
        $pageUrl        = 'http://example.com/?gad_source=test';

        // Inject the mock parser into the SourceDetector.
        $sourceDetector = new SourceDetector($referrerUrl, $pageUrl);

        // Test if the referral was parsed correctly.
        $this->assertEquals('Google Ads', $sourceDetector->getName());
        $this->assertEquals('google_ads', $sourceDetector->getIdentifier());
        $this->assertEquals('paid_search', $sourceDetector->getChannel());
    }

    /**
     * Test if null is returned for unknown referrals.
     */
    public function test_returns_null_for_unknown_referrals()
    {
        $referrerUrl    = 'https://unknown.com';
        $pageUrl        = 'http://example.com';

        // Inject the mock parser into the SourceDetector.
        $sourceDetector = new SourceDetector($referrerUrl, $pageUrl);

        // Test if null is returned when data is missing.
        $this->assertNull($sourceDetector->getName());
        $this->assertNull($sourceDetector->getIdentifier());
        $this->assertNull($sourceDetector->getChannel());
    }

    /**
     * Test if null is returned self referrals.
     */
    public function test_returns_null_for_self_referrals()
    {
        $referrerUrl    = 'http://example.com/hello-world';
        $pageUrl        = 'http://example.com?gad_source=test';

        // Inject the mock parser into the SourceDetector.
        $sourceDetector = new SourceDetector($referrerUrl, $pageUrl);

        // Test if null is returned when data is missing.
        $this->assertNull($sourceDetector->getName());
        $this->assertNull($sourceDetector->getIdentifier());
        $this->assertNull($sourceDetector->getChannel());
    }
}
