<?php

namespace WP_Statistics\Service\Admin\LicenseManagement\ApiHandler;

use WP_Statistics\Components\RemoteRequest;
use WP_STATISTICS\Helper;

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
}
