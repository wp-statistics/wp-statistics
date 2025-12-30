<?php

namespace WP_Statistics\Testing\Simulator;

/**
 * SimulatorConfig - Configuration value object for the stress test simulator
 *
 * Holds all configuration options for simulation runs including:
 * - Volume settings (target records, visitors per day)
 * - Performance settings (parallel workers, batch size, delays)
 * - Scenario settings (scenario type, invalid/attack ratios)
 * - Date range settings
 * - Output settings (verbose, dry-run, log file)
 *
 * @package WP_Statistics\Testing\Simulator
 * @since 15.0.0
 */
class SimulatorConfig
{
    // =========================================================================
    // Volume Settings
    // =========================================================================

    /**
     * Total number of records to generate
     */
    public int $targetRecords = 1000;

    /**
     * Average visitors per day
     */
    public int $visitorsPerDay = 50;

    /**
     * Number of days to generate data for
     */
    public int $daysToGenerate = 7;

    // =========================================================================
    // Date Range Settings
    // =========================================================================

    /**
     * Start date (YYYY-MM-DD format)
     */
    public ?string $dateFrom = null;

    /**
     * End date (YYYY-MM-DD format)
     */
    public ?string $dateTo = null;

    // =========================================================================
    // Performance Settings
    // =========================================================================

    /**
     * Number of parallel HTTP workers
     */
    public int $parallelWorkers = 10;

    /**
     * Records per batch (for checkpointing)
     */
    public int $batchSize = 1000;

    /**
     * Delay between requests in milliseconds
     */
    public int $delayMs = 50;

    /**
     * HTTP request timeout in seconds
     */
    public int $httpTimeout = 30;

    /**
     * HTTP connection timeout in seconds
     */
    public int $connectionTimeout = 10;

    /**
     * HTTP request timeout in seconds (alias for httpTimeout)
     */
    public int $requestTimeout = 30;

    /**
     * Maximum retries for failed HTTP requests
     */
    public int $maxRetries = 2;

    /**
     * Delay between requests in milliseconds
     */
    public int $requestDelayMs = 0;

    /**
     * Auto-configure WP Statistics settings before running
     */
    public bool $autoConfigureSettings = true;

    /**
     * Restore settings after simulation completes
     */
    public bool $restoreSettingsAfter = false;

    /**
     * Checkpoint save interval (number of records)
     */
    public int $checkpointInterval = 10000;

    // =========================================================================
    // Resource Provisioning Settings
    // =========================================================================

    /**
     * Minimum number of posts required
     */
    public int $minPosts = 10;

    /**
     * Minimum number of pages required
     */
    public int $minPages = 5;

    /**
     * Minimum number of users required for logged-in simulation
     */
    public int $minUsers = 5;

    // =========================================================================
    // Scenario Settings
    // =========================================================================

    /**
     * Scenario type: normal, stress, invalid, security, mixed
     */
    public string $scenario = 'normal';

    /**
     * Ratio of invalid data to include (0.0 to 1.0)
     */
    public float $invalidDataRatio = 0.0;

    /**
     * Ratio of attack payloads to include (0.0 to 1.0)
     */
    public float $attackPayloadRatio = 0.0;

    /**
     * Enable enhanced realistic data patterns
     */
    public bool $realistic = true;

    /**
     * Ratio of logged-in visitors (0.0 to 1.0)
     */
    public float $loggedInRatio = 0.12;

    // =========================================================================
    // Output Settings
    // =========================================================================

    /**
     * Verbose output
     */
    public bool $verbose = false;

    /**
     * Dry run mode (no actual HTTP requests)
     */
    public bool $dryRun = false;

    /**
     * Log file path
     */
    public ?string $logFile = null;

    /**
     * Generate security report
     */
    public bool $securityReport = false;

    /**
     * Validate HTTP responses
     */
    public bool $validateResponses = false;

    /**
     * Output format: text, json
     */
    public string $outputFormat = 'text';

    // =========================================================================
    // Target Settings
    // =========================================================================

    /**
     * Custom site URL (optional)
     */
    public ?string $siteUrl = null;

    /**
     * Target URL for HTTP requests (admin-ajax.php)
     */
    public string $targetUrl = '';

    /**
     * Directory containing data JSON files
     */
    public string $dataDir = '';

    /**
     * Directory for checkpoint files
     */
    public string $checkpointDir = '';

    /**
     * Enable checkpoint saving
     */
    public bool $enableCheckpoints = true;

    /**
     * Checkpoint file for resumability
     */
    public ?string $checkpointFile = null;

    /**
     * Checkpoint identifier for named checkpoints
     */
    public string $checkpointId = '';

    /**
     * Resume from checkpoint
     */
    public bool $resume = false;

    // =========================================================================
    // Factory Methods
    // =========================================================================

    /**
     * Create configuration from CLI arguments
     *
     * @param array $args Parsed CLI arguments
     * @return self
     */
    public static function fromCliArgs(array $args): self
    {
        $config = new self();
        $config->setDefaultDirectories();

        // Volume settings
        if (isset($args['target'])) {
            $config->targetRecords = self::parseVolumeString($args['target']);
        }
        if (isset($args['visitors-per-day'])) {
            $config->visitorsPerDay = (int) $args['visitors-per-day'];
        }
        if (isset($args['days'])) {
            $config->daysToGenerate = (int) $args['days'];
        }

        // Date range
        if (isset($args['from'])) {
            $config->dateFrom = $args['from'];
        }
        if (isset($args['to'])) {
            $config->dateTo = $args['to'];
        }

        // Performance settings
        if (isset($args['workers'])) {
            $config->parallelWorkers = min(100, max(1, (int) $args['workers']));
        }
        if (isset($args['batch-size'])) {
            $config->batchSize = (int) $args['batch-size'];
        }
        if (isset($args['delay'])) {
            $config->delayMs = (int) $args['delay'];
        }

        // Scenario settings
        if (isset($args['scenario'])) {
            $config->scenario = $args['scenario'];
        }
        if (isset($args['invalid-ratio'])) {
            $config->invalidDataRatio = min(1.0, max(0.0, (float) $args['invalid-ratio']));
        }
        if (isset($args['attack-ratio'])) {
            $config->attackPayloadRatio = min(1.0, max(0.0, (float) $args['attack-ratio']));
        }
        if (isset($args['logged-in-ratio'])) {
            $config->loggedInRatio = min(1.0, max(0.0, (float) $args['logged-in-ratio']));
        }

        // Flags
        $config->realistic = isset($args['realistic']) || !isset($args['no-realistic']);
        $config->verbose = isset($args['verbose']);
        $config->dryRun = isset($args['dry-run']);
        $config->securityReport = isset($args['security-report']);
        $config->validateResponses = isset($args['validate-responses']);
        $config->resume = isset($args['resume']);

        // Other settings
        if (isset($args['url'])) {
            $config->siteUrl = rtrim($args['url'], '/');
            $config->targetUrl = $config->siteUrl . '/wp-admin/admin-ajax.php';
        }
        if (isset($args['log-file'])) {
            $config->logFile = $args['log-file'];
        }
        if (isset($args['checkpoint'])) {
            $config->checkpointFile = $args['checkpoint'];
        }
        if (isset($args['checkpoint-id'])) {
            $config->checkpointId = $args['checkpoint-id'];
        }
        if (isset($args['no-checkpoints'])) {
            $config->enableCheckpoints = false;
        }
        if (isset($args['output-format'])) {
            $config->outputFormat = $args['output-format'];
        }

        return $config;
    }

    /**
     * Create configuration for stress testing
     *
     * @param int $targetRecords Target number of records
     * @return self
     */
    public static function forStressTest(int $targetRecords): self
    {
        $config = new self();
        $config->setDefaultDirectories();
        $config->targetRecords = $targetRecords;
        $config->scenario = 'stress';
        $config->parallelWorkers = 20;
        $config->delayMs = 0;
        $config->batchSize = 10000;
        $config->realistic = true;
        return $config;
    }

    /**
     * Create configuration for security testing
     *
     * @return self
     */
    public static function forSecurityTest(): self
    {
        $config = new self();
        $config->setDefaultDirectories();
        $config->scenario = 'security';
        $config->attackPayloadRatio = 1.0;
        $config->validateResponses = true;
        $config->securityReport = true;
        $config->parallelWorkers = 5;
        $config->delayMs = 100;
        return $config;
    }

    /**
     * Create configuration for invalid data testing
     *
     * @return self
     */
    public static function forInvalidDataTest(): self
    {
        $config = new self();
        $config->setDefaultDirectories();
        $config->scenario = 'invalid';
        $config->invalidDataRatio = 1.0;
        $config->validateResponses = true;
        $config->parallelWorkers = 5;
        return $config;
    }

    /**
     * Create configuration for PHPUnit tests
     *
     * @param string $targetUrl Target URL for HTTP requests
     * @param array $overrides Configuration overrides
     * @return self
     */
    public static function forPHPUnit(string $targetUrl, array $overrides = []): self
    {
        $config = new self();
        $config->setDefaultDirectories();
        $config->targetUrl = $targetUrl;
        $config->targetRecords = 100;
        $config->visitorsPerDay = 10;
        $config->daysToGenerate = 1;
        $config->parallelWorkers = 1;
        $config->delayMs = 0;
        $config->verbose = false;
        $config->dryRun = true;
        $config->enableCheckpoints = false;

        foreach ($overrides as $key => $value) {
            if (property_exists($config, $key)) {
                $config->$key = $value;
            }
        }

        return $config;
    }

    /**
     * Create configuration for quick testing
     *
     * @return self
     */
    public static function forQuickTest(): self
    {
        $config = new self();
        $config->setDefaultDirectories();
        $config->targetRecords = 10;
        $config->visitorsPerDay = 20;
        $config->daysToGenerate = 1;
        $config->parallelWorkers = 5;
        $config->delayMs = 10;
        $config->verbose = true;
        $config->enableCheckpoints = false;
        return $config;
    }

    // =========================================================================
    // Helper Methods
    // =========================================================================

    /**
     * Set default directories based on bin location
     *
     * @return void
     */
    public function setDefaultDirectories(): void
    {
        // Default data directory is bin/data/
        if (empty($this->dataDir)) {
            $this->dataDir = dirname(__DIR__) . '/data';
        }

        // Default checkpoint directory
        if (empty($this->checkpointDir)) {
            $this->checkpointDir = dirname(__DIR__) . '/checkpoints';
        }

        // Create checkpoint directory if it doesn't exist
        if ($this->enableCheckpoints && !is_dir($this->checkpointDir)) {
            @mkdir($this->checkpointDir, 0755, true);
        }
    }

    /**
     * Set the target URL (call after WordPress is loaded if using admin_url)
     *
     * @param string|null $targetUrl Optional target URL, uses admin_url if not provided
     * @return void
     */
    public function setTargetUrl(?string $targetUrl = null): void
    {
        if ($targetUrl) {
            $this->targetUrl = $targetUrl;
        } elseif (function_exists('admin_url')) {
            $this->targetUrl = admin_url('admin-ajax.php');
        }
    }

    /**
     * Parse volume string (e.g., "100K", "1M", "10M")
     *
     * @param string $value Volume string
     * @return int
     */
    private static function parseVolumeString(string $value): int
    {
        $value = strtoupper(trim($value));

        if (preg_match('/^(\d+(?:\.\d+)?)\s*(K|M|B)?$/', $value, $matches)) {
            $num = (float) $matches[1];
            $suffix = $matches[2] ?? '';

            switch ($suffix) {
                case 'K':
                    return (int) ($num * 1000);
                case 'M':
                    return (int) ($num * 1000000);
                case 'B':
                    return (int) ($num * 1000000000);
                default:
                    return (int) $num;
            }
        }

        return (int) $value;
    }

    /**
     * Get computed date range
     *
     * @return array ['from' => string, 'to' => string]
     */
    public function getDateRange(): array
    {
        $to = $this->dateTo ?? date('Y-m-d');
        $from = $this->dateFrom ?? date('Y-m-d', strtotime("-{$this->daysToGenerate} days"));

        return [
            'from' => $from,
            'to'   => $to,
        ];
    }

    /**
     * Get AJAX URL based on configuration
     *
     * @return string
     */
    public function getAjaxUrl(): string
    {
        if ($this->siteUrl) {
            return $this->siteUrl . '/wp-admin/admin-ajax.php';
        }
        return admin_url('admin-ajax.php');
    }

    /**
     * Get site URL
     *
     * @return string
     */
    public function getSiteUrl(): string
    {
        return $this->siteUrl ?? home_url();
    }

    /**
     * Get checkpoint file path (with default)
     *
     * @return string
     */
    public function getCheckpointPath(): string
    {
        return $this->checkpointFile ?? sys_get_temp_dir() . '/wp-statistics-simulator-checkpoint.json';
    }

    /**
     * Validate configuration
     *
     * @return array Array of validation errors (empty if valid)
     */
    public function validate(): array
    {
        $errors = [];

        if ($this->targetRecords < 1) {
            $errors[] = 'Target records must be at least 1';
        }

        if ($this->parallelWorkers < 1 || $this->parallelWorkers > 100) {
            $errors[] = 'Parallel workers must be between 1 and 100';
        }

        if ($this->invalidDataRatio < 0 || $this->invalidDataRatio > 1) {
            $errors[] = 'Invalid data ratio must be between 0.0 and 1.0';
        }

        if ($this->attackPayloadRatio < 0 || $this->attackPayloadRatio > 1) {
            $errors[] = 'Attack payload ratio must be between 0.0 and 1.0';
        }

        if ($this->loggedInRatio < 0 || $this->loggedInRatio > 1) {
            $errors[] = 'Logged-in ratio must be between 0.0 and 1.0';
        }

        $validScenarios = ['normal', 'stress', 'invalid', 'security', 'mixed'];
        if (!in_array($this->scenario, $validScenarios)) {
            $errors[] = 'Invalid scenario. Must be one of: ' . implode(', ', $validScenarios);
        }

        return $errors;
    }

    /**
     * Convert to array for serialization
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'targetRecords'      => $this->targetRecords,
            'visitorsPerDay'     => $this->visitorsPerDay,
            'daysToGenerate'     => $this->daysToGenerate,
            'dateFrom'           => $this->dateFrom,
            'dateTo'             => $this->dateTo,
            'parallelWorkers'    => $this->parallelWorkers,
            'batchSize'          => $this->batchSize,
            'delayMs'            => $this->delayMs,
            'scenario'           => $this->scenario,
            'invalidDataRatio'   => $this->invalidDataRatio,
            'attackPayloadRatio' => $this->attackPayloadRatio,
            'loggedInRatio'      => $this->loggedInRatio,
            'realistic'          => $this->realistic,
            'verbose'            => $this->verbose,
            'dryRun'             => $this->dryRun,
            'siteUrl'            => $this->siteUrl,
        ];
    }

    /**
     * Print configuration summary
     */
    public function printSummary(): void
    {
        $dateRange = $this->getDateRange();

        echo "Configuration:\n";
        echo "  Target Records: " . number_format($this->targetRecords) . "\n";
        echo "  Date Range: {$dateRange['from']} to {$dateRange['to']}\n";
        echo "  Scenario: {$this->scenario}\n";
        echo "  Parallel Workers: {$this->parallelWorkers}\n";
        echo "  Logged-in Ratio: " . ($this->loggedInRatio * 100) . "%\n";

        if ($this->invalidDataRatio > 0) {
            echo "  Invalid Data Ratio: " . ($this->invalidDataRatio * 100) . "%\n";
        }
        if ($this->attackPayloadRatio > 0) {
            echo "  Attack Payload Ratio: " . ($this->attackPayloadRatio * 100) . "%\n";
        }
        if ($this->dryRun) {
            echo "  Mode: DRY RUN\n";
        }
    }
}
