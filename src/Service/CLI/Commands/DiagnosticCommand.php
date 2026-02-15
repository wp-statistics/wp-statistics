<?php

namespace WP_Statistics\Service\CLI\Commands;

use WP_CLI;
use WP_Statistics\Service\Admin\Diagnostic\DiagnosticManager;
use WP_Statistics\Service\Admin\Diagnostic\DiagnosticResult;

/**
 * Run diagnostic checks for WP Statistics.
 *
 * @since 15.0.0
 */
class DiagnosticCommand
{
    /**
     * Diagnostic manager.
     *
     * @var DiagnosticManager
     */
    private $diagnosticManager;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->diagnosticManager = new DiagnosticManager();
    }

    /**
     * Run diagnostic checks.
     *
     * ## OPTIONS
     *
     * [<check>]
     * : Run a specific check by key (e.g. geoip, server, cron, cache, loopback, tracking, schema).
     * If omitted, runs all checks.
     *
     * [--fresh]
     * : Bypass cache and run checks fresh.
     *
     * [--format=<format>]
     * : Output format.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - json
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *      # Run all diagnostics
     *      $ wp statistics diagnostic run
     *
     *      # Run a specific check
     *      $ wp statistics diagnostic run geoip
     *
     *      # Force fresh results (bypass cache)
     *      $ wp statistics diagnostic run --fresh
     *
     *      # Output as JSON
     *      $ wp statistics diagnostic run --format=json
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function run($args, $assoc_args)
    {
        $format = $assoc_args['format'] ?? 'table';
        $fresh  = isset($assoc_args['fresh']);

        if ($fresh) {
            $this->diagnosticManager->clearCache();
        }

        if (!empty($args[0])) {
            // Run a single check
            $result = $this->diagnosticManager->runCheck($args[0]);

            if ($result === null) {
                WP_CLI::error(sprintf('Unknown check: %s', $args[0]));
                return;
            }

            $items = [$this->formatResult($result)];
        } else {
            // Run all checks
            $results = $this->diagnosticManager->runAll($fresh);
            $items   = array_map([$this, 'formatResult'], $results);
        }

        \WP_CLI\Utils\format_items($format, $items, ['Check', 'Status', 'Message']);

        // Report summary
        $failures = array_filter($items, function ($item) {
            return $item['Status'] === DiagnosticResult::STATUS_FAIL;
        });

        if (!empty($failures)) {
            WP_CLI::warning(sprintf('%d check(s) failed.', count($failures)));
        } else {
            WP_CLI::success('All checks passed.');
        }
    }

    /**
     * Show cached diagnostic results.
     *
     * Returns the last cached diagnostic results without running new checks.
     * Exits with code 1 if any failures exist (useful for CI).
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format.
     * ---
     * default: table
     * options:
     *   - table
     *   - csv
     *   - json
     *   - yaml
     * ---
     *
     * ## EXAMPLES
     *
     *      $ wp statistics diagnostic status
     *      $ wp statistics diagnostic status --format=json
     *
     * @param array $args       Positional arguments.
     * @param array $assoc_args Associative arguments.
     * @return void
     */
    public function status($args, $assoc_args)
    {
        $format  = $assoc_args['format'] ?? 'table';
        $results = $this->diagnosticManager->getResults();

        if (empty($results)) {
            WP_CLI::warning('No cached diagnostic results. Run "wp statistics diagnostic run" first.');
            return;
        }

        $items = array_map([$this, 'formatResult'], $results);

        \WP_CLI\Utils\format_items($format, $items, ['Check', 'Status', 'Message']);

        // Exit non-zero if failures exist
        $failures = array_filter($items, function ($item) {
            return $item['Status'] === DiagnosticResult::STATUS_FAIL;
        });

        if (!empty($failures)) {
            WP_CLI::halt(1);
        }
    }

    /**
     * Format a DiagnosticResult into a display row.
     *
     * @param DiagnosticResult $result Diagnostic result.
     * @return array Associative array with Check, Status, Message keys.
     */
    private function formatResult(DiagnosticResult $result): array
    {
        return [
            'Check'   => $result->label ?? $result->key,
            'Status'  => $result->status,
            'Message' => $result->message ?? '',
        ];
    }
}
