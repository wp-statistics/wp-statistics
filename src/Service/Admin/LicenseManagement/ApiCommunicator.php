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
     * How long a failure that decided nothing is remembered (5 minutes).
     *
     * A timeout, a DNS failure or a 5xx says nothing about the licence — the answer may
     * be different in a moment — so this stays short. It is the starting point of the
     * backoff in {@see self::undecidedRetryDelay()}, not a fixed interval.
     */
    const NEGATIVE_CACHE_DURATION = 5 * MINUTE_IN_SECONDS;

    /**
     * How long the first refusal from the licence server is remembered (12 hours).
     *
     * An expired or suspended licence, or a domain that is not on it, is a decision the
     * server has already made. Asking again in five minutes cannot change it, and 288
     * asks a day per add-on per subsite is what issue #1123 measured. Twelve hours is a
     * working day either side, and {@see self::clearProductInfoCache()} clears this the
     * moment a licence is validated, so a customer who renews waits no time at all.
     */
    const AUTHORITATIVE_NEGATIVE_CACHE_DURATION = 12 * HOUR_IN_SECONDS;

    /**
     * The longest a repeated refusal is remembered (48 hours).
     */
    const AUTHORITATIVE_NEGATIVE_CACHE_MAX_DURATION = 2 * DAY_IN_SECONDS;

    /**
     * The longest an undecided failure is remembered once its backoff has stretched.
     */
    const MAX_UNDECIDED_CACHE_DURATION = 6 * HOUR_IN_SECONDS;

    /**
     * The 4xx codes that are not an answer about the licence.
     *
     * 429 is the server asking us to slow down. A 404 is a route that has moved — rename
     * the endpoint during a deploy and every install would otherwise cache "refused" for
     * twelve hours and stay dead long after the rollback. A 403 is what an edge rule or a
     * WAF returns, with no licence involved at all. 408 is a timeout wearing a 4xx.
     *
     * Each of these still heals quickly, and each is now covered by the backoff below, so
     * a fleet that keeps hitting one is no longer a fixed-rate flood.
     */
    const UNDECIDED_CLIENT_CODES = [403, 404, 408, 429];

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
     * Generate the cache key for a refusal.
     *
     * Keyed on the blog, exactly like the success cache above — deliberately NOT on
     * `home_url()` and deliberately not shared network-wide.
     *
     * Not the address: on a multilingual subdirectory site `home_url()` returns `/en`,
     * `/fr`, `/de`, so one install would multiply into one entry and one request per
     * language, in a change whose entire purpose is to reduce request volume.
     *
     * Not network-wide either: the server judges the `domain` we send, so one subsite of
     * a network can be entitled while another is refused. A single shared refusal would
     * let a refused subsite deny updates to an entitled one for up to 48 hours. Keyed on
     * the blog, a refusal and the success it replaces live under the same unit, so the
     * two can never disagree about whether this install is entitled.
     *
     * @param string $pluginSlug The plugin slug.
     * @param string $licenseKey The license key.
     *
     * @return string The cache key.
     */
    private function getRefusalCacheKey($pluginSlug, $licenseKey)
    {
        return 'wp_statistics_license_refusal_' . md5($pluginSlug . '_' . $licenseKey . '_' . get_current_blog_id());
    }

    /**
     * The entry that counts consecutive failures which decided nothing.
     *
     * Kept apart from the refusal, and outliving it. Holding the counter inside the
     * refusal cannot work: the refusal expiring is the only thing that lets another
     * attempt happen, so by the time it is read back it is always gone and the count is
     * always one — which would leave the backoff at a fixed five minutes forever.
     *
     * @param string $pluginSlug The plugin slug.
     * @param string $licenseKey The license key.
     *
     * @return string The cache key.
     */
    private function getRefusalAttemptsKey($pluginSlug, $licenseKey)
    {
        return $this->getRefusalCacheKey($pluginSlug, $licenseKey) . '_attempts';
    }

    /**
     * The entry that remembers how long the last refusal was held for.
     *
     * @param string $pluginSlug The plugin slug.
     * @param string $licenseKey The license key.
     *
     * @return string The cache key.
     */
    private function getRefusalBackoffKey($pluginSlug, $licenseKey)
    {
        return $this->getRefusalCacheKey($pluginSlug, $licenseKey) . '_backoff';
    }

    /**
     * The entry that holds the licence's current refusal generation.
     *
     * One licence collects one refusal per subsite. Clearing only the subsite the
     * customer happened to renew on would leave the other thirty-nine refused for twelve
     * hours — worse than the five minutes they used to wait. Rather than keep a list of
     * every refusal key (a read-modify-write that concurrent refusals could race, losing
     * a key and leaving that subsite refused after renewal), this holds one opaque token
     * that validation replaces. Every refusal, attempt count and backoff carries the
     * token that was current when it was written; one that carries any other token is
     * treated as absent. A token rather than a timestamp, so that two web nodes with
     * different clocks cannot disagree about which came first.
     *
     * @param string $licenseKey The license key.
     *
     * @return string The cache key.
     */
    private function getRefusalGenerationKey($licenseKey)
    {
        return 'wp_statistics_license_refusal_generation_' . md5($licenseKey);
    }

    /**
     * Clear the refusal for this add-on and blog, with its backoff state.
     *
     * @param string $pluginSlug The plugin slug.
     * @param string $licenseKey The license key.
     *
     * @return void
     */
    private function clearProductInfoNegativeCache($pluginSlug, $licenseKey)
    {
        $this->deleteEntry($this->getRefusalCacheKey($pluginSlug, $licenseKey));
        $this->deleteEntry($this->getRefusalAttemptsKey($pluginSlug, $licenseKey));
        $this->deleteEntry($this->getRefusalBackoffKey($pluginSlug, $licenseKey));
    }

    /**
     * Read a remembered refusal.
     *
     * @param string $pluginSlug The plugin slug.
     * @param string $licenseKey The license key.
     *
     * @return array|false The stored refusal, or false when there is none.
     */
    private function getRefusal($pluginSlug, $licenseKey)
    {
        $refusal = $this->readLiveEntry($this->getRefusalCacheKey($pluginSlug, $licenseKey), $licenseKey);

        return $refusal !== false && isset($refusal['code']) ? $refusal : false;
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

        // And every other blog this licence was refused on. A network renews on one
        // subsite; the rest must not stay refused for the remaining twelve hours —
        // which would be worse than the five minutes they used to wait.
        $this->markRefusalsCleared($licenseKey);
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
        $cacheKey = $this->getProductInfoCacheKey($pluginSlug, $licenseKey);

        // A remembered refusal answers for the whole of its life. This blog asked, the
        // server said no, and asking again before it expires only adds traffic.
        if ($this->getRefusal($pluginSlug, $licenseKey) !== false) {
            return null;
        }

        // The release before this one wrote its marker into the *success* key, which
        // RemoteRequest reads and hands straight back. Without this, an upgraded site is
        // served {_negative_cache: true} as though it were product info — no
        // download_url, no version. Cleared and then ignored, rather than answered with
        // null: the marker means the old code failed once, up to five minutes ago.
        $this->discardLegacyNegativeEntry($cacheKey);

        $remoteRequest = new RemoteRequest(ApiEndpoints::PRODUCT_DOWNLOAD, 'GET', [
            'license_key' => $licenseKey,
            'domain'      => home_url(),
            'plugin_slug' => $pluginSlug,
        ]);

        try {
            // Use custom cache key for proper multisite/multilingual support.
            $productInfo = $remoteRequest->execute(true, true, DAY_IN_SECONDS, $cacheKey);

            // A licence that answers again clears whatever we were remembering about it,
            // so a customer who renews is never held back by a stale refusal.
            $this->clearProductInfoNegativeCache($pluginSlug, $licenseKey);

            return $productInfo;

        } catch (Exception $e) {
            $this->rememberRefusal($pluginSlug, $licenseKey, $remoteRequest->getResponseCode());

            throw $e;
        }
    }

    /**
     * Remember that this add-on, licence and blog was turned away.
     *
     * The response code is the whole decision. RemoteRequest throws the same plain
     * Exception whether WordPress could not reach the host at all or the server answered
     * "this licence expired", and treating those alike is what made a refused install ask
     * every five minutes forever.
     *
     * - **A 4xx** is the server's considered answer about the licence, held for twelve
     *   hours and longer if it repeats — except {@see self::UNDECIDED_CLIENT_CODES}.
     * - **No code at all** means `wp_remote_request` returned a `WP_Error`: a timeout, a
     *   DNS failure, a refused connection. Nothing has been decided.
     * - **A 5xx** means the server is unwell. Also nothing decided.
     *
     * @param string   $pluginSlug   The plugin slug.
     * @param string   $licenseKey   The license key.
     * @param int|null $responseCode HTTP response code, if one arrived.
     *
     * @return void
     */
    private function rememberRefusal($pluginSlug, $licenseKey, $responseCode)
    {
        $code = is_numeric($responseCode) ? (int) $responseCode : 0;

        if ($this->isAuthoritativeRefusal($code)) {
            // An answer, even an unwelcome one, proves the server is reachable — so the
            // outage history goes with it. Without this a site that timed out three times
            // and then got a clean 400 would wait 40 minutes for its next blip.
            $this->deleteEntry($this->getRefusalAttemptsKey($pluginSlug, $licenseKey));
            $this->storeRefusal($pluginSlug, $licenseKey, $this->authoritativeRefusalDuration($pluginSlug, $licenseKey), $code);

            return;
        }

        // Transport failure, 5xx, 429 and the rest: try again, but not as often each time.
        $attempts = $this->recordAttempt($pluginSlug, $licenseKey);

        $this->storeRefusal($pluginSlug, $licenseKey, $this->undecidedRetryDelay($attempts), $code);
    }

    /**
     * Determine whether the API returned a stable answer about the licence.
     *
     * @param int $responseCode HTTP response code.
     *
     * @return bool
     */
    private function isAuthoritativeRefusal($responseCode)
    {
        return $responseCode >= 400
            && $responseCode < 500
            && !in_array($responseCode, self::UNDECIDED_CLIENT_CODES, true);
    }

    /**
     * How long to hold this refusal, lengthening while it keeps repeating.
     *
     * Twelve hours, then a day, then two, which is the cap. Kept in an entry of its own
     * so it outlives the refusal whose expiry is the only thing that allows another ask.
     *
     * @param string $pluginSlug The plugin slug.
     * @param string $licenseKey The license key.
     *
     * @return int Seconds.
     */
    private function authoritativeRefusalDuration($pluginSlug, $licenseKey)
    {
        $backoffKey       = $this->getRefusalBackoffKey($pluginSlug, $licenseKey);
        $previous         = $this->readLiveEntry($backoffKey, $licenseKey);
        $previousDuration = $previous !== false && isset($previous['duration']) ? (int) $previous['duration'] : 0;

        if ($previousDuration < self::AUTHORITATIVE_NEGATIVE_CACHE_DURATION
            || $previousDuration > self::AUTHORITATIVE_NEGATIVE_CACHE_MAX_DURATION
        ) {
            $duration = self::AUTHORITATIVE_NEGATIVE_CACHE_DURATION;
        } else {
            $duration = min($previousDuration * 2, self::AUTHORITATIVE_NEGATIVE_CACHE_MAX_DURATION);
        }

        $this->writeStampedEntry($backoffKey, $licenseKey, ['duration' => $duration], self::AUTHORITATIVE_NEGATIVE_CACHE_MAX_DURATION + DAY_IN_SECONDS);

        return $duration;
    }

    /**
     * Count this failure, and return how many there have now been in a row.
     *
     * Given the longest wait plus an hour, so a site that recovers stops carrying its
     * history around and one that does not keeps climbing.
     *
     * @param string $pluginSlug The plugin slug.
     * @param string $licenseKey The license key.
     *
     * @return int
     */
    private function recordAttempt($pluginSlug, $licenseKey)
    {
        $key      = $this->getRefusalAttemptsKey($pluginSlug, $licenseKey);
        $stored   = $this->readLiveEntry($key, $licenseKey);
        $attempts = ($stored !== false && isset($stored['attempts']) ? (int) $stored['attempts'] : 0) + 1;

        $this->writeStampedEntry($key, $licenseKey, ['attempts' => $attempts], self::MAX_UNDECIDED_CACHE_DURATION + HOUR_IN_SECONDS);

        return $attempts;
    }

    /**
     * How long to wait after a failure that decided nothing.
     *
     * Doubles per consecutive attempt from five minutes, capped at six hours. A site that
     * cannot reach us keeps trying, but a whole fleet that cannot reach us does not turn
     * into a fixed-rate flood the moment the server comes back.
     *
     * @param int $attempts
     *
     * @return int Seconds.
     */
    private function undecidedRetryDelay($attempts)
    {
        // 2 ** 10 is already far past the cap; clamping keeps the shift cheap and safe.
        $exponent = max(0, min((int) $attempts - 1, 10));
        $delay    = self::NEGATIVE_CACHE_DURATION * (2 ** $exponent);

        return (int) min($delay, self::MAX_UNDECIDED_CACHE_DURATION);
    }

    /**
     * Write the refusal.
     *
     * @param string $pluginSlug The plugin slug.
     * @param string $licenseKey The license key.
     * @param int    $duration   Seconds to hold it for.
     * @param int    $code       The response code that produced it.
     *
     * @return void
     */
    private function storeRefusal($pluginSlug, $licenseKey, $duration, $code)
    {
        $this->writeStampedEntry($this->getRefusalCacheKey($pluginSlug, $licenseKey), $licenseKey, ['code' => (int) $code], $duration);
    }

    /**
     * Void every refusal recorded for a licence, whichever blog recorded it.
     *
     * Nothing is deleted here. The rows stay until their own TTL runs out, or until
     * {@see self::readLiveEntry()} meets one and finds it stale. A single scalar write
     * cannot lose a concurrent refusal the way a shared list can.
     *
     * Held a day past the longest refusal so that it outlives everything it voids.
     *
     * @param string $licenseKey The license key.
     *
     * @return void
     */
    private function markRefusalsCleared($licenseKey)
    {
        $this->writeEntry($this->getRefusalGenerationKey($licenseKey), wp_generate_uuid4(), self::AUTHORITATIVE_NEGATIVE_CACHE_MAX_DURATION + DAY_IN_SECONDS);
    }

    /**
     * The token a refusal written now must carry to be honoured.
     *
     * @param string $licenseKey The license key.
     *
     * @return string Empty when the licence has never been validated on this install.
     */
    private function currentGeneration($licenseKey)
    {
        $generation = $this->readEntry($this->getRefusalGenerationKey($licenseKey));

        return is_string($generation) ? $generation : '';
    }

    /**
     * Read an entry, unless the licence has been validated since it was written.
     *
     * A stale entry is deleted on sight. Otherwise, should the generation token be
     * evicted from a persistent object cache before the entry expires, the entry would
     * come back to life and the renewed licence be refused again.
     *
     * @param string $key        The cache key.
     * @param string $licenseKey The license key.
     *
     * @return array|false The stored array, or false when absent, malformed or voided.
     */
    private function readLiveEntry($key, $licenseKey)
    {
        $stored = $this->readEntry($key);

        // Anything that is not our own shape is treated as absent rather than trusted:
        // the previous release stored an object, and an upgrade must not trip on it.
        if (!is_array($stored) || !array_key_exists('generation', $stored)) {
            return false;
        }

        if ($stored['generation'] !== $this->currentGeneration($licenseKey)) {
            $this->deleteEntry($key);

            return false;
        }

        return $stored;
    }

    /**
     * Write an entry carrying the current generation, for {@see self::readLiveEntry()}.
     *
     * @param string $key        The cache key.
     * @param string $licenseKey The license key.
     * @param array  $value      The entry.
     * @param int    $duration   Seconds to hold it for.
     *
     * @return void
     */
    private function writeStampedEntry($key, $licenseKey, array $value, $duration)
    {
        $value['generation'] = $this->currentGeneration($licenseKey);

        $this->writeEntry($key, $value, $duration);
    }

    /**
     * Clear the previous release's marker out of the success cache.
     *
     * @param string $cacheKey The product info cache key.
     *
     * @return void
     */
    private function discardLegacyNegativeEntry($cacheKey)
    {
        $cached = get_transient($cacheKey);

        if (is_object($cached) && isset($cached->_negative_cache)) {
            delete_transient($cacheKey);
        }
    }

    /**
     * Read one entry, network-wide on multisite.
     *
     * Site transients on multisite, so the row lives in one place rather than in every
     * subsite's own options table and a renewal on any subsite can reach the rest. The
     * key carries the blog ID, so each subsite still keeps its own verdict.
     *
     * @param string $key
     *
     * @return mixed
     */
    private function readEntry($key)
    {
        return is_multisite() ? get_site_transient($key) : get_transient($key);
    }

    /**
     * @param string $key
     * @param mixed  $value
     * @param int    $duration
     *
     * @return void
     */
    private function writeEntry($key, $value, $duration)
    {
        if (is_multisite()) {
            set_site_transient($key, $value, $duration);

            return;
        }

        set_transient($key, $value, $duration);
    }

    /**
     * @param string $key
     *
     * @return void
     */
    private function deleteEntry($key)
    {
        if (is_multisite()) {
            delete_site_transient($key);

            return;
        }

        delete_transient($key);
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
