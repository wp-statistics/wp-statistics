<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\ApiHandler;

use WP_Statistics\Components\RemoteRequest;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Admin\LicenseManagement\AddOnDecorator;

/**
 * Factory class for all the license management related API calls.
 */
class LicenseManagerApiFactory
{
    /**
     * Returns `license/status` request's result.
     *
     * @param string $licenseKey
     * @param bool $domain
     *
     * @return LicenseStatusResponseDecorator
     *
     * @throws \Exception
     */
    public static function getStatusApi($licenseKey, $domain = false)
    {
        $url      = WP_STATISTICS_SITE . 'wp-json/wp-license-manager/v1/license/status';
        $request  = new RemoteRequest($url, 'GET', [
            'license_key' => $licenseKey,
            'domain'      => $domain ? $domain : Helper::get_domain_name(home_url()),
        ]);

        $response = $request->execute();

        return new LicenseStatusResponseDecorator($response);
    }

    /**
     * Returns the list of availalbe add-ons on WP-Statistics.com.
     *
     * @return array Format: `['slug' => new AddOnDecorator(), 'slug' => new AddOnDecorator(), ...]`.
     *
     * @throws \Exception
     */
    public static function getAddOnsList()
    {
        $addOns = get_transient('wp_statistics_addons');
        if (!empty($addOns) && is_array($addOns)) {
            return $addOns;
        }

        $request  = new RemoteRequest(WP_STATISTICS_SITE . 'wp-json/plugin/addons/');
        $response = $request->execute();
        if (empty($response) || empty($response->items) || !is_array($response->items)) {
            throw new \Exception(__('Invalid add-ons list response!', 'wp-statistics'));
        }

        $addOns = [];
        foreach ($response->items as $addOn) {
            if (empty($addOn->id) || empty($addOn->slug)) {
                continue;
            }

            $addOns[$addOn->slug] = new AddOnDecorator($addOn);
        }

        set_transient('wp_statistics_addons', $addOns, WEEK_IN_SECONDS);
        return $addOns;
    }
}
