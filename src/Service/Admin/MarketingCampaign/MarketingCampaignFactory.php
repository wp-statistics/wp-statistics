<?php

namespace WP_Statistics\Service\Admin\MarketingCampaign;

use WP_Statistics\Decorators\MarketingCampaignDecorator;

class MarketingCampaignFactory
{

    /**
     * Retrieves the raw marketing campaign data from WordPress options.
     *
     * @return array
     */
    public static function getRawMarketingCampaignData()
    {
        return get_option('wp_statistics_marketing_campaign', []);
    }

    /**
     * Retrieves the marketing campaign data from WordPress options.
     *
     * @return object
     */
    public static function getMarketingCampaignData()
    {
        $rawMarketingCampaign = get_option('wp_statistics_marketing_campaign', []);
        $marketingCampaign    = $rawMarketingCampaign['data'] ?? [];

        if (empty($marketingCampaign) || !is_array($marketingCampaign)) {
            return null;
        }

        return new MarketingCampaignDecorator((object)$marketingCampaign);
    }
}