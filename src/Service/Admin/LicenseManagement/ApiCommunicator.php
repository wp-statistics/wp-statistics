<?php

namespace WP_Statistics\Service\Admin\LicenseManagement;

use Exception;
use WP_Statistics\Components\RemoteRequest;
use WP_Statistics\Exception\LicenseException;
use WP_STATISTICS\Helper;
use WP_Statistics\Traits\TransientCacheTrait;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHelper;

class ApiCommunicator
{
    use TransientCacheTrait;

    /**
     * Cache duration for transient request failures.
     */
    const NEGATIVE_CACHE_DURATION = 5 * MINUTE_IN_SECONDS;

    /**
     * Cache duration for authoritative license refusals.
     */
    const AUTHORITATIVE_NEGATIVE_CACHE_DURATION = 12 * HOUR_IN_SECONDS;

    /**
     * Maximum cache duration after repeated authoritative refusals.
     */
    const AUTHORITATIVE_NEGATIVE_CACHE_MAX_DURATION = 2 * DAY_IN_SECONDS;

    /**
     * How long to remember the previous refusal interval for backoff.
     */
    const AUTHORITATIVE_NEGATIVE_CACHE_BACKOFF_STATE_DURATION = WEEK_IN_SECONDS;

    /**
     * Get the list of products (add-ons) from the API and cache it for 1 week.
     *
     * @return array
     * @throws Exception if there is an error with the API call
     */
    public function getProducts()
    {
        try {
            $remoteRequest = new RemoteRequest(ApiEndpoints::PRODUCT_LIST, 'GET');
            $plugins       = $remoteRequest->execute(false, true, WEEK_IN_SECONDS);

            if (empty($plugins) || !is_array($plugins)) {
                throw new Exception(
                    /* translators: %s: string value */
                    sprintf(__('No products were found. The API returned an empty response from the following URL: %s', 'wp-statistics'), ApiEndpoints::PRODUCT_LIST)
                );
            }

        } catch (Exception $e) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- internal exception, message is not rendered to HTML
            throw new Exception(
            // translators: %s: Error message.
                sprintf(__('Unable to retrieve product list from the remote server, %s. Please check the remote server connection or your remote work configuration.', 'wp-statistics'), $e->getMessage())
            );
        }

        return $plugins;
    }

    /**
     * Generate a cache key for product info.
     *
     * The key is site-specific to handle:
     * - Multisite with subdomains (each subsite may have different license)
     * - Multisite with subdirectories
     * - Single site with multilingual plugins (WPML, Polylang) where home_url() varies by language
     *
     * @param string $pluginSlug The plugin slug.
     * @param string $licenseKey The license key.
     *
     * @return string The cache key.
     */
    private function getProductInfoCacheKey($pluginSlug, $licenseKey)
    {
        // Use blog ID for multisite to ensure each subsite has its own cache
        // For single sites, this will always be 1
        $siteIdentifier = get_current_blog_id();

        return 'wp_statistics_product_info_' . md5($pluginSlug . '_' . $licenseKey . '_' . $siteIdentifier);
    }

    /**
     * Generate a network-wide negative cache key for product info.
     *
     * @param string $pluginSlug The plugin slug.
     * @param string $licenseKey The license key.
     *
     * @return string The cache key.
     */
    private function getProductInfoNegativeCacheKey($pluginSlug, $licenseKey)
    {
        return 'wp_statistics_product_info_negative_' . md5($pluginSlug . '_' . $licenseKey);
    }

    /**
     * Generate the network-wide cache key used to track refusal backoff.
     *
     * @param string $pluginSlug The plugin slug.
     * @param string $licenseKey The license key.
     *
     * @return string The cache key.
     */
    private function getProductInfoNegativeCacheBackoffKey($pluginSlug, $licenseKey)
    {
        return $this->getProductInfoNegativeCacheKey($pluginSlug, $licenseKey) . '_backoff';
    }

    /**
     * Clear the network-wide refusal marker and its backoff state.
     *
     * @param string $pluginSlug The plugin slug.
     * @param string $licenseKey The license key.
     *
     * @return void
     */
    private function clearProductInfoNegativeCache($pluginSlug, $licenseKey)
    {
        delete_site_transient($this->getProductInfoNegativeCacheKey($pluginSlug, $licenseKey));
        delete_site_transient($this->getProductInfoNegativeCacheBackoffKey($pluginSlug, $licenseKey));
    }

    /**
     * Clear cached product info for a specific plugin and license.
     *
     * Call this method when license is validated/changed to ensure fresh data.
     *
     * @param string $licenseKey The license key.
     * @param string $pluginSlug The plugin slug (optional, clears all if not provided).
     *
     * @return void
     */
    public function clearProductInfoCache($licenseKey, $pluginSlug = null)
    {
        if ($pluginSlug) {
            delete_transient($this->getProductInfoCacheKey($pluginSlug, $licenseKey));
            $this->clearProductInfoNegativeCache($pluginSlug, $licenseKey);
        } else {
            // Clear cache for all known add-ons when no specific slug provided
            foreach (array_keys(PluginHelper::$plugins) as $addon) {
                delete_transient($this->getProductInfoCacheKey($addon, $licenseKey));
                $this->clearProductInfoNegativeCache($addon, $licenseKey);
            }
        }
    }

    /**
     * Get the download link for the specified plugin using the license key.
     *
     * @param string $licenseKey
     * @param string $pluginSlug
     *
     * @return object|null The product info if found, null otherwise
     * @throws Exception if the API call fails
     */
    public function getDownloadUrl($licenseKey, $pluginSlug)
    {
        $cacheKey         = $this->getProductInfoCacheKey($pluginSlug, $licenseKey);
        $negativeCacheKey = $this->getProductInfoNegativeCacheKey($pluginSlug, $licenseKey);

        // Keep existing successful product data site-specific and usable for its normal lifetime.
        $cached = get_transient($cacheKey);
        if ($cached !== false) {
            return is_object($cached) && isset($cached->_negative_cache) ? null : $cached;
        }

        // The negative cache is shared across subsites because the refusal applies to the license.
        $cached = get_site_transient($negativeCacheKey);
        if ($cached !== false && is_object($cached) && isset($cached->_negative_cache)) {
            return null;
        }

        try {
            $remoteRequest = new RemoteRequest(ApiEndpoints::PRODUCT_DOWNLOAD, 'GET', [
                'license_key' => $licenseKey,
                'domain'      => home_url(),
                'plugin_slug' => $pluginSlug,
            ]);

            // Use custom cache key for proper multisite/multilingual support.
            $productInfo = $remoteRequest->execute(true, true, DAY_IN_SECONDS, $cacheKey);

            // A successful response proves the previous refusal state is stale.
            $this->clearProductInfoNegativeCache($pluginSlug, $licenseKey);

            return $productInfo;

        } catch (Exception $e) {
            $responseCode = $remoteRequest->getResponseCode();
            $duration     = $this->isAuthoritativeRefusal($responseCode)
                ? $this->getAuthoritativeNegativeCacheDuration($pluginSlug, $licenseKey)
                : self::NEGATIVE_CACHE_DURATION;

            set_site_transient($negativeCacheKey, (object)['_negative_cache' => true], $duration);
            throw $e;
        }
    }

    /**
     * Determine whether the API returned a stable client-side refusal.
     *
     * Request timeouts and rate limits can recover quickly, so they retain the short cache duration.
     *
     * @param int|null $responseCode HTTP response code.
     *
     * @return bool
     */
    private function isAuthoritativeRefusal($responseCode)
    {
        return $responseCode >= 400
            && $responseCode < 500
            && !in_array($responseCode, [408, 429], true);
    }

    /**
     * Get and persist the next bounded backoff duration for a refusal.
     *
     * @param string $pluginSlug The plugin slug.
     * @param string $licenseKey The license key.
     *
     * @return int Cache duration in seconds.
     */
    private function getAuthoritativeNegativeCacheDuration($pluginSlug, $licenseKey)
    {
        $backoffKey       = $this->getProductInfoNegativeCacheBackoffKey($pluginSlug, $licenseKey);
        $previousDuration = (int) get_site_transient($backoffKey);

        if ($previousDuration < self::AUTHORITATIVE_NEGATIVE_CACHE_DURATION
            || $previousDuration > self::AUTHORITATIVE_NEGATIVE_CACHE_MAX_DURATION
        ) {
            $duration = self::AUTHORITATIVE_NEGATIVE_CACHE_DURATION;
        } else {
            $duration = min($previousDuration * 2, self::AUTHORITATIVE_NEGATIVE_CACHE_MAX_DURATION);
        }

        set_site_transient($backoffKey, $duration, self::AUTHORITATIVE_NEGATIVE_CACHE_BACKOFF_STATE_DURATION);

        return $duration;
    }

    /**
     * Get the download URL for a specific plugin slug from the license status.
     *
     * @param string $licenseKey
     * @param string $pluginSlug
     *
     * @return string|null The download URL if found, null otherwise
     * @throws Exception
     */
    public function getDownloadUrlFromLicense($licenseKey, $pluginSlug)
    {
        // Validate the license and get the licensed products
        $licenseStatus = $this->validateLicense($licenseKey, $pluginSlug);

        // Search for the download URL in the licensed products
        foreach ($licenseStatus->products as $product) {
            if ($product->slug === $pluginSlug) {
                return $product->download_url ?? null;
            }
        }

        return null;
    }

    /**
     * Validate the license and get the status of licensed products.
     *
     * @param string $licenseKey
     * @param string $product Optional param to check whether the license is valid for a particular product, or not
     *
     * @return object License status
     * @throws Exception if the API call fails
     */
    public function validateLicense($licenseKey, $product = false)
    {
        if (empty($licenseKey) || !Helper::isStringLengthBetween($licenseKey, 32, 40) || !preg_match('/^[a-zA-Z0-9-]+$/', $licenseKey)) {
            throw new LicenseException(
                esc_html__('License key is not valid. Please enter a valid license and try again.', 'wp-statistics'),
                'invalid_license'
            );
        }

        $remoteRequest = new RemoteRequest(ApiEndpoints::LICENSE_STATUS, 'GET', [
            'license_key' => $licenseKey,
            'domain'      => home_url(),
        ]);

        $licenseData = $remoteRequest->execute(false, false);

        if (empty($licenseData)) {
            throw new LicenseException(esc_html__('Invalid license response!', 'wp-statistics'));
        }

        if (empty($licenseData->license_details)) {
            // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- internal exception, message is not rendered to HTML
            throw new LicenseException(
                $licenseData->message ?? esc_html__('Unknown error!', 'wp-statistics'),
                $licenseData->status ?? '',
                intval($licenseData->code)
            );
        }

        if (!empty($product)) {
            $productSlugs = array_column($licenseData->products, 'slug');

            if (!in_array($product, $productSlugs, true)) {
                /* translators: %s: string value */
                throw new LicenseException(sprintf(esc_html__('The license is not related to the requested Add-on <b>%s</b>.', 'wp-statistics'), $product)); // phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- internal exception, message is not rendered to HTML
            }
        }

        LicenseHelper::storeLicense($licenseKey, $licenseData);

        // Clear product info cache on successful license validation
        // This ensures fresh download URLs after license changes (renewal, domain addition, etc.)
        $this->clearProductInfoCache($licenseKey);

        return $licenseData;
    }
}
