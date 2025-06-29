<?php

namespace WP_Statistics\Service\Admin\MarketingCampaign;

class MarketingCampaignFactory
{
    /**
     * Retrieves all marketing campaigns after processing and filtering.
     *
     * @return array Processed and decorated notifications.
     */
    public static function getAllMarketingCampaigns()
    {
        $rawMarketingCampaigns = self::getRawMarketingCampaignsData();
        $marketingCampaigns    = MarketingCampaignProcessor::filterMarketingCampaignsByTags($rawMarketingCampaigns['data'] ?? []);

        return MarketingCampaignProcessor::decorateMarketingCampaigns($marketingCampaigns);
    }

    /**
     * Retrieves the raw notification data from WordPress options.
     *
     * @return array The raw notification data stored in the database.
     */
    public static function getRawMarketingCampaignsData()
    {
        return get_option('wp_statistics_marketing_campaigns', []);
    }

    /**
     * Retrieves the marketing campaign data from WordPress options.
     *
     * @param string $type
     *
     * @return object|null
     */
    public static function getLatestMarketingCampaignByType($type)
    {
        $marketingCampaigns = self::getAllMarketingCampaigns();

        foreach ($marketingCampaigns as $marketingCampaign) {
            if ($marketingCampaign->getType() === $type) {
                return $marketingCampaign;
            }
        }

        return null;
    }
}