<?php

namespace WP_Statistics\Service\Admin\TrackerUsageData;

use WP_Statistics\Components\Event;
use WP_STATISTICS\Option;

class TrackerUsageDataManager
{
    /**
     * TrackerUsageDataManager constructor.
     *
     * This method hooks into the 'cron_schedules' filter to add a custom cron interval,
     * and schedules a cron event to run the 'trackerUsageData' method every two months.
     */
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerActions']);

        if (Option::get('enable_usage_tracking')) {
            add_filter('cron_schedules', [$this, 'trackerUsageDataCronIntervalsHook']);
            Event::schedule('wp_statistics_tracker_usage_data_hook', time(), 'every_two_months', [$this, 'sendTrackerUsageData']);
        }
    }

    /**
     * Registers a custom cron schedule for tracker usage data
     *
     * @param array $schedules Existing cron schedules.
     *
     * @return array Modified cron schedules with an added "every_two_months" interval.
     */
    public function trackerUsageDataCronIntervalsHook($schedules)
    {
        $schedules['every_two_months'] = array(
            'interval' => 60 * 60 * 24 * 60,
            'display'  => __('Every 2 Months', 'wp-statistics')
        );
        return $schedules;
    }

    /**
     * Sends tracker usage data to the remote API.
     */
    public function sendTrackerUsageData()
    {
        $trackerUsageDataSender = new TrackerUsageDataSender();

        $trackerUsageDataSender->sendTrackerUsageData($this->getTrackerUsageData());
    }

    /**
     * Retrieve tracker usage data.
     *
     * @return array
     */
    public function getTrackerUsageData()
    {
        return [
            'domain'            => TrackerUsageDataProvider::getHomeUrl(),
            'wordpress_version' => TrackerUsageDataProvider::getWordpressVersion(),
            'php_version'       => TrackerUsageDataProvider::getPhpVersion() ?? 'not available',
            'plugin_version'    => TrackerUsageDataProvider::getPluginVersion(),
            'database_version'  => TrackerUsageDataProvider::getDatabaseVersion() ?? 'not available',
            'plugin_slug'       => TrackerUsageDataProvider::getPluginSlug(),
        ];
    }

    /**
     * Registers tracker usage data actions.
     *
     * @return void
     */
    public function registerActions()
    {
        $notificationActions = new TrackerUsageDataActions();

        $notificationActions->register();
    }
}