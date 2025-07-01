<?php

namespace WP_Statistics\Service;

use WP_Statistics\Components\Event;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\MarketingCampaign\MarketingCampaignFetcher;
use WP_Statistics\Service\Admin\Notification\NotificationFetcher;

class CronEventManager
{
    /**
     * Array of legacy cron hook names that should be unscheduled
     * @var string[]
     * @static
     */
    private static $legacyCronHooks = [
        'wp_statistics_marketing_campaign_hook',
        'wp_statistics_notification_hook'
    ];

    /**
     * CronEventManager constructor.
     */
    public function __construct()
    {
        Event::schedule('wp_statistics_daily_cron_hook', time(), 'daily', [$this, 'handleDailyTasks']);
        $this->cleanupLegacyCronJobs();
    }

    /**
     * Handle daily tasks triggered by the scheduled cron event.
     *
     * Calls both notification and marketing campaign fetchers.
     */
    public function handleDailyTasks()
    {
        if (Option::get('display_notifications')) {
            $this->fetchNotification();
            $this->fetchMarketingCampaign();
        }
    }

    /**
     * Fetches new notifications.
     *
     * This method is triggered by the scheduled cron event
     * and retrieves new notifications.
     */
    private function fetchNotification()
    {
        $notificationFetcher = new NotificationFetcher();
        $notificationFetcher->fetchNotification();
    }

    /**
     * Fetches marketing campaign.
     *
     * This method is triggered by the scheduled cron event
     * and retrieve marketing campaign.
     */
    private function fetchMarketingCampaign()
    {
        $marketingCampaignFetcher = new MarketingCampaignFetcher();
        $marketingCampaignFetcher->fetchMarketingCampaign();
    }

    /**
     * Cleans up legacy cron jobs that are no longer needed.
     *
     * This removes the old individual cron hooks that have been consolidated
     * into the daily cron job handler.
     */
    private function cleanupLegacyCronJobs(): void
    {
        foreach (self::$legacyCronHooks as $hook) {
            if (Event::isScheduled($hook)) {
                Event::unschedule($hook);
            }
        }
    }
}