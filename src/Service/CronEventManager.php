<?php

namespace WP_Statistics\Service;

use WP_Statistics\Components\Event;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\MarketingCampaign\MarketingCampaignFetcher;
use WP_Statistics\Service\Admin\Notification\NotificationFetcher;

class CronEventManager
{
    /**
     * CronEventManager constructor.
     */
    public function __construct()
    {
        Event::schedule('wp_statistics_daily_cron_hook', time(), 'daily', [$this, 'handleDailyTasks']);
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
}