<?php

namespace WP_Statistics\Service\Admin\Notice\Notices;

use WP_Statistics\Service\Admin\Notice\NoticeItem;
use WP_Statistics\Service\Admin\Diagnostic\DiagnosticManager;
use WP_Statistics\Utils\User;

/**
 * Diagnostic Notice Generator.
 *
 * Generates admin notices when diagnostic checks fail.
 *
 * @since 15.0.0
 */
class DiagnosticNotice implements NoticeInterface
{
    /**
     * Notice ID for diagnostic issues.
     */
    private const NOTICE_ID = 'diagnostic_issues';

    /**
     * {@inheritDoc}
     */
    public function shouldRun(): bool
    {
        // Only show to users who can manage WP Statistics
        return User::hasAccess('manage');
    }

    /**
     * {@inheritDoc}
     */
    public function getNotices(): array
    {
        $manager = new DiagnosticManager();

        // Get failed and warning checks from lightweight results
        // (Heavy checks only run on-demand, so we only show notices for lightweight issues)
        $results = $manager->getResults();

        $failCount    = 0;
        $warningCount = 0;

        foreach ($results as $result) {
            if ($result->isFailed()) {
                $failCount++;
            } elseif ($result->isWarning()) {
                $warningCount++;
            }
        }

        // No issues, no notice
        if ($failCount === 0 && $warningCount === 0) {
            return [];
        }

        // Build message based on severity
        $totalIssues = $failCount + $warningCount;
        $type        = $failCount > 0 ? 'error' : 'warning';

        if ($failCount > 0 && $warningCount > 0) {
            $message = sprintf(
                /* translators: 1: number of critical issues, 2: number of warnings */
                __('%1$d critical issue(s) and %2$d warning(s) detected that may affect WP Statistics functionality.', 'wp-statistics'),
                $failCount,
                $warningCount
            );
        } elseif ($failCount > 0) {
            $message = sprintf(
                /* translators: %d: number of critical issues */
                _n(
                    '%d critical issue detected that may affect WP Statistics functionality.',
                    '%d critical issues detected that may affect WP Statistics functionality.',
                    $failCount,
                    'wp-statistics'
                ),
                $failCount
            );
        } else {
            $message = sprintf(
                /* translators: %d: number of warnings */
                _n(
                    '%d configuration warning detected.',
                    '%d configuration warnings detected.',
                    $warningCount,
                    'wp-statistics'
                ),
                $warningCount
            );
        }

        return [
            new NoticeItem([
                'id'          => self::NOTICE_ID,
                'message'     => $message,
                'type'        => $type,
                'actionUrl'   => admin_url('admin.php?page=wp-statistics#/tools/diagnostics'),
                'actionLabel' => __('View Diagnostics', 'wp-statistics'),
                'dismissible' => true,
                'priority'    => 5, // High priority for diagnostic issues
            ]),
        ];
    }
}
