<?php

namespace WP_Statistics\Service\Admin\Diagnostic\Checks;

use WP_Statistics\Service\Admin\Diagnostic\DiagnosticResult;

/**
 * Server Environment Check.
 *
 * Verifies PHP settings and extensions required for WP Statistics.
 *
 * @since 15.0.0
 */
class ServerEnvironmentCheck extends AbstractCheck
{
    /**
     * Minimum recommended memory limit in MB.
     */
    private const MIN_MEMORY_MB = 64;

    /**
     * Minimum recommended max execution time in seconds.
     */
    private const MIN_EXECUTION_TIME = 30;

    /**
     * Required PHP extensions.
     */
    private const REQUIRED_EXTENSIONS = ['curl', 'json'];

    /**
     * Recommended PHP extensions.
     */
    private const RECOMMENDED_EXTENSIONS = ['mbstring', 'gmp', 'bcmath'];

    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return 'server';
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel(): string
    {
        return __('Server Environment', 'wp-statistics');
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return __('Checks PHP settings and extensions.', 'wp-statistics');
    }

    /**
     * {@inheritDoc}
     */
    public function getHelpUrl(): ?string
    {
        return 'https://developer.wordpress.org/advanced-administration/server/configuration/';
    }

    /**
     * {@inheritDoc}
     */
    public function isLightweight(): bool
    {
        return true; // ini_get calls are fast
    }

    /**
     * {@inheritDoc}
     */
    public function run(): DiagnosticResult
    {
        $issues   = [];
        $warnings = [];
        $details  = [];

        // Check memory limit
        $memoryLimit   = $this->getMemoryLimitMB();
        $details['memory_limit'] = $memoryLimit . 'MB';

        if ($memoryLimit > 0 && $memoryLimit < self::MIN_MEMORY_MB) {
            $issues[] = sprintf(
                __('Memory limit (%dMB) is below recommended (%dMB).', 'wp-statistics'),
                $memoryLimit,
                self::MIN_MEMORY_MB
            );
        }

        // Check max execution time
        $maxTime = (int) ini_get('max_execution_time');
        $details['max_execution_time'] = $maxTime . 's';

        if ($maxTime > 0 && $maxTime < self::MIN_EXECUTION_TIME) {
            $warnings[] = sprintf(
                __('Max execution time (%ds) may be too short for background processes.', 'wp-statistics'),
                $maxTime
            );
        }

        // Check required extensions
        $missingRequired = [];
        foreach (self::REQUIRED_EXTENSIONS as $ext) {
            if (!extension_loaded($ext)) {
                $missingRequired[] = $ext;
            }
        }
        $details['required_extensions'] = self::REQUIRED_EXTENSIONS;
        $details['missing_required']    = $missingRequired;

        if (!empty($missingRequired)) {
            $issues[] = sprintf(
                __('Missing required PHP extensions: %s', 'wp-statistics'),
                implode(', ', $missingRequired)
            );
        }

        // Check recommended extensions
        $missingRecommended = [];
        foreach (self::RECOMMENDED_EXTENSIONS as $ext) {
            if (!extension_loaded($ext)) {
                $missingRecommended[] = $ext;
            }
        }
        $details['recommended_extensions'] = self::RECOMMENDED_EXTENSIONS;
        $details['missing_recommended']    = $missingRecommended;

        if (!empty($missingRecommended)) {
            $warnings[] = sprintf(
                __('Missing recommended PHP extensions: %s', 'wp-statistics'),
                implode(', ', $missingRecommended)
            );
        }

        // Add PHP version
        $details['php_version'] = PHP_VERSION;

        // Determine result
        if (!empty($issues)) {
            return $this->fail(
                implode(' ', $issues),
                $details
            );
        }

        if (!empty($warnings)) {
            return $this->warning(
                implode(' ', $warnings),
                $details
            );
        }

        return $this->pass(
            sprintf(
                __('PHP %s with %dMB memory and all required extensions.', 'wp-statistics'),
                PHP_VERSION,
                $memoryLimit
            ),
            $details
        );
    }

    /**
     * Get memory limit in MB.
     *
     * @return int
     */
    private function getMemoryLimitMB(): int
    {
        $limit = ini_get('memory_limit');

        if (empty($limit) || $limit === '-1') {
            return -1; // Unlimited
        }

        $value = (int) $limit;
        $unit  = strtoupper(substr($limit, -1));

        switch ($unit) {
            case 'G':
                $value *= 1024;
                break;
            case 'K':
                $value /= 1024;
                break;
        }

        return $value;
    }
}
