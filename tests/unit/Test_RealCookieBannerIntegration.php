<?php

namespace WP_Statistics\Tests\Integrations;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Option;
use WP_Statistics\Service\Integrations\Plugins\RealCookieBanner;
use WP_UnitTestCase;

// Real Cookie Banner is not installed in the test environment. This stands in for wp_rcb_consent_given():
// a registered service returns the visitor's choice, an unregistered one reports cookieOptIn = true.
// Defined through eval so it lands in the global namespace, where RealCookieBanner calls it.
if (!function_exists('wp_rcb_consent_given')) {
    eval('function wp_rcb_consent_given($id) { $s = isset($GLOBALS["wp_statistics_test_rcb_services"]) ? $GLOBALS["wp_statistics_test_rcb_services"] : []; return array_key_exists($id, $s) ? ["cookie" => true, "cookieOptIn" => $s[$id]] : ["cookie" => null, "cookieOptIn" => true]; }'); // phpcs:ignore Squiz.PHP.Eval.Discouraged
}

class Test_RealCookieBannerIntegration extends WP_UnitTestCase
{
    public function tearDown(): void
    {
        unset($GLOBALS['wp_statistics_test_rcb_services']);
        Option::update('hash_ips', true);
        parent::tearDown();
    }

    public function test_declined_visitor_is_not_counted_when_ips_are_hashed()
    {
        Option::update('hash_ips', true);
        $GLOBALS['wp_statistics_test_rcb_services'] = ['wp-statistics' => false];

        $this->assertFalse((new RealCookieBanner())->hasConsent());
    }

    public function test_accepted_visitor_is_counted_when_ips_are_hashed()
    {
        Option::update('hash_ips', true);
        $GLOBALS['wp_statistics_test_rcb_services'] = ['wp-statistics' => true];

        $this->assertTrue((new RealCookieBanner())->hasConsent());
    }

    public function test_data_processing_service_still_counts_when_ips_are_not_hashed()
    {
        Option::update('hash_ips', false);

        $GLOBALS['wp_statistics_test_rcb_services'] = ['wp-statistics' => false, 'wp-statistics-with-data-processing' => true];
        $this->assertTrue((new RealCookieBanner())->hasConsent());

        $GLOBALS['wp_statistics_test_rcb_services'] = ['wp-statistics' => false, 'wp-statistics-with-data-processing' => false];
        $this->assertFalse((new RealCookieBanner())->hasConsent());
    }

    public function test_declined_visitor_is_not_counted_when_the_data_processing_service_was_never_added()
    {
        // IPs not hashed, but the owner only created the base service in Real Cookie Banner.
        Option::update('hash_ips', false);
        $GLOBALS['wp_statistics_test_rcb_services'] = ['wp-statistics' => false];

        $this->assertFalse((new RealCookieBanner())->hasConsent());
    }

    public function test_accepted_visitor_is_tracked_anonymously_when_the_data_processing_service_was_never_added()
    {
        Option::update('hash_ips', false);
        $GLOBALS['wp_statistics_test_rcb_services'] = ['wp-statistics' => true];

        $this->assertTrue((new RealCookieBanner())->hasConsent());
        $this->assertTrue((new RealCookieBanner())->trackAnonymously());
    }

    public function test_full_tracking_when_both_services_are_accepted()
    {
        Option::update('hash_ips', false);
        $GLOBALS['wp_statistics_test_rcb_services'] = ['wp-statistics' => true, 'wp-statistics-with-data-processing' => true];

        $this->assertFalse((new RealCookieBanner())->trackAnonymously());
    }
}
