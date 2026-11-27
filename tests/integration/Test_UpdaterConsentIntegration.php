<?php

use WP_STATISTICS\Option;
use WP_Statistics\Core\Operations\Updater;

/**
 * The update routine selects WP Consent API only for sites coming from before 14.14, when it was
 * followed automatically. Later updates keep an empty setting (None) as the admin left it (#1258).
 */
class Test_UpdaterConsentIntegration extends WP_UnitTestCase
{
    const CONSENT_API = 'wp-consent-api/wp-consent-api.php';

    private $activePlugins;

    public function setUp(): void
    {
        parent::setUp();
        $this->activePlugins = get_option('active_plugins', []);
        update_option('active_plugins', [self::CONSENT_API]);
        Option::update('consent_integration', '');
    }

    public function tearDown(): void
    {
        update_option('active_plugins', $this->activePlugins);
        Option::update('consent_integration', '');
        parent::tearDown();
    }

    private function runMigrationFrom($version)
    {
        $updater  = new Updater();
        $property = new ReflectionProperty(Updater::class, 'currentVersion');
        $method   = new ReflectionMethod(Updater::class, 'migrateConsentIntegration');
        if (PHP_VERSION_ID < 80100) {
            $property->setAccessible(true);
            $method->setAccessible(true);
        }

        $property->setValue($updater, $version);
        $method->invoke($updater);
    }

    public function test_update_from_before_14_14_selects_wp_consent_api()
    {
        $this->runMigrationFrom('14.13.5');

        $this->assertSame('wp_consent_api', Option::get('consent_integration'));
    }

    public function test_later_update_keeps_none()
    {
        foreach (['14.14', '14.16.20', '14.17.0'] as $version) {
            $this->runMigrationFrom($version);

            $this->assertSame('', (string)Option::get('consent_integration'), "Updating from {$version} must not select WP Consent API.");
        }
    }

    public function test_update_never_replaces_another_integration()
    {
        Option::update('consent_integration', 'cookieyes');

        $this->runMigrationFrom('14.13.5');

        $this->assertSame('cookieyes', Option::get('consent_integration'));
    }

    public function test_update_without_wp_consent_api_keeps_none()
    {
        update_option('active_plugins', []);

        $this->runMigrationFrom('14.13.5');

        $this->assertSame('', (string)Option::get('consent_integration'));
    }
}
