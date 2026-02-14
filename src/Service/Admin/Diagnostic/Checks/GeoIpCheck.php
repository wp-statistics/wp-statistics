<?php

namespace WP_Statistics\Service\Admin\Diagnostic\Checks;

use WP_Statistics\Service\Admin\Diagnostic\DiagnosticResult;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Components\Option;

/**
 * GeoIP Database Check.
 *
 * Verifies the GeoIP database exists and is up to date.
 *
 * @since 15.0.0
 */
class GeoIpCheck extends AbstractCheck
{
    /**
     * Maximum age of GeoIP database before showing warning (30 days).
     */
    private const MAX_AGE_DAYS = 30;

    /**
     * GeoIP provider instance.
     *
     * @var object|null
     */
    private $provider = null;

    /**
     * Get the GeoIP provider instance.
     *
     * @return object
     */
    private function getProvider()
    {
        if ($this->provider === null) {
            $this->provider = GeolocationFactory::getProviderInstance();
        }
        return $this->provider;
    }

    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return 'geoip';
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel(): string
    {
        return __('GeoIP Database', 'wp-statistics');
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return __('Validates the GeoIP database for visitor location detection.', 'wp-statistics');
    }

    /**
     * {@inheritDoc}
     */
    public function getHelpUrl(): ?string
    {
        return 'https://wp-statistics.com/resources/geoip-database/';
    }

    /**
     * {@inheritDoc}
     */
    public function isLightweight(): bool
    {
        return true; // File check is lightweight
    }

    /**
     * {@inheritDoc}
     */
    public function run(): DiagnosticResult
    {
        // Check if using Cloudflare geolocation instead
        if (Option::getValue('geoip_location_detection_method') === 'cf') {
            return $this->pass(
                __('Using Cloudflare geolocation (no database required).', 'wp-statistics'),
                ['provider' => 'cloudflare']
            );
        }

        // Check if database file exists
        $provider = $this->getProvider();
        if (!$provider->isDatabaseExist()) {
            return $this->fail(
                __('GeoIP database not found. Location detection will not work.', 'wp-statistics'),
                ['database_path' => $provider->getDatabasePath()]
            );
        }

        // Check database age
        $lastUpdated = $provider->getLastDatabaseFileUpdated();

        if ($lastUpdated) {
            $ageInDays = (time() - strtotime($lastUpdated)) / DAY_IN_SECONDS;

            if ($ageInDays > self::MAX_AGE_DAYS) {
                return $this->warning(
                    sprintf(
                        __('GeoIP database is %d days old. Consider updating for accurate location data.', 'wp-statistics'),
                        round($ageInDays)
                    ),
                    [
                        'last_updated' => $lastUpdated,
                        'age_days'     => round($ageInDays),
                    ]
                );
            }

            return $this->pass(
                sprintf(
                    __('Database exists and was updated %d days ago.', 'wp-statistics'),
                    round($ageInDays)
                ),
                [
                    'last_updated' => $lastUpdated,
                    'age_days'     => round($ageInDays),
                ]
            );
        }

        // Database exists but can't determine age
        return $this->pass(
            __('Database exists.', 'wp-statistics'),
            ['database_path' => $provider->getDatabasePath()]
        );
    }
}
