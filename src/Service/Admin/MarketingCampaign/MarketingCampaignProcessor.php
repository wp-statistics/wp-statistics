<?php

namespace WP_Statistics\Service\Admin\MarketingCampaign;

use WP_Statistics\Decorators\MarketingCampaignDecorator;
use WP_Statistics\Service\Admin\ConditionTagEvaluator;

class MarketingCampaignProcessor
{
    /**
     * Filters marketing campaigns based on their associated tags.
     *
     * @param array $marketingCampaigns List of marketing campaigns to be filtered.
     * @return array Filtered marketing campaigns.
     */
    public static function filterMarketingCampaignsByTags($marketingCampaigns)
    {
        if (!empty($marketingCampaigns) && is_array($marketingCampaigns)) {
            foreach ($marketingCampaigns as $key => $marketingCampaign) {
                if (!empty($marketingCampaign['tags']) && is_array($marketingCampaign['tags'])) {
                    $condition = true;
                    foreach ($marketingCampaign['tags'] as $tag) {
                        if (!ConditionTagEvaluator::checkConditions($tag)) {
                            $condition = false;
                            break;
                        }
                    }
                } else {
                    $condition = true;
                }

                if (!$condition) {
                    unset($marketingCampaigns[$key]);
                }
            }

            $marketingCampaigns = array_values($marketingCampaigns);
        }

        return $marketingCampaigns;
    }

    /**
     * Sorts the marketing campaigns array by the 'activated_at' field in descending order.
     *
     * @param array $marketingCampaigns
     *
     * @return array
     */
    public static function sortMarketingCampaignsByActivatedAt($marketingCampaigns)
    {
        if (!empty($marketingCampaigns['data']) && is_array($marketingCampaigns['data'])) {
            usort($marketingCampaigns['data'], function ($a, $b) {
                return strtotime($b['activated_at']) - strtotime($a['activated_at']);
            });
        }

        return $marketingCampaigns;
    }

    /**
     * Decorates notifications using the NotificationDecorator.
     *
     * @param array $notifications List of notifications to be decorated.
     * @return array Decorated notifications.
     */
    public static function decorateMarketingCampaigns($marketingCampaigns)
    {
        if (empty($marketingCampaigns) || !is_array($marketingCampaigns)) {
            return [];
        }

        return array_map(function ($marketingCampaigns) {
            return new MarketingCampaignDecorator((object)$marketingCampaigns);
        }, $marketingCampaigns);
    }
}