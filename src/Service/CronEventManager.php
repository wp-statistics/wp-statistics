<?php

namespace WP_Statistics\Service;

use WP_Statistics\Components\Event;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\MarketingCampaign\MarketingCampaignFetcher;
use WP_Statistics\Service\Admin\Notification\NotificationFetcher;

class CronEventManager
{
    /**
     *
     */
    public function __construct()
    {
        if (Option::get('display_notifications')) {
            Event::schedule('wp_statistics_notification_hook', time(), 'daily', [$this, 'fetchNotification']);
            Event::schedule('wp_statistics_marketing_campaign_hook', time(), 'daily', [$this, 'fetchMarketingCampaign']);
        } else {
            Event::unschedule('wp_statistics_notification_hook');
            Event::unschedule('wp_statistics_marketing_campaign_hook');
        }
    }

    /**
     * Fetches new notifications.
     *
     * This method is triggered by the scheduled cron event
     * and retrieves new notifications.
     */
    public function fetchNotification()
    {
        $notificationFetcher = new NotificationFetcher();
        $notificationFetcher->fetchNotification();
    }

    /**
     * Fetches marketing campaign.
     *
     * This method is triggered by the scheduled cron event
     * and retrieve marketing campaign.
     *
     * @return void
     */
    public function fetchMarketingCampaign()
    {
        $marketingCampaignFetcher = new MarketingCampaignFetcher();
        $marketingCampaignFetcher->fetchMarketingCampaign();
    }
}