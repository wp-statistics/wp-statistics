<?php

namespace WP_Statistics\Service\Cron\Events;

use WP_Statistics\Globals\Option;
use WP_Statistics\Service\Admin\MarketingCampaign\MarketingCampaignFetcher;
use WP_Statistics\Service\Admin\Notification\NotificationFetcher;

/**
 * Notification Cron Event.
 *
 * Fetches notifications and marketing campaigns.
 *
 * @since 15.0.0
 */
class NotificationEvent extends AbstractCronEvent
{
    /**
     * @var string
     */
    protected $hook = 'wp_statistics_daily_cron_hook';

    /**
     * @var string
     */
    protected $recurrence = 'daily';

    /**
     * Notification event should always be scheduled.
     *
     * @return bool
     */
    protected function shouldSchedule()
    {
        return true;
    }

    /**
     * Execute the notification fetching.
     *
     * @return void
     */
    public function execute()
    {
        if (!Option::getValue('display_notifications')) {
            return;
        }

        $this->fetchNotifications();
        $this->fetchMarketingCampaigns();
    }

    /**
     * Fetch new notifications.
     *
     * @return void
     */
    private function fetchNotifications()
    {
        $notificationFetcher = new NotificationFetcher();
        $notificationFetcher->fetchNotification();
    }

    /**
     * Fetch marketing campaigns.
     *
     * @return void
     */
    private function fetchMarketingCampaigns()
    {
        $marketingCampaignFetcher = new MarketingCampaignFetcher();
        $marketingCampaignFetcher->fetchMarketingCampaign();
    }
}
