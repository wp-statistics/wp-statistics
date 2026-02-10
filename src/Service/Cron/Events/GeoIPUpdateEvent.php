<?php

namespace WP_Statistics\Service\Cron\Events;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Service\Cron\CronSchedules;

/**
 * GeoIP Database Update Cron Event.
 *
 * Downloads and updates the GeoIP database.
 *
 * @since 15.0.0
 */
class GeoIPUpdateEvent extends AbstractCronEvent
{
    /**
     * @var string
     */
    protected $hook = 'wp_statistics_geoip_hook';

    /**
     * @var string
     */
    protected $recurrence = 'monthly';

    /**
     * @var string
     */
    protected $description = 'GeoIP Database Update';

    /**
     * Check if GeoIP update should be scheduled.
     *
     * @return bool
     */
    public function shouldSchedule(): bool
    {
        // Don't schedule if using CloudFlare for geolocation
        $locationMethod = Option::getValue('geoip_location_detection_method', 'maxmind');
        if ($locationMethod === 'cf') {
            return false;
        }

        return true;
    }

    /**
     * Get the next schedule time for GeoIP update.
     *
     * @return int Timestamp for first day of next month at 8:00 AM.
     */
    protected function getNextScheduleTime(): int
    {
        $schedules = CronSchedules::getSchedules();
        return $schedules['monthly']['next_schedule'] ?? time();
    }

    /**
     * Execute the GeoIP database update.
     *
     * @return void
     */
    public function execute(): void
    {
        $locationMethod = Option::getValue('geoip_location_detection_method', 'maxmind');

        // Only update if using MaxMind or DB-IP
        if (in_array($locationMethod, ['maxmind', 'dbip'], true)) {
            GeolocationFactory::downloadDatabase();
        }
    }
}
