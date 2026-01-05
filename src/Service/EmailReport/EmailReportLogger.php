<?php

namespace WP_Statistics\Service\EmailReport;

use WP_Statistics\Components\Option;

/**
 * Email Report Logger
 *
 * Logs email report send results using WordPress options.
 * Maintains a rolling log of the last N entries to prevent unbounded growth.
 *
 * @package WP_Statistics\Service\EmailReport
 * @since 15.0.0
 */
class EmailReportLogger
{
    /**
     * Option key for email log storage
     */
    private const OPTION_KEY = 'email_report_log';

    /**
     * Maximum number of log entries to keep
     */
    private const MAX_ENTRIES = 50;

    /**
     * Log an email send result.
     *
     * @param array $data Log entry data with keys:
     *                    - success: bool
     *                    - recipients: array
     *                    - frequency: string
     *                    - error: string|null
     * @return void
     */
    public function log(array $data): void
    {
        $log = $this->getLog();

        $entry = [
            'sent_at'    => current_time('mysql'),
            'timestamp'  => time(),
            'success'    => !empty($data['success']),
            'recipients' => $data['recipients'] ?? [],
            'frequency'  => $data['frequency'] ?? 'weekly',
            'error'      => $data['error'] ?? null,
        ];

        // Prepend new entry to the beginning
        array_unshift($log, $entry);

        // Keep only last N entries
        $log = array_slice($log, 0, self::MAX_ENTRIES);

        Option::update(self::OPTION_KEY, $log);
    }

    /**
     * Get the full log.
     *
     * @return array
     */
    public function getLog(): array
    {
        $log = Option::get(self::OPTION_KEY);

        if (!is_array($log)) {
            return [];
        }

        return $log;
    }

    /**
     * Get the last N log entries.
     *
     * @param int $count Number of entries to retrieve.
     * @return array
     */
    public function getRecentEntries(int $count = 10): array
    {
        $log = $this->getLog();
        return array_slice($log, 0, $count);
    }

    /**
     * Get the last successful send timestamp.
     *
     * @return string|null MySQL datetime string or null if never sent.
     */
    public function getLastSent(): ?string
    {
        foreach ($this->getLog() as $entry) {
            if (!empty($entry['success'])) {
                return $entry['sent_at'] ?? null;
            }
        }

        return null;
    }

    /**
     * Get the last send result (success or failure).
     *
     * @return array|null Last log entry or null if no entries.
     */
    public function getLastResult(): ?array
    {
        $log = $this->getLog();
        return $log[0] ?? null;
    }

    /**
     * Get count of successful sends.
     *
     * @return int
     */
    public function getSuccessCount(): int
    {
        return count(array_filter($this->getLog(), function ($entry) {
            return !empty($entry['success']);
        }));
    }

    /**
     * Get count of failed sends.
     *
     * @return int
     */
    public function getFailureCount(): int
    {
        return count(array_filter($this->getLog(), function ($entry) {
            return empty($entry['success']);
        }));
    }

    /**
     * Get all failed sends.
     *
     * @return array
     */
    public function getFailures(): array
    {
        return array_filter($this->getLog(), function ($entry) {
            return empty($entry['success']);
        });
    }

    /**
     * Check if the last send was successful.
     *
     * @return bool|null True if successful, false if failed, null if no entries.
     */
    public function wasLastSendSuccessful(): ?bool
    {
        $last = $this->getLastResult();

        if ($last === null) {
            return null;
        }

        return !empty($last['success']);
    }

    /**
     * Get statistics summary.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $log     = $this->getLog();
        $total   = count($log);
        $success = $this->getSuccessCount();
        $failed  = $total - $success;

        return [
            'total_entries'     => $total,
            'successful_sends'  => $success,
            'failed_sends'      => $failed,
            'success_rate'      => $total > 0 ? round(($success / $total) * 100, 1) : 0,
            'last_sent'         => $this->getLastSent(),
            'last_result'       => $this->getLastResult(),
        ];
    }

    /**
     * Clear all log entries.
     *
     * @return void
     */
    public function clear(): void
    {
        Option::delete(self::OPTION_KEY);
    }

    /**
     * Get log entries within a date range.
     *
     * @param string $startDate Start date (Y-m-d format).
     * @param string $endDate   End date (Y-m-d format).
     * @return array
     */
    public function getEntriesBetween(string $startDate, string $endDate): array
    {
        $startTimestamp = strtotime($startDate . ' 00:00:00');
        $endTimestamp   = strtotime($endDate . ' 23:59:59');

        return array_filter($this->getLog(), function ($entry) use ($startTimestamp, $endTimestamp) {
            $entryTimestamp = $entry['timestamp'] ?? strtotime($entry['sent_at'] ?? '');
            return $entryTimestamp >= $startTimestamp && $entryTimestamp <= $endTimestamp;
        });
    }

    /**
     * Format log entry for display.
     *
     * @param array $entry Log entry.
     * @return array Formatted entry.
     */
    public function formatEntry(array $entry): array
    {
        return [
            'date'       => date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $entry['timestamp'] ?? strtotime($entry['sent_at'] ?? '')),
            'status'     => $entry['success'] ? __('Sent', 'wp-statistics') : __('Failed', 'wp-statistics'),
            'recipients' => implode(', ', $entry['recipients'] ?? []),
            'frequency'  => ucfirst($entry['frequency'] ?? 'unknown'),
            'error'      => $entry['error'] ?? '',
        ];
    }

    /**
     * Get formatted log for display.
     *
     * @param int $count Number of entries.
     * @return array
     */
    public function getFormattedLog(int $count = 10): array
    {
        $entries = $this->getRecentEntries($count);

        return array_map([$this, 'formatEntry'], $entries);
    }
}
