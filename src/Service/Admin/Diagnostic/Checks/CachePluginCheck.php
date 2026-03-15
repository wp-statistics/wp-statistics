<?php

namespace WP_Statistics\Service\Admin\Diagnostic\Checks;

use WP_Statistics\Service\Admin\Diagnostic\DiagnosticResult;
use WP_Statistics\Components\Option;
use WP_STATISTICS\Helper;

/**
 * Cache Plugin Check.
 *
 * Detects active cache plugins and verifies tracking configuration.
 *
 * @since 15.0.0
 */
class CachePluginCheck extends AbstractCheck
{
    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return 'cache';
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel(): string
    {
        return __('Cache Plugin', 'wp-statistics');
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return __('Detects cache plugins and verifies tracking compatibility.', 'wp-statistics');
    }

    /**
     * {@inheritDoc}
     */
    public function getHelpUrl(): ?string
    {
        return 'https://wp-statistics.com/resources/cache-compatibility/';
    }

    /**
     * {@inheritDoc}
     */
    public function isLightweight(): bool
    {
        return true; // Function calls are lightweight
    }

    /**
     * {@inheritDoc}
     */
    public function run(): DiagnosticResult
    {
        $details = [];

        // Check for active cache plugins
        $cacheInfo = Helper::checkActiveCachePlugin();

        if (empty($cacheInfo['status'])) {
            return $this->pass(
                __('No cache plugin detected.', 'wp-statistics'),
                ['cache_plugin' => null]
            );
        }

        $details['cache_plugin'] = $cacheInfo['plugin'];
        $details['debug']        = $cacheInfo['debug'] ?? '';

        // Check if bypass ad blockers is enabled
        $bypassAdBlockers = Option::getValue('bypass_ad_blockers');
        $details['bypass_ad_blockers'] = (bool) $bypassAdBlockers;

        return $this->pass(
            sprintf(
                __('%s detected, tracking configured correctly.', 'wp-statistics'),
                $cacheInfo['plugin']
            ),
            $details
        );
    }
}
