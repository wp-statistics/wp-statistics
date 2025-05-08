<?php

namespace WP_Statistics\Service\Admin\MarketingCampaign;

use WP_Statistics\Components\Event;
use WP_STATISTICS\Option;

class MarketingCampaignManager
{
    /**
     * MarketingCampaignManager constructor.
     *
     * Schedules the marketing campaign fetch event.
     */
    public function __construct()
    {
        if (Option::get('display_notifications')) {
            Event::schedule('wp_statistics_marketing_campaign_hook', time(), 'daily', [$this, 'fetchMarketingCampaign']);
        } else {
            Event::unschedule('wp_statistics_marketing_campaign_hook');
        }
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