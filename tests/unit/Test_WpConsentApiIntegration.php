<?php

namespace WP_Statistics\Tests\Integrations;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use ReflectionClass;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\NoticeHandler\GeneralNotices;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Integrations\IntegrationHelper;
use WP_Statistics\Service\Integrations\IntegrationsManager;
use WP_Statistics\Service\Integrations\Plugins\WpConsentApi;
use WP_UnitTestCase;

/**
 * WP Consent API is not installed in the test environment. is_plugin_active() only reads the
 * active_plugins option, so the tests switch plugins on there, and registration goes through
 * the same `wp_consent_api_registered_{plugin}` filter the API's consent_api_registered() reads.
 */
class Test_WpConsentApiIntegration extends WP_UnitTestCase
{
    const CONSENT_API   = 'wp-consent-api/wp-consent-api.php';
    const COOKIE_PLUGIN = 'cookiehold/cookiehold.php';
    const CONSUMER      = 'woocommerce/woocommerce.php';

    private $activePlugins;

    public function setUp(): void
    {
        parent::setUp();
        $this->activePlugins = get_option('active_plugins', []);
    }

    public function tearDown(): void
    {
        global $pagenow;

        update_option('active_plugins', $this->activePlugins);
        Option::update('consent_integration', '');
        remove_all_filters('wp_get_consent_type');
        remove_all_filters('wp_consent_api_registered_' . self::COOKIE_PLUGIN);
        remove_all_filters('wp_consent_api_registered_' . self::CONSUMER);
        remove_all_filters('wp_statistics_consent_api_compatible_plugins');

        $notices = (new ReflectionClass(Notice::class))->getProperty('adminNotices');
        if (PHP_VERSION_ID < 80100) $notices->setAccessible(true);
        $notices->setValue(null, []);

        unset($_REQUEST['page']);
        $pagenow = 'index.php';
        set_current_screen('front');

        parent::tearDown();
    }

    private function activate(array $plugins)
    {
        update_option('active_plugins', $plugins);
    }

    /** A cookie plugin registers with WP Consent API and sets the consent type, as CookieHold does. */
    private function registerCookiePlugin()
    {
        add_filter('wp_consent_api_registered_' . self::COOKIE_PLUGIN, '__return_true');
        add_filter('wp_get_consent_type', function () {
            return 'optin';
        });
    }

    public function test_registered_cookie_plugin_counts_as_compatible()
    {
        $this->activate([self::CONSENT_API, self::COOKIE_PLUGIN]);
        $this->registerCookiePlugin();

        $integration = new WpConsentApi();

        $this->assertArrayHasKey(self::COOKIE_PLUGIN, $integration->getCompatiblePlugins());
        $this->assertTrue($integration->isSelectable());
        $this->assertSame('', $integration->getConfigurationWarning());
    }

    public function test_registered_plugin_that_only_reads_consent_is_not_a_cookie_plugin()
    {
        // WooCommerce and Site Kit register too, but no plugin answers the consent type.
        $this->activate([self::CONSENT_API, self::CONSUMER]);
        add_filter('wp_consent_api_registered_' . self::CONSUMER, '__return_true');

        $this->assertSame([], (new WpConsentApi())->getCompatiblePlugins());
    }

    public function test_registration_is_ignored_while_wp_consent_api_is_inactive()
    {
        $this->activate([self::COOKIE_PLUGIN]);
        $this->registerCookiePlugin();

        $this->assertArrayNotHasKey(self::COOKIE_PLUGIN, (new WpConsentApi())->getCompatiblePlugins());
    }

    public function test_compatible_plugins_can_be_extended_with_a_filter()
    {
        $this->activate([self::CONSENT_API]);
        add_filter('wp_statistics_consent_api_compatible_plugins', function ($plugins) {
            $plugins['my-banner/my-banner.php'] = 'My Banner';
            return $plugins;
        });

        $this->assertSame(['my-banner/my-banner.php' => 'My Banner'], (new WpConsentApi())->getCompatiblePlugins());
    }

    public function test_option_is_kept_on_admin_ajax_when_no_cookie_plugin_is_active()
    {
        $this->activate([self::CONSENT_API]);
        Option::update('consent_integration', 'wp_consent_api');
        add_filter('wp_doing_ajax', '__return_true');

        (new IntegrationsManager())->registerIntegrations();

        remove_filter('wp_doing_ajax', '__return_true');

        $this->assertSame('wp_consent_api', Option::get('consent_integration'));
        // WP Consent API is still active, so the tracker keeps asking it for consent.
        $this->assertSame('wp_consent_api', IntegrationHelper::getActiveIntegration()->getKey());
        $this->assertStringContainsString('no compatible cookie plugin is active', IntegrationHelper::getConfigurationWarning());
    }

    public function test_option_is_kept_and_visits_are_anonymous_when_wp_consent_api_is_deactivated()
    {
        $this->activate([self::CONSENT_API, self::COOKIE_PLUGIN]);
        $this->registerCookiePlugin();
        Option::update('consent_integration', 'wp_consent_api');

        $this->activate([self::COOKIE_PLUGIN]);
        (new IntegrationsManager())->registerIntegrations();

        $this->assertSame('wp_consent_api', Option::get('consent_integration'));
        $this->assertFalse(IntegrationHelper::getActiveIntegration());
        $this->assertTrue(IntegrationHelper::shouldTrackAnonymously());
        $this->assertStringContainsString('records visits anonymously', IntegrationHelper::getConfigurationWarning());
    }

    public function test_borlabs_choice_is_kept_when_its_service_is_missing()
    {
        // Borlabs Cookie's service table is not in the test environment, so the service reads as missing.
        $this->activate(['borlabs-cookie/borlabs-cookie.php']);
        Option::update('consent_integration', 'borlabs_cookie');

        (new IntegrationsManager())->registerIntegrations();

        $this->assertSame('borlabs_cookie', Option::get('consent_integration'));
        $this->assertStringContainsString('WP Statistics service is not set up in Borlabs Cookie', IntegrationHelper::getConfigurationWarning());
    }

    public function test_no_anonymous_fallback_without_a_selected_integration()
    {
        $this->activate([]);
        Option::update('consent_integration', '');

        $this->assertFalse(IntegrationHelper::shouldTrackAnonymously());
        $this->assertSame('', IntegrationHelper::getConfigurationWarning());
    }

    public function test_notice_is_shown_on_wp_statistics_pages()
    {
        global $pagenow;

        $this->activate([self::CONSENT_API]);
        Option::update('consent_integration', 'wp_consent_api');

        set_current_screen('dashboard');
        $pagenow          = 'admin.php';
        $_REQUEST['page'] = 'wps_settings_page';

        $method = (new ReflectionClass(GeneralNotices::class))->getMethod('consentIntegrationWarning');
        if (PHP_VERSION_ID < 80100) $method->setAccessible(true);
        $method->invoke(new GeneralNotices());

        ob_start();
        Notice::displayNotices();
        $output = ob_get_clean();

        $this->assertStringContainsString('Consent integration is set to WP Consent API, but no compatible cookie plugin is active', $output);
    }

    public function test_notice_is_not_shown_when_a_cookie_plugin_is_active()
    {
        global $pagenow;

        $this->activate([self::CONSENT_API, self::COOKIE_PLUGIN]);
        $this->registerCookiePlugin();
        Option::update('consent_integration', 'wp_consent_api');

        set_current_screen('dashboard');
        $pagenow          = 'admin.php';
        $_REQUEST['page'] = 'wps_settings_page';

        $method = (new ReflectionClass(GeneralNotices::class))->getMethod('consentIntegrationWarning');
        if (PHP_VERSION_ID < 80100) $method->setAccessible(true);
        $method->invoke(new GeneralNotices());

        ob_start();
        Notice::displayNotices();
        $output = ob_get_clean();

        $this->assertStringNotContainsString('Consent integration is set to', $output);
    }
}
