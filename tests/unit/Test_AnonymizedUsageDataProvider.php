<?php

namespace WP_Statistics\Tests\AnonymizedUsageData;

use WP_UnitTestCase;
use WP_Statistics\Service\Admin\AnonymizedUsageData\AnonymizedUsageDataProvider;
use WP_Statistics\Service\Database\DatabaseSchema;
use WP_Statistics\Components\Option;

/**
 * Tests for AnonymizedUsageDataProvider.
 */
class Test_AnonymizedUsageDataProvider extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        delete_option('wp_statistics');
    }

    public function tearDown(): void
    {
        delete_option('wp_statistics');
        parent::tearDown();
    }

    // ------------------------------------------------------------------
    // getTablesStats
    // ------------------------------------------------------------------

    public function test_get_tables_stats_returns_array()
    {
        $stats = AnonymizedUsageDataProvider::getTablesStats();
        $this->assertIsArray($stats);
    }

    public function test_get_tables_stats_keys_are_table_keys_not_full_names()
    {
        $stats = AnonymizedUsageDataProvider::getTablesStats();

        foreach (array_keys($stats) as $key) {
            $this->assertStringNotContainsString('wp_', $key, "Key '{$key}' should be a table key, not a full table name");
        }
    }

    public function test_get_tables_stats_values_are_integers()
    {
        $stats = AnonymizedUsageDataProvider::getTablesStats();

        foreach ($stats as $key => $count) {
            $this->assertIsInt($count, "Row count for '{$key}' should be an integer");
        }
    }

    public function test_get_tables_stats_excludes_useronline()
    {
        $stats = AnonymizedUsageDataProvider::getTablesStats();
        $this->assertArrayNotHasKey('useronline', $stats);
    }

    public function test_get_tables_stats_includes_v15_core_tables()
    {
        $stats = AnonymizedUsageDataProvider::getTablesStats();

        $v15Tables = [
            'visitors',
            'sessions',
            'views',
            'resources',
            'resource_uris',
            'countries',
            'cities',
            'device_types',
            'device_browsers',
            'device_browser_versions',
            'device_oss',
            'languages',
            'timezones',
            'referrers',
            'summary',
            'summary_totals',
        ];

        foreach ($v15Tables as $table) {
            if (DatabaseSchema::tableExists(DatabaseSchema::table($table))) {
                $this->assertArrayHasKey($table, $stats, "v15 table '{$table}' should be included in stats");
            }
        }
    }

    public function test_get_tables_stats_only_includes_existing_tables()
    {
        $stats          = AnonymizedUsageDataProvider::getTablesStats();
        $existingTables = DatabaseSchema::getExistingTables(['useronline']);

        foreach (array_keys($stats) as $key) {
            $this->assertArrayHasKey($key, $existingTables, "Table '{$key}' in stats should exist in database");
        }
    }

    // ------------------------------------------------------------------
    // getLicensesInfo
    // ------------------------------------------------------------------

    public function test_get_licenses_info_returns_array()
    {
        $info = AnonymizedUsageDataProvider::getLicensesInfo();
        $this->assertIsArray($info);
    }

    public function test_get_licenses_info_has_premium_active_key()
    {
        $info = AnonymizedUsageDataProvider::getLicensesInfo();
        $this->assertArrayHasKey('premium_active', $info);
    }

    public function test_get_licenses_info_premium_active_is_bool()
    {
        $info = AnonymizedUsageDataProvider::getLicensesInfo();
        $this->assertIsBool($info['premium_active']);
    }

    public function test_get_licenses_info_reflects_premium_constant()
    {
        $info     = AnonymizedUsageDataProvider::getLicensesInfo();
        $expected = defined('WP_STATISTICS_PREMIUM_FILE');
        $this->assertSame($expected, $info['premium_active']);
    }

    // ------------------------------------------------------------------
    // getPluginSettings
    // ------------------------------------------------------------------

    public function test_get_plugin_settings_returns_main_and_addons()
    {
        $settings = AnonymizedUsageDataProvider::getPluginSettings();

        $this->assertIsArray($settings);
        $this->assertArrayHasKey('main', $settings);
        $this->assertArrayHasKey('addOns', $settings);
    }

    public function test_get_plugin_settings_main_contains_v15_keys()
    {
        $settings = AnonymizedUsageDataProvider::getPluginSettings();
        $main     = $settings['main'];

        $expectedKeys = [
            'trackPageViews',
            'hashRotationInterval',
            'consentIntegration',
            'privacyAudit',
            'menuBar',
            'displayHitsPosition',
            'emailReportsEnabled',
            'emailReportsFrequency',
            'emailReportsDeliveryHour',
            'emailList',
            'excludedCountries',
            'includedCountries',
            'robotlist',
            'robotThreshold',
            'dataRetentionMode',
            'deleteDataOnUninstall',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $main, "Settings payload should contain v15 key '{$key}'");
        }
    }

    public function test_get_plugin_settings_excludes_version_and_geoip_database_size()
    {
        $settings = AnonymizedUsageDataProvider::getPluginSettings();
        $main     = $settings['main'];

        $this->assertArrayNotHasKey('version', $main);
        $this->assertArrayNotHasKey('geoIpDatabaseSize', $main);
    }

    public function test_get_plugin_settings_does_not_expose_secrets()
    {
        $settings = AnonymizedUsageDataProvider::getPluginSettings();
        $main     = $settings['main'];

        $this->assertArrayNotHasKey('geoip_license_key', $main);
        $this->assertArrayNotHasKey('geoip_dbip_license_key_option', $main);
    }

    public function test_get_plugin_settings_email_list_reports_set_not_actual_value()
    {
        Option::updateValue('email_list', 'secret@example.com');

        $settings = AnonymizedUsageDataProvider::getPluginSettings();
        $main     = $settings['main'];

        $this->assertSame('Set', $main['emailList']);
        $this->assertStringNotContainsString('secret@example.com', $main['emailList']);
    }

    // ------------------------------------------------------------------
    // Full payload structure
    // ------------------------------------------------------------------

    public function test_get_anonymized_usage_data_has_all_top_level_keys()
    {
        $manager = new \WP_Statistics\Service\Admin\AnonymizedUsageData\AnonymizedUsageDataManager();
        $data    = $manager->getAnonymizedUsageData();

        $expectedKeys = [
            'domain',
            'wordpress_version',
            'php_version',
            'plugin_version',
            'database_version',
            'server_info',
            'theme_info',
            'plugins',
            'settings',
            'timezone',
            'language',
            'licenses_info',
            'tables_stats',
            'payload',
        ];

        foreach ($expectedKeys as $key) {
            $this->assertArrayHasKey($key, $data, "Payload missing top-level key '{$key}'");
        }
    }

    // ------------------------------------------------------------------
    // Other provider methods
    // ------------------------------------------------------------------

    public function test_get_home_url_returns_hashed_string()
    {
        $url = AnonymizedUsageDataProvider::getHomeUrl();
        $this->assertIsString($url);
        $this->assertEquals(40, strlen($url), 'Hashed domain should be 40 characters');
    }

    public function test_hash_domain_is_deterministic()
    {
        $hash1 = AnonymizedUsageDataProvider::hashDomain('example.com');
        $hash2 = AnonymizedUsageDataProvider::hashDomain('example.com');
        $this->assertSame($hash1, $hash2);
    }

    public function test_hash_domain_differs_for_different_domains()
    {
        $hash1 = AnonymizedUsageDataProvider::hashDomain('example.com');
        $hash2 = AnonymizedUsageDataProvider::hashDomain('other.com');
        $this->assertNotSame($hash1, $hash2);
    }

    public function test_get_clean_domain_strips_www_and_scheme()
    {
        $this->assertSame('example.com', AnonymizedUsageDataProvider::getCleanDomain('https://www.example.com'));
        $this->assertSame('example.com', AnonymizedUsageDataProvider::getCleanDomain('http://example.com'));
    }

    public function test_get_plugin_version_matches_constant()
    {
        $this->assertSame(WP_STATISTICS_VERSION, AnonymizedUsageDataProvider::getPluginVersion());
    }

    public function test_get_server_info_has_required_keys()
    {
        $info = AnonymizedUsageDataProvider::getServerInfo();
        $this->assertArrayHasKey('webserver', $info);
        $this->assertArrayHasKey('database_type', $info);
    }

    public function test_get_all_plugins_returns_activated_plugins_key()
    {
        $plugins = AnonymizedUsageDataProvider::getAllPlugins();
        $this->assertArrayHasKey('activated_plugins', $plugins);
        $this->assertIsArray($plugins['activated_plugins']);
    }

    public function test_get_timezone_returns_string()
    {
        $tz = AnonymizedUsageDataProvider::getTimezone();
        $this->assertIsString($tz);
        $this->assertNotEmpty($tz);
    }

    public function test_get_locale_returns_string()
    {
        $locale = AnonymizedUsageDataProvider::getLocale();
        $this->assertIsString($locale);
        $this->assertNotEmpty($locale);
    }

    public function test_get_payload_has_required_keys()
    {
        $payload = AnonymizedUsageDataProvider::getPayload();
        $this->assertArrayHasKey('plugin_database_version_legacy', $payload);
        $this->assertArrayHasKey('plugin_database_version', $payload);
        $this->assertArrayHasKey('jobs', $payload);
        $this->assertArrayHasKey('dismissed_notices', $payload);
    }
}
