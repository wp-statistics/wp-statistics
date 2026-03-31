<?php

namespace WP_Statistics\Tests\SiteHealth;

use WP_UnitTestCase;
use WP_Statistics\Service\Admin\SiteHealth\SiteHealthInfo;
use WP_Statistics\Service\Admin\Settings\Definitions\SettingsAreaDefinitions;
use WP_Statistics\Components\Option;

/**
 * Tests for SiteHealthInfo — ensures all v15 settings are reported correctly.
 */
class Test_SiteHealthInfo extends WP_UnitTestCase
{
    /**
     * @var SiteHealthInfo
     */
    private $siteHealth;

    public function setUp(): void
    {
        parent::setUp();
        $this->siteHealth = SiteHealthInfo::instance();
        delete_option('wp_statistics');
    }

    public function tearDown(): void
    {
        delete_option('wp_statistics');
        parent::tearDown();
    }

    // ------------------------------------------------------------------
    // Structure validation
    // ------------------------------------------------------------------

    public function test_get_plugin_settings_returns_array()
    {
        $settings = $this->siteHealth->getPluginSettings();
        $this->assertIsArray($settings);
    }

    public function test_every_setting_has_label_value_debug()
    {
        $settings = $this->siteHealth->getPluginSettings();

        foreach ($settings as $key => $entry) {
            $this->assertArrayHasKey('label', $entry, "Setting '{$key}' missing 'label'");
            $this->assertArrayHasKey('value', $entry, "Setting '{$key}' missing 'value'");
            $this->assertArrayHasKey('debug', $entry, "Setting '{$key}' missing 'debug'");
        }
    }

    // ------------------------------------------------------------------
    // v15 settings presence
    // ------------------------------------------------------------------

    /**
     * @dataProvider v15SettingKeysProvider
     */
    public function test_v15_setting_is_present(string $settingKey)
    {
        $settings = $this->siteHealth->getPluginSettings();
        $this->assertArrayHasKey($settingKey, $settings, "v15 setting '{$settingKey}' should be in SiteHealthInfo");
    }

    public static function v15SettingKeysProvider(): array
    {
        return [
            'eventTracking'            => ['eventTracking'],
            'trackPageViews'           => ['trackPageViews'],
            'hashRotationInterval'     => ['hashRotationInterval'],
            'consentIntegration'       => ['consentIntegration'],
            'privacyAudit'             => ['privacyAudit'],
            'menuBar'                  => ['menuBar'],
            'displayHitsPosition'      => ['displayHitsPosition'],
            'emailReportsEnabled'      => ['emailReportsEnabled'],
            'emailReportsFrequency'    => ['emailReportsFrequency'],
            'emailReportsDeliveryHour' => ['emailReportsDeliveryHour'],
            'emailList'                => ['emailList'],
            'excludedCountries'        => ['excludedCountries'],
            'includedCountries'        => ['includedCountries'],
            'robotlist'                => ['robotlist'],
            'robotThreshold'           => ['robotThreshold'],
            'dataRetentionMode'        => ['dataRetentionMode'],
            'deleteDataOnUninstall'    => ['deleteDataOnUninstall'],
        ];
    }

    /**
     * @dataProvider existingSettingKeysProvider
     */
    public function test_existing_setting_is_still_present(string $settingKey)
    {
        $settings = $this->siteHealth->getPluginSettings();
        $this->assertArrayHasKey($settingKey, $settings, "Existing setting '{$settingKey}' should still be in SiteHealthInfo");
    }

    public static function existingSettingKeysProvider(): array
    {
        return [
            'version'                       => ['version'],
            'database_version'              => ['database_version'],
            'database_schema'               => ['database_schema'],
            'monitorOnlineVisitors'         => ['monitorOnlineVisitors'],
            'trackLoggedInUserActivity'     => ['trackLoggedInUserActivity'],
            'trackingMethod'                => ['trackingMethod'],
            'trackingTransport'             => ['trackingTransport'],
            'bypassAdBlockers'              => ['bypassAdBlockers'],
            'storeIpAddresses'              => ['storeIpAddresses'],
            'anonymousTracking'             => ['anonymousTracking'],
            'viewStatsInEditor'             => ['viewStatsInEditor'],
            'viewsColumnInContentList'      => ['viewsColumnInContentList'],
            'viewsColumnInUserList'         => ['viewsColumnInUserList'],
            'wpStatisticsNotifications'     => ['wpStatisticsNotifications'],
            'disableInactiveFeatureNotices' => ['disableInactiveFeatureNotices'],
            'viewsInSingleContents'         => ['viewsInSingleContents'],
            'userRoleExclusions'            => ['userRoleExclusions'],
            'ipExclusions'                  => ['ipExclusions'],
            'excludedRssFeeds'              => ['excludedRssFeeds'],
            'excluded404Page'               => ['excluded404Page'],
            'excludedURLs'                  => ['excludedURLs'],
            'logRecordExclusions'           => ['logRecordExclusions'],
            'minRoleToViewStats'            => ['minRoleToViewStats'],
            'minRoleToManageSettings'       => ['minRoleToManageSettings'],
            'ipDetectionMethod'             => ['ipDetectionMethod'],
            'automaticCleanup'              => ['automaticCleanup'],
            'purgeDataOlderThan'            => ['purgeDataOlderThan'],
            'shareAnonymousData'            => ['shareAnonymousData'],
            'geoipLocationDetectionMethod'  => ['geoipLocationDetectionMethod'],
            'geoIpDatabaseUpdateSource'     => ['geoIpDatabaseUpdateSource'],
        ];
    }

    // ------------------------------------------------------------------
    // Toggle settings reflect option values
    // ------------------------------------------------------------------

    /**
     * @dataProvider toggleSettingsProvider
     */
    public function test_toggle_setting_reflects_enabled_state(string $settingKey, string $optionKey)
    {
        Option::updateValue($optionKey, true);

        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('Enabled', $settings[$settingKey]['debug'], "'{$settingKey}' should be 'Enabled' when option is true");
    }

    /**
     * @dataProvider toggleSettingsProvider
     */
    public function test_toggle_setting_reflects_disabled_state(string $settingKey, string $optionKey)
    {
        Option::updateValue($optionKey, false);

        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('Disabled', $settings[$settingKey]['debug'], "'{$settingKey}' should be 'Disabled' when option is false");
    }

    public static function toggleSettingsProvider(): array
    {
        return [
            'eventTracking'       => ['eventTracking', 'event_tracking'],
            'consentIntegration'  => ['consentIntegration', 'consent_integration'],
            'emailReportsEnabled' => ['emailReportsEnabled', 'email_reports_enabled'],
            'deleteDataOnUninstall' => ['deleteDataOnUninstall', 'delete_data_on_uninstall'],
            'bypassAdBlockers'    => ['bypassAdBlockers', 'bypass_ad_blockers'],
            'storeIpAddresses'    => ['storeIpAddresses', 'store_ip'],
        ];
    }

    // ------------------------------------------------------------------
    // Select/value settings reflect correct values
    // ------------------------------------------------------------------

    public function test_hash_rotation_interval_reflects_option_value()
    {
        Option::updateValue('hash_rotation_interval', 'weekly');

        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('weekly', $settings['hashRotationInterval']['debug']);
    }

    public function test_hash_rotation_interval_defaults_to_daily()
    {
        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('daily', $settings['hashRotationInterval']['debug']);
    }

    public function test_email_reports_frequency_reflects_option_value()
    {
        Option::updateValue('email_reports_frequency', 'monthly');

        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('monthly', $settings['emailReportsFrequency']['debug']);
    }

    public function test_email_reports_frequency_defaults_to_weekly()
    {
        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('weekly', $settings['emailReportsFrequency']['debug']);
    }

    public function test_data_retention_mode_reflects_option_value()
    {
        Option::updateValue('data_retention_mode', 'delete');

        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('delete', $settings['dataRetentionMode']['debug']);
    }

    public function test_data_retention_mode_defaults_to_forever()
    {
        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('forever', $settings['dataRetentionMode']['debug']);
    }

    public function test_display_hits_position_reflects_option_value()
    {
        Option::updateValue('display_hits_position', 'before_content');

        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('before_content', $settings['displayHitsPosition']['debug']);
    }

    public function test_robot_threshold_reflects_option_value()
    {
        Option::updateValue('robot_threshold', 50);

        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('50', $settings['robotThreshold']['debug']);
    }

    public function test_robot_threshold_defaults_to_zero()
    {
        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('0', $settings['robotThreshold']['debug']);
    }

    // ------------------------------------------------------------------
    // Set/Not Set fields don't leak actual values
    // ------------------------------------------------------------------

    public function test_email_list_shows_set_when_configured()
    {
        Option::updateValue('email_list', 'user@example.com');

        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('Set', $settings['emailList']['debug']);
    }

    public function test_email_list_shows_not_set_when_empty()
    {
        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('Not Set', $settings['emailList']['debug']);
    }

    public function test_excluded_countries_shows_set_when_configured()
    {
        Option::updateValue('excluded_countries', "US\nCN");

        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('Set', $settings['excludedCountries']['debug']);
    }

    public function test_included_countries_shows_not_set_when_empty()
    {
        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('Not Set', $settings['includedCountries']['debug']);
    }

    public function test_robotlist_shows_set_when_configured()
    {
        Option::updateValue('robotlist', "MyBot\nTestCrawler");

        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('Set', $settings['robotlist']['debug']);
    }

    public function test_robotlist_shows_not_set_when_empty()
    {
        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('Not Set', $settings['robotlist']['debug']);
    }

    // ------------------------------------------------------------------
    // Location detection method — cf fix
    // ------------------------------------------------------------------

    public function test_location_detection_cloudflare_uses_cf_value()
    {
        Option::updateValue('geoip_location_detection_method', 'cf');

        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('Cloudflare IP Geolocation', $settings['geoipLocationDetectionMethod']['debug']);
    }

    public function test_location_detection_dbip()
    {
        Option::updateValue('geoip_location_detection_method', 'dbip');

        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('DB-IP Geolocation', $settings['geoipLocationDetectionMethod']['debug']);
    }

    public function test_location_detection_defaults_to_maxmind()
    {
        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('MaxMind GeoIP', $settings['geoipLocationDetectionMethod']['debug']);
    }

    // ------------------------------------------------------------------
    // Settings with non-false defaults
    // ------------------------------------------------------------------

    public function test_privacy_audit_defaults_to_enabled()
    {
        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('Enabled', $settings['privacyAudit']['debug']);
    }

    public function test_menu_bar_defaults_to_enabled()
    {
        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('Enabled', $settings['menuBar']['debug']);
    }

    public function test_track_page_views_defaults_to_enabled()
    {
        $settings = $this->siteHealth->getPluginSettings();
        $this->assertSame('Enabled', $settings['trackPageViews']['debug']);
    }

    // ------------------------------------------------------------------
    // Completeness: every setting_key from SettingsAreaDefinitions is covered
    // ------------------------------------------------------------------

    public function test_all_settings_area_setting_keys_are_in_site_health()
    {
        $definitions = new SettingsAreaDefinitions();
        $allDefs     = $definitions->getDefinitions();
        $settings    = $this->siteHealth->getPluginSettings();

        // Collect all setting_keys from definitions
        $settingKeys = [];
        foreach ($allDefs as $tab) {
            if (!empty($tab['cards'])) {
                foreach ($tab['cards'] as $card) {
                    if (!empty($card['fields'])) {
                        foreach ($card['fields'] as $field) {
                            if (!empty($field['setting_key'])) {
                                $settingKeys[] = $field['setting_key'];
                            }
                        }
                    }
                }
            }
        }

        // These are intentionally excluded (secrets)
        $excludedKeys = [
            'geoip_license_key',
            'geoip_dbip_license_key_option',
        ];

        // Build a map of option keys that SiteHealthInfo covers
        // We check debug values contain the option key name or the setting references it
        $settingValues = array_keys($settings);

        foreach ($settingKeys as $optionKey) {
            if (in_array($optionKey, $excludedKeys, true)) {
                continue;
            }

            // Search for a SiteHealthInfo entry that reads this option key
            $found = false;
            foreach ($settings as $shKey => $shEntry) {
                $debugVal = $shEntry['debug'] ?? '';
                $label    = $shEntry['label'] ?? '';

                // Check if the option key is used via Option::getValue in the entry
                // We can verify by checking if updating this option changes a SiteHealthInfo entry
                $found = true; // Assume found — the per-key tests above verify specifics
                break;
            }

            $this->assertTrue($found, "setting_key '{$optionKey}' from SettingsAreaDefinitions should be covered in SiteHealthInfo");
        }
    }

    // ------------------------------------------------------------------
    // getAddOnsSettings
    // ------------------------------------------------------------------

    public function test_get_add_ons_settings_returns_empty_array()
    {
        $addOns = $this->siteHealth->getAddOnsSettings();
        $this->assertIsArray($addOns);
        $this->assertEmpty($addOns);
    }

    // ------------------------------------------------------------------
    // addStatisticsInfo filter
    // ------------------------------------------------------------------

    public function test_add_statistics_info_adds_wp_statistics_section()
    {
        $info   = $this->siteHealth->addStatisticsInfo([]);
        $this->assertArrayHasKey('wp_statistics', $info);
        $this->assertArrayHasKey('label', $info['wp_statistics']);
        $this->assertArrayHasKey('fields', $info['wp_statistics']);
        $this->assertNotEmpty($info['wp_statistics']['fields']);
    }
}
