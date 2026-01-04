<?php

namespace WP_Statistics\Service\Admin\SiteHealth;

use WP_Statistics\Components\Singleton;
use WP_Statistics\Globals\Option;
use WP_Statistics\Service\Geolocation\GeolocationFactory;

/**
 * Class SiteHealthTests
 *
 * Provides WP Statistics health tests for the WordPress Site Health Status page.
 *
 * @package WP_Statistics\Service\Admin\SiteHealth
 */
class SiteHealthTests extends Singleton
{
    /**
     * Whether hooks have been registered.
     *
     * @var bool
     */
    private static $registered = false;

    /**
     * Get the singleton instance and auto-register hooks.
     *
     * @return self
     */
    public static function instance(): self
    {
        $instance = parent::instance();

        if (!self::$registered) {
            self::$registered = true;
            add_filter('site_status_tests', [$instance, 'registerTests']);
        }

        return $instance;
    }

    /**
     * Register WP Statistics health tests.
     *
     * @param array $tests Existing tests.
     * @return array Modified tests array.
     */
    public function registerTests($tests)
    {
        $tests['direct']['wp_statistics_geoip_exists'] = [
            'label' => __('WP Statistics GeoIP Database', 'wp-statistics'),
            'test'  => [$this, 'testGeoIpDatabaseExists'],
        ];

        $tests['direct']['wp_statistics_geoip_freshness'] = [
            'label' => __('WP Statistics GeoIP Database Freshness', 'wp-statistics'),
            'test'  => [$this, 'testGeoIpDatabaseFreshness'],
        ];

        $tests['direct']['wp_statistics_database_schema'] = [
            'label' => __('WP Statistics Database Schema', 'wp-statistics'),
            'test'  => [$this, 'testDatabaseSchema'],
        ];

        $tests['direct']['wp_statistics_tracking'] = [
            'label' => __('WP Statistics Tracking', 'wp-statistics'),
            'test'  => [$this, 'testTrackingEnabled'],
        ];

        $tests['direct']['wp_statistics_php_extensions'] = [
            'label' => __('WP Statistics PHP Extensions', 'wp-statistics'),
            'test'  => [$this, 'testPhpExtensions'],
        ];

        return $tests;
    }

    /**
     * Test if GeoIP database exists and is valid.
     *
     * @return array Test result.
     */
    public function testGeoIpDatabaseExists()
    {
        $geoIpProvider = GeolocationFactory::getProviderInstance();
        $databaseExists = $geoIpProvider->isDatabaseExist();
        $validation = $geoIpProvider->validateDatabaseFile();

        if (!$databaseExists) {
            return [
                'label'       => __('GeoIP database is missing', 'wp-statistics'),
                'status'      => 'critical',
                'badge'       => [
                    'label' => __('WP Statistics', 'wp-statistics'),
                    'color' => 'red',
                ],
                'description' => sprintf(
                    '<p>%s</p>',
                    __('The GeoIP database file is missing. Location detection for visitors will not work. Please download the database from WP Statistics settings.', 'wp-statistics')
                ),
                'actions'     => sprintf(
                    '<p><a href="%s">%s</a></p>',
                    esc_url(admin_url('admin.php?page=wps_settings_page&tab=externals-settings')),
                    __('Go to Settings', 'wp-statistics')
                ),
                'test'        => 'wp_statistics_geoip_exists',
            ];
        }

        if (is_wp_error($validation)) {
            return [
                'label'       => __('GeoIP database is invalid', 'wp-statistics'),
                'status'      => 'critical',
                'badge'       => [
                    'label' => __('WP Statistics', 'wp-statistics'),
                    'color' => 'red',
                ],
                'description' => sprintf(
                    '<p>%s</p><p>%s</p>',
                    __('The GeoIP database file exists but appears to be corrupted or invalid.', 'wp-statistics'),
                    esc_html($validation->get_error_message())
                ),
                'actions'     => sprintf(
                    '<p><a href="%s">%s</a></p>',
                    esc_url(admin_url('admin.php?page=wps_settings_page&tab=externals-settings')),
                    __('Re-download Database', 'wp-statistics')
                ),
                'test'        => 'wp_statistics_geoip_exists',
            ];
        }

        return [
            'label'       => __('GeoIP database is installed and valid', 'wp-statistics'),
            'status'      => 'good',
            'badge'       => [
                'label' => __('WP Statistics', 'wp-statistics'),
                'color' => 'blue',
            ],
            'description' => sprintf(
                '<p>%s</p>',
                __('The GeoIP database is properly installed and validated. Visitor location detection is working.', 'wp-statistics')
            ),
            'test'        => 'wp_statistics_geoip_exists',
        ];
    }

    /**
     * Test if GeoIP database is fresh (updated within 30 days).
     *
     * @return array Test result.
     */
    public function testGeoIpDatabaseFreshness()
    {
        $geoIpProvider = GeolocationFactory::getProviderInstance();

        if (!$geoIpProvider->isDatabaseExist()) {
            return [
                'label'       => __('GeoIP database freshness check skipped', 'wp-statistics'),
                'status'      => 'recommended',
                'badge'       => [
                    'label' => __('WP Statistics', 'wp-statistics'),
                    'color' => 'orange',
                ],
                'description' => sprintf(
                    '<p>%s</p>',
                    __('Cannot check freshness because the GeoIP database is not installed.', 'wp-statistics')
                ),
                'test'        => 'wp_statistics_geoip_freshness',
            ];
        }

        $lastUpdated = $geoIpProvider->getLastDatabaseFileUpdated();
        $daysOld = 0;

        if ($lastUpdated && $lastUpdated !== '-') {
            $lastUpdatedTime = strtotime($lastUpdated);
            if ($lastUpdatedTime) {
                $daysOld = floor((time() - $lastUpdatedTime) / DAY_IN_SECONDS);
            }
        }

        if ($daysOld > 30) {
            return [
                'label'       => __('GeoIP database is outdated', 'wp-statistics'),
                'status'      => 'recommended',
                'badge'       => [
                    'label' => __('WP Statistics', 'wp-statistics'),
                    'color' => 'orange',
                ],
                'description' => sprintf(
                    '<p>%s</p>',
                    sprintf(
                        __('The GeoIP database was last updated %d days ago. For accurate location detection, consider updating it regularly.', 'wp-statistics'),
                        $daysOld
                    )
                ),
                'actions'     => sprintf(
                    '<p><a href="%s">%s</a></p>',
                    esc_url(admin_url('admin.php?page=wps_settings_page&tab=externals-settings')),
                    __('Update Database', 'wp-statistics')
                ),
                'test'        => 'wp_statistics_geoip_freshness',
            ];
        }

        return [
            'label'       => __('GeoIP database is up to date', 'wp-statistics'),
            'status'      => 'good',
            'badge'       => [
                'label' => __('WP Statistics', 'wp-statistics'),
                'color' => 'blue',
            ],
            'description' => sprintf(
                '<p>%s</p>',
                $daysOld > 0
                    ? sprintf(__('The GeoIP database was updated %d days ago.', 'wp-statistics'), $daysOld)
                    : __('The GeoIP database is current.', 'wp-statistics')
            ),
            'test'        => 'wp_statistics_geoip_freshness',
        ];
    }

    /**
     * Test database schema migration status.
     *
     * @return array Test result.
     */
    public function testDatabaseSchema()
    {
        $isMigrated = Option::getGroupValue('db', 'migrated', false);
        $statusDetail = Option::getGroupValue('db', 'migration_status_detail', []);

        if (!empty($statusDetail['status']) && $statusDetail['status'] === 'failed') {
            return [
                'label'       => __('Database migration failed', 'wp-statistics'),
                'status'      => 'critical',
                'badge'       => [
                    'label' => __('WP Statistics', 'wp-statistics'),
                    'color' => 'red',
                ],
                'description' => sprintf(
                    '<p>%s</p><p><strong>%s</strong> %s</p>',
                    __('The database schema migration encountered an error. Some features may not work correctly.', 'wp-statistics'),
                    __('Error:', 'wp-statistics'),
                    esc_html($statusDetail['message'] ?? __('Unknown error', 'wp-statistics'))
                ),
                'actions'     => sprintf(
                    '<p><a href="%s">%s</a></p>',
                    esc_url(admin_url('admin.php?page=wps_settings_page')),
                    __('Go to Settings', 'wp-statistics')
                ),
                'test'        => 'wp_statistics_database_schema',
            ];
        }

        if (!$isMigrated) {
            return [
                'label'       => __('Database migrations pending', 'wp-statistics'),
                'status'      => 'recommended',
                'badge'       => [
                    'label' => __('WP Statistics', 'wp-statistics'),
                    'color' => 'orange',
                ],
                'description' => sprintf(
                    '<p>%s</p>',
                    __('There are pending database migrations. Visit the admin dashboard to trigger the migration process.', 'wp-statistics')
                ),
                'actions'     => sprintf(
                    '<p><a href="%s">%s</a></p>',
                    esc_url(admin_url('admin.php?page=wps_overview_page')),
                    __('Go to Dashboard', 'wp-statistics')
                ),
                'test'        => 'wp_statistics_database_schema',
            ];
        }

        return [
            'label'       => __('Database schema is up to date', 'wp-statistics'),
            'status'      => 'good',
            'badge'       => [
                'label' => __('WP Statistics', 'wp-statistics'),
                'color' => 'blue',
            ],
            'description' => sprintf(
                '<p>%s</p>',
                __('The database schema is current and all migrations have been applied successfully.', 'wp-statistics')
            ),
            'test'        => 'wp_statistics_database_schema',
        ];
    }

    /**
     * Test if tracking is enabled.
     *
     * @return array Test result.
     */
    public function testTrackingEnabled()
    {
        $isTrackingDisabled = Option::getValue('disable_tracking', false);

        if ($isTrackingDisabled) {
            return [
                'label'       => __('Visitor tracking is disabled', 'wp-statistics'),
                'status'      => 'recommended',
                'badge'       => [
                    'label' => __('WP Statistics', 'wp-statistics'),
                    'color' => 'orange',
                ],
                'description' => sprintf(
                    '<p>%s</p>',
                    __('Visitor tracking is currently disabled. No new visitor data is being collected.', 'wp-statistics')
                ),
                'actions'     => sprintf(
                    '<p><a href="%s">%s</a></p>',
                    esc_url(admin_url('admin.php?page=wps_settings_page')),
                    __('Enable Tracking', 'wp-statistics')
                ),
                'test'        => 'wp_statistics_tracking',
            ];
        }

        return [
            'label'       => __('Visitor tracking is active', 'wp-statistics'),
            'status'      => 'good',
            'badge'       => [
                'label' => __('WP Statistics', 'wp-statistics'),
                'color' => 'blue',
            ],
            'description' => sprintf(
                '<p>%s</p>',
                __('Visitor tracking is enabled and collecting analytics data.', 'wp-statistics')
            ),
            'test'        => 'wp_statistics_tracking',
        ];
    }

    /**
     * Test PHP extensions availability.
     *
     * @return array Test result.
     */
    public function testPhpExtensions()
    {
        $hasGmp = extension_loaded('gmp');
        $hasBcmath = extension_loaded('bcmath');

        if (!$hasGmp && !$hasBcmath) {
            return [
                'label'       => __('Recommended PHP extensions missing', 'wp-statistics'),
                'status'      => 'recommended',
                'badge'       => [
                    'label' => __('WP Statistics', 'wp-statistics'),
                    'color' => 'orange',
                ],
                'description' => sprintf(
                    '<p>%s</p><p>%s</p>',
                    __('Neither GMP nor BCMath PHP extensions are installed.', 'wp-statistics'),
                    __('These extensions improve performance for IP address processing. Consider asking your host to enable at least one of them.', 'wp-statistics')
                ),
                'test'        => 'wp_statistics_php_extensions',
            ];
        }

        $installedExtensions = [];
        if ($hasGmp) {
            $installedExtensions[] = 'GMP';
        }
        if ($hasBcmath) {
            $installedExtensions[] = 'BCMath';
        }

        return [
            'label'       => __('Required PHP extensions are installed', 'wp-statistics'),
            'status'      => 'good',
            'badge'       => [
                'label' => __('WP Statistics', 'wp-statistics'),
                'color' => 'blue',
            ],
            'description' => sprintf(
                '<p>%s %s</p>',
                __('The following extensions are available:', 'wp-statistics'),
                implode(', ', $installedExtensions)
            ),
            'test'        => 'wp_statistics_php_extensions',
        ];
    }
}
