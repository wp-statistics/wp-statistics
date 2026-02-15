<?php

namespace WP_Statistics\Service\ImportExport;

use WP_Statistics\Service\Cron\Events\DatabaseMaintenanceEvent;
use WP_Statistics\Components\Option;
use InvalidArgumentException;

/**
 * Executes data retention policies (purge/archive old data).
 *
 * Decouples the retention business logic from the AJAX endpoint so it can
 * be called from WP-CLI, cron, or other contexts.
 *
 * @since 15.0.0
 */
class DataRetentionService
{
    /**
     * Get the current retention policy from settings.
     *
     * @return array{mode: string, days: int}
     */
    public function getPolicy(): array
    {
        return [
            'mode' => Option::getValue('data_retention_mode', 'forever'),
            'days' => (int) Option::getValue('data_retention_days', 180),
        ];
    }

    /**
     * Purge data according to the current retention policy.
     *
     * @return array{mode: string, days: int, cutoff: string, affected: int, results: array}
     * @throws InvalidArgumentException If retention mode is 'forever' or days invalid.
     */
    public function purge(): array
    {
        $policy = $this->getPolicy();

        return $this->purgeWithPolicy($policy['mode'], $policy['days']);
    }

    /**
     * Purge data with explicitly specified parameters.
     *
     * @param string $mode 'delete' or 'archive'.
     * @param int    $days Number of days to retain.
     * @return array{mode: string, days: int, cutoff: string, affected: int, results: array}
     * @throws InvalidArgumentException If mode is 'forever' or days invalid.
     */
    public function purgeWithPolicy(string $mode, int $days): array
    {
        if ($mode === 'forever') {
            throw new InvalidArgumentException(
                __('Data retention mode is set to "Keep forever". No data will be purged.', 'wp-statistics')
            );
        }

        if ($days <= 0) {
            throw new InvalidArgumentException(
                __('Invalid retention period. Please set a valid number of days.', 'wp-statistics')
            );
        }

        $maintenanceEvent = new DatabaseMaintenanceEvent();
        $cutoffDate       = date('Y-m-d', strtotime("-{$days} days"));

        if ($mode === 'delete') {
            $results = $maintenanceEvent->deleteOldData($cutoffDate);
        } else {
            $results = $maintenanceEvent->archiveOldData($cutoffDate);
        }

        $totalAffected = array_sum($results);

        return [
            'mode'     => $mode,
            'days'     => $days,
            'cutoff'   => $cutoffDate,
            'affected' => $totalAffected,
            'results'  => $results,
        ];
    }
}
