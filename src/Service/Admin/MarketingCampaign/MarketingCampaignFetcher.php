<?php

namespace WP_Statistics\Service\Admin\MarketingCampaign;

use Exception;
use WP_Statistics\Components\RemoteRequest;

class MarketingCampaignFetcher
{
    /**
     * API base URL for fetching marketing campaign.
     *
     * @var string
     */
    private $apiUrl = 'http://127.0.0.1:8000';

    /**
     * Fetch marketing campaign from the remote API and store it in the WordPress database.
     *
     * @param string $type The type of marketing campaign to fetch. Accepts 'promo_banner', 'modal', or 'sidebar_callout'.
     *
     * @return bool True on success, false on failure.
     * @throws Exception If the API response is invalid or an error occurs.
     */
    public function fetchMarketingCampaign($type = 'promo_banner')
    {
        try {
            $pluginSlug = basename(dirname(WP_STATISTICS_MAIN_FILE));
            $url        = $this->apiUrl . '/api/v1/marketing-campaign';
            $method     = 'GET';
            $params     = ['plugin_slug' => $pluginSlug, 'type' => $type, 'per_page' => 20, 'sortby' => 'activated_at-desc'];
            $args       = [
                'timeout'     => 45,
                'redirection' => 5,
                'headers'     => array(
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8',
                    'user-agent'   => $pluginSlug,
                ),
                'cookies'     => array(),
            ];

            $remoteRequest = new RemoteRequest($url, $method, $params, $args);

            $remoteRequest->execute(false, false);

            $response     = $remoteRequest->getResponseBody();
            $responseCode = $remoteRequest->getResponseCode();

            if ($responseCode === 404) {
                update_option('wp_statistics_marketing_campaign', []);
                return false;
            }

            if ($responseCode !== 200) {
                return false;
            }

            $marketingCampaign = json_decode($response, true);

            if (empty($marketingCampaign) || !is_array($marketingCampaign)) {
                throw new Exception(
                    sprintf(__('No marketing campaign were found. The API returned an empty response from the following URL: %s', 'wp-statistics'), "{$this->apiUrl}/api/v1/marketing-campaign?plugin_slug={$pluginSlug}")
                );
            }

            $prevRawMarketingCampaignData = MarketingCampaignFactory::getRawMarketingCampaignData();

            if (!update_option('wp_statistics_marketing_campaign', $marketingCampaign)) {
                if ($prevRawMarketingCampaignData !== $marketingCampaign) {
                    WP_Statistics()->log('Failed to update wp_statistics_marketing_campaign option.', 'error');
                }
            }

            return true;
        } catch (Exception $e) {
            WP_Statistics()->log($e->getMessage(), 'error');
            return false;
        }
    }
}