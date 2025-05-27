<?php

namespace WP_Statistics\Service\Admin\MarketingCampaign;

use Exception;
use WP_Statistics\Components\RemoteRequest;

class MarketingCampaignFetcher {
	/**
	 * API base URL for fetching marketing campaigns.
	 *
	 * @var string
	 */
	private $apiUrl = 'https://connect.wp-statistics.com';

	/**
	 * Fetch marketing campaigns from the remote API and store it in the WordPress database.
	 *
	 * @return bool True on success, false on failure.
	 * @throws Exception If the API response is invalid or an error occurs.
	 */
	public function fetchMarketingCampaign() {
		try {
			$pluginSlug = basename( dirname( WP_STATISTICS_MAIN_FILE ) );
			$url        = $this->apiUrl . '/api/v1/marketing-campaigns';
			$method     = 'GET';
			$params     = [
				'plugin_slug' => $pluginSlug,
				'per_page'    => 100,
				'sortby'      => 'activated_at-desc'
			];
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

			$remoteRequest = new RemoteRequest( $url, $method, $params, $args );

			$remoteRequest->execute( false, false );

			$response     = $remoteRequest->getResponseBody();
			$responseCode = $remoteRequest->getResponseCode();

			if ( $responseCode === 404 ) {
				update_option( 'wp_statistics_marketing_campaigns', [] );

				return false;
			}

			if ( $responseCode !== 200 ) {
				return false;
			}

			$marketingCampaigns = json_decode( $response, true );

			if ( empty( $marketingCampaigns ) || ! is_array( $marketingCampaigns ) ) {
				throw new Exception(
					sprintf( __( 'No marketing campaign were found. The API returned an empty response from the following URL: %s', 'wp-statistics' ), "{$this->apiUrl}/api/v1/marketing-campaigns?plugin_slug={$pluginSlug}" )
				);
			}

			$marketingCampaigns            = MarketingCampaignProcessor::sortMarketingCampaignsByActivatedAt( $marketingCampaigns );
			$prevRawMarketingCampaignsData = MarketingCampaignFactory::getRawMarketingCampaignsData();

			if ( ! update_option( 'wp_statistics_marketing_campaigns', $marketingCampaigns ) ) {
				if ( $prevRawMarketingCampaignsData !== $marketingCampaigns ) {
					WP_Statistics()->log( 'Failed to update wp_statistics_marketing_campaigns option.', 'error' );
				}
			}

			return true;
		} catch ( Exception $e ) {
			WP_Statistics()->log( $e->getMessage(), 'error' );

			return false;
		}
	}
}