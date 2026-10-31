<?php

namespace WP_Statistics\Tests\Integrations;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Service\Integrations\IntegrationHelper;
use WP_Statistics\Service\Integrations\Plugins\CookieYes;
use WP_STATISTICS\Option;
use WP_UnitTestCase;

// CookieYes is not installed in the test environment; this stands in for its legacy-mode check.
// Defined through eval so it lands in the global namespace, where CookieYes declares it.
if (!function_exists('cky_is_legacy')) {
    eval('function cky_is_legacy() { return !empty($GLOBALS["wp_statistics_test_cky_legacy"]); }'); // phpcs:ignore Squiz.PHP.Eval.Discouraged
}

class Test_CookieYesIntegration extends WP_UnitTestCase
{
    private $previousOptions;

    public function setUp(): void
    {
        parent::setUp();
        $this->previousOptions = Option::getOptions();
    }

    public function tearDown(): void
    {
        unset($_COOKIE['cookieyes-consent'], $_COOKIE['cookielawinfo-checkbox-analytics'], $_COOKIE['viewed_cookie_policy'], $GLOBALS['wp_statistics_test_cky_legacy']);
        Option::save_options($this->previousOptions);
        parent::tearDown();
    }

    public function test_is_registered()
    {
        $this->assertInstanceOf(CookieYes::class, IntegrationHelper::getIntegration('cookieyes'));
    }

    public function test_no_cookie_means_no_consent()
    {
        $this->assertFalse((new CookieYes())->hasConsent());
    }

    public function test_analytics_yes_gives_consent()
    {
        $_COOKIE['cookieyes-consent'] = 'consentid:abc,consent:yes,action:yes,necessary:yes,functional:no,analytics:yes,performance:no,advertisement:no';

        $this->assertTrue((new CookieYes())->hasConsent());
    }

    public function test_analytics_no_or_missing_gives_no_consent()
    {
        $_COOKIE['cookieyes-consent'] = 'consentid:abc,consent:no,action:yes,necessary:yes,analytics:no';
        $this->assertFalse((new CookieYes())->hasConsent());

        $_COOKIE['cookieyes-consent'] = 'consentid:abc,consent:yes,action:yes,necessary:yes';
        $this->assertFalse((new CookieYes())->hasConsent());
    }

    /**
     * Legacy mode (the older "GDPR Cookie Consent" banner) stores one cookie per category,
     * plus viewed_cookie_policy for the answer to the banner.
     */
    public function test_legacy_needs_an_accepted_banner_and_analytics_allowed()
    {
        $GLOBALS['wp_statistics_test_cky_legacy'] = true;
        $_COOKIE['viewed_cookie_policy']           = 'yes';

        $_COOKIE['cookielawinfo-checkbox-analytics'] = 'yes';
        $this->assertTrue((new CookieYes())->hasConsent());

        $_COOKIE['cookielawinfo-checkbox-analytics'] = 'no';
        $this->assertFalse((new CookieYes())->hasConsent());

        $_COOKIE['cookielawinfo-checkbox-analytics'] = '';
        $this->assertFalse((new CookieYes())->hasConsent());
    }

    /**
     * CookieYes writes the category cookie from its default on the first page load, before
     * the visitor answers. That alone is not consent.
     */
    public function test_legacy_category_default_before_any_answer_is_not_consent()
    {
        $GLOBALS['wp_statistics_test_cky_legacy']   = true;
        $_COOKIE['cookielawinfo-checkbox-analytics'] = 'yes';

        $this->assertFalse((new CookieYes())->hasConsent());
    }

    /**
     * A legacy banner without an Analytics category: the answer to the banner decides.
     */
    public function test_legacy_banner_without_analytics_category()
    {
        $GLOBALS['wp_statistics_test_cky_legacy'] = true;

        $_COOKIE['viewed_cookie_policy'] = 'yes';
        $this->assertTrue((new CookieYes())->hasConsent());

        $_COOKIE['viewed_cookie_policy'] = 'no';
        $this->assertFalse((new CookieYes())->hasConsent());
    }

    /**
     * After moving to the newer banner, cookies left from legacy mode do not count.
     */
    public function test_leftover_legacy_cookies_are_ignored_outside_legacy_mode()
    {
        $GLOBALS['wp_statistics_test_cky_legacy']   = false;
        $_COOKIE['viewed_cookie_policy']             = 'yes';
        $_COOKIE['cookielawinfo-checkbox-analytics'] = 'yes';

        $this->assertFalse((new CookieYes())->hasConsent());
    }

    public function test_status_tells_the_tracker_about_legacy_and_anonymous_modes()
    {
        $GLOBALS['wp_statistics_test_cky_legacy'] = true;
        $this->assertTrue((new CookieYes())->getStatus()['legacy']);
        $this->assertFalse((new CookieYes())->getStatus()['anonymous_tracking']);

        $GLOBALS['wp_statistics_test_cky_legacy'] = false;
        Option::update('anonymous_tracking', true);

        $this->assertFalse((new CookieYes())->getStatus()['legacy']);
        $this->assertTrue((new CookieYes())->getStatus()['anonymous_tracking']);
    }

    public function test_new_mode_cookie_wins_over_legacy_cookies()
    {
        $_COOKIE['cookieyes-consent']                = 'consentid:abc,consent:yes,action:yes,necessary:yes,analytics:no';
        $_COOKIE['cookielawinfo-checkbox-analytics'] = 'yes';

        $this->assertFalse((new CookieYes())->hasConsent());
    }

    public function test_empty_new_mode_cookie_still_wins_over_legacy_cookies()
    {
        $_COOKIE['cookieyes-consent']                = '';
        $_COOKIE['cookielawinfo-checkbox-analytics'] = 'yes';

        $this->assertFalse((new CookieYes())->hasConsent());
    }

    public function test_anonymous_tracking_requires_an_explicit_modern_decline_and_the_opt_in()
    {
        $_COOKIE['cookieyes-consent'] = 'consentid:abc,consent:no,action:yes,necessary:yes,analytics:no';

        $this->assertFalse((new CookieYes())->trackAnonymously());

        Option::update('anonymous_tracking', true);
        $this->assertTrue((new CookieYes())->trackAnonymously());

        $_COOKIE['cookieyes-consent'] = 'consentid:abc,consent:no,action:no,necessary:yes,analytics:no';
        $this->assertFalse((new CookieYes())->trackAnonymously());

        $_COOKIE['cookieyes-consent'] = 'consentid:abc,consent:yes,action:yes,necessary:yes,analytics:yes';
        $this->assertFalse((new CookieYes())->trackAnonymously());
    }

    public function test_anonymous_tracking_counts_explicit_legacy_declines_only()
    {
        $GLOBALS['wp_statistics_test_cky_legacy'] = true;
        Option::update('anonymous_tracking', true);

        $this->assertFalse((new CookieYes())->trackAnonymously());

        $_COOKIE['viewed_cookie_policy'] = 'no';
        $this->assertTrue((new CookieYes())->trackAnonymously());

        $_COOKIE['viewed_cookie_policy']             = 'yes';
        $_COOKIE['cookielawinfo-checkbox-analytics'] = 'no';
        $this->assertTrue((new CookieYes())->trackAnonymously());

        $_COOKIE['cookielawinfo-checkbox-analytics'] = 'yes';
        $this->assertFalse((new CookieYes())->trackAnonymously());
    }
}
