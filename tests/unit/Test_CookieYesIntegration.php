<?php

namespace WP_Statistics\Tests\Integrations;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Service\Integrations\IntegrationHelper;
use WP_Statistics\Service\Integrations\Plugins\CookieYes;
use WP_UnitTestCase;

class Test_CookieYesIntegration extends WP_UnitTestCase
{
    public function tearDown(): void
    {
        unset($_COOKIE['cookieyes-consent'], $_COOKIE['cookielawinfo-checkbox-analytics'], $_COOKIE['viewed_cookie_policy']);
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
     * Legacy mode (the older "GDPR Cookie Consent" banner) stores one cookie per category.
     */
    public function test_legacy_analytics_category_decides()
    {
        $_COOKIE['viewed_cookie_policy'] = 'yes';

        $_COOKIE['cookielawinfo-checkbox-analytics'] = 'yes';
        $this->assertTrue((new CookieYes())->hasConsent());

        $_COOKIE['cookielawinfo-checkbox-analytics'] = 'no';
        $this->assertFalse((new CookieYes())->hasConsent());
    }

    /**
     * A legacy banner without an Analytics category: the answer to the banner decides.
     */
    public function test_legacy_banner_without_analytics_category()
    {
        $_COOKIE['viewed_cookie_policy'] = 'yes';
        $this->assertTrue((new CookieYes())->hasConsent());

        $_COOKIE['viewed_cookie_policy'] = 'no';
        $this->assertFalse((new CookieYes())->hasConsent());
    }

    public function test_new_mode_cookie_wins_over_legacy_cookies()
    {
        $_COOKIE['cookieyes-consent']               = 'consentid:abc,consent:yes,action:yes,necessary:yes,analytics:no';
        $_COOKIE['cookielawinfo-checkbox-analytics'] = 'yes';

        $this->assertFalse((new CookieYes())->hasConsent());
    }
}
