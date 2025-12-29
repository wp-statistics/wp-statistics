<?php

namespace WP_Statistics\Service\Admin;

use WP_Statistics\Service\Database\Migrations\BackgroundProcess\BackgroundProcessFactory;

/**
 * Service class for managing background processes.
 *
 * Provides methods to list, cancel, retry, and check the status of background processes.
 * Used by both the Optimization tab UI and AJAX endpoints.
 */
class BackgroundProcessService
{
    /**
     * Status constants for background processes.
     */
    const STATUS_RUNNING = 'running';
    const STATUS_PENDING = 'pending';
    const STATUS_STUCK   = 'stuck';
    const STATUS_FAILED  = 'failed';

    /**
     * Minutes after which a process with no progress is considered stuck.
     *
     * @var int
     */
    const STUCK_THRESHOLD_MINUTES = 10;

    /**
     * Get all registered background processes with their status.
     *
     * @return array Array of process data with keys: key, title, status, progress, total, processed, last_activity, has_error
     */
    public function getAll(): array
    {
        $processes = BackgroundProcessFactory::getAllJobs();
        $result    = [];

        foreach (array_keys($processes) as $key) {
            $instance = BackgroundProcessFactory::getBackgroundProcess($key);

            if (!$instance) {
                continue;
            }

            $jobInstance = new $instance();
            $jobInstance->localizeJobTexts();

            $total     = method_exists($jobInstance, 'getTotal') ? $jobInstance->getTotal() : 0;
            $processed = method_exists($jobInstance, 'getProcessed') ? $jobInstance->getProcessed() : 0;

            // Skip completed processes (processed >= total and total > 0)
            if ($total > 0 && $processed >= $total) {
                continue;
            }

            $isProcessing  = method_exists($jobInstance, 'is_processing') ? $jobInstance->is_processing() : false;
            $isActive      = method_exists($jobInstance, 'is_active') ? $jobInstance->is_active() : false;
            $dispatchError = method_exists($jobInstance, 'get_dispatch_error') ? $jobInstance->get_dispatch_error() : false;

            $status       = $this->determineStatus($jobInstance, $key, $isProcessing, $isActive, $dispatchError);
            $lastActivity = $this->getLastActivity($jobInstance, $key);

            $result[] = [
                'key'           => $key,
                'title'         => method_exists($jobInstance, 'getJobTitle') ? $jobInstance->getJobTitle() : $key,
                'status'        => $status,
                'progress'      => $total > 0 ? (int) floor(($processed / $total) * 100) : 0,
                'total'         => $total,
                'processed'     => $processed,
                'last_activity' => $lastActivity,
                'has_error'     => !empty($dispatchError),
                'error_message' => $dispatchError ? ($dispatchError['message'] ?? '') : '',
            ];
        }

        return $result;
    }

    /**
     * Cancel a running/stuck/failed process.
     *
     * @param string $processKey The process key to cancel.
     *
     * @return bool True on success, false on failure.
     */
    public function cancel(string $processKey): bool
    {
        $instance = BackgroundProcessFactory::getBackgroundProcess($processKey);

        if (!$instance) {
            return false;
        }

        if (method_exists($instance, 'stopProcess')) {
            $instance->stopProcess();
        }

        if (method_exists($instance, 'setInitiated')) {
            $instance->setInitiated(false);
        }

        if (method_exists($instance, 'clear_dispatch_error')) {
            $instance->clear_dispatch_error();
        }

        return true;
    }

    /**
     * Retry a failed/stuck process.
     *
     * @param string $processKey The process key to retry.
     *
     * @return bool True on success, false on failure.
     */
    public function retry(string $processKey): bool
    {
        $instance = BackgroundProcessFactory::getBackgroundProcess($processKey);

        if (!$instance) {
            return false;
        }

        // Clear the dispatch error
        if (method_exists($instance, 'clear_dispatch_error')) {
            $instance->clear_dispatch_error();
        }

        // Attempt to dispatch again
        $result = $instance->dispatch();

        return !is_wp_error($result);
    }

    /**
     * Check if a process is stuck (no progress for X minutes).
     *
     * @param string $processKey The process key to check.
     * @param int    $minutes    Number of minutes threshold. Default 10.
     *
     * @return bool True if stuck, false otherwise.
     */
    public function isStuck(string $processKey, int $minutes = self::STUCK_THRESHOLD_MINUTES): bool
    {
        $instance = BackgroundProcessFactory::getBackgroundProcess($processKey);

        if (!$instance) {
            return false;
        }

        $isProcessing = method_exists($instance, 'is_processing') ? $instance->is_processing() : false;

        // If actively processing, not stuck
        if ($isProcessing) {
            return false;
        }

        $isActive = method_exists($instance, 'is_active') ? $instance->is_active() : false;

        // If not active at all, not stuck
        if (!$isActive) {
            return false;
        }

        // Check last activity time
        $lastActivity = $this->getLastActivityTimestamp($instance, $processKey);

        if ($lastActivity === 0) {
            return false;
        }

        $threshold = time() - ($minutes * 60);

        return $lastActivity < $threshold;
    }

    /**
     * Determine the status of a background process.
     *
     * @param object     $instance      The process instance.
     * @param string     $key           The process key.
     * @param bool       $isProcessing  Whether the process is currently processing.
     * @param bool       $isActive      Whether the process is active.
     * @param array|bool $dispatchError The dispatch error, if any.
     *
     * @return string One of: running, pending, stuck, failed
     */
    private function determineStatus($instance, string $key, bool $isProcessing, bool $isActive, $dispatchError): string
    {
        // Loopback/dispatch error = stuck (process can't start)
        if ($dispatchError) {
            return self::STATUS_STUCK;
        }

        // Currently processing
        if ($isProcessing) {
            return self::STATUS_RUNNING;
        }

        // Active but no progress for X minutes = failed
        if ($isActive && $this->isStuck($key)) {
            return self::STATUS_FAILED;
        }

        // Active but not processing = pending
        if ($isActive) {
            return self::STATUS_PENDING;
        }

        return self::STATUS_PENDING;
    }

    /**
     * Get human-readable last activity time.
     *
     * @param object $instance The process instance.
     * @param string $key      The process key.
     *
     * @return string Human-readable time like "Just now", "5 min ago", "1 hour ago"
     */
    private function getLastActivity($instance, string $key): string
    {
        $timestamp = $this->getLastActivityTimestamp($instance, $key);

        if ($timestamp === 0) {
            return __('Unknown', 'wp-statistics');
        }

        $diff = time() - $timestamp;

        if ($diff < 60) {
            return __('Just now', 'wp-statistics');
        }

        if ($diff < 3600) {
            $minutes = (int) floor($diff / 60);
            /* translators: %d: Number of minutes */
            return sprintf(_n('%d min ago', '%d mins ago', $minutes, 'wp-statistics'), $minutes);
        }

        if ($diff < 86400) {
            $hours = (int) floor($diff / 3600);
            /* translators: %d: Number of hours */
            return sprintf(_n('%d hour ago', '%d hours ago', $hours, 'wp-statistics'), $hours);
        }

        $days = (int) floor($diff / 86400);
        /* translators: %d: Number of days */
        return sprintf(_n('%d day ago', '%d days ago', $days, 'wp-statistics'), $days);
    }

    /**
     * Get the last activity timestamp for a process.
     *
     * @param object $instance The process instance.
     * @param string $key      The process key.
     *
     * @return int Unix timestamp, or 0 if unknown.
     */
    private function getLastActivityTimestamp($instance, string $key): int
    {
        // Check dispatch error time
        if (method_exists($instance, 'get_dispatch_error')) {
            $error = $instance->get_dispatch_error();
            if ($error && isset($error['time'])) {
                return (int) $error['time'];
            }
        }

        // Check process lock transient (indicates active processing)
        $lockKey = 'wp_statistics_' . $key . '_process_lock';
        $lock    = get_site_transient($lockKey);

        if ($lock) {
            // Lock exists, process was recently active
            return time();
        }

        // Fallback: check when the job was initiated
        // This is approximate since we don't store exact timestamps
        return 0;
    }
}
