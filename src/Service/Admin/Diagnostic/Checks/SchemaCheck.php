<?php

namespace WP_Statistics\Service\Admin\Diagnostic\Checks;

use WP_Statistics\Service\Admin\Diagnostic\DiagnosticResult;
use WP_Statistics\Service\Database\Managers\SchemaMaintainer;

/**
 * Schema Health Check.
 *
 * Checks database schema for missing tables or columns.
 * This is an on-demand check due to potential performance impact on large databases.
 *
 * @since 15.0.0
 */
class SchemaCheck extends AbstractCheck
{
    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return 'schema';
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel(): string
    {
        return __('Database Schema', 'wp-statistics');
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return __('Verifies that all required database tables and columns exist.', 'wp-statistics');
    }

    /**
     * {@inheritDoc}
     */
    public function isLightweight(): bool
    {
        // Schema check can be slow on large databases
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function run(): DiagnosticResult
    {
        $results = SchemaMaintainer::check();

        $status = $results['status'] ?? 'unknown';
        $issues = $results['issues'] ?? [];
        $errors = $results['errors'] ?? [];

        if ($status === 'success' || (empty($issues) && empty($errors))) {
            return $this->pass(
                __('All tables and columns are present.', 'wp-statistics')
            );
        }

        // Build message based on issues found
        $issueCount = count($issues);
        $errorCount = count($errors);

        if ($errorCount > 0) {
            return $this->fail(
                sprintf(
                    /* translators: %d: number of errors */
                    _n(
                        '%d schema error detected.',
                        '%d schema errors detected.',
                        $errorCount,
                        'wp-statistics'
                    ),
                    $errorCount
                ),
                [
                    'issues' => $issues,
                    'errors' => $errors,
                    'canRepair' => true,
                ]
            );
        }

        return $this->warning(
            sprintf(
                /* translators: %d: number of issues */
                _n(
                    '%d schema issue found.',
                    '%d schema issues found.',
                    $issueCount,
                    'wp-statistics'
                ),
                $issueCount
            ),
            [
                'issues' => $issues,
                'canRepair' => true,
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getHelpUrl(): ?string
    {
        return 'https://wp-statistics.com/resources/database-schema/';
    }
}
