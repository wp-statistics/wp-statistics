<?php

namespace WP_Statistics\Testing\Simulator;

use WP_Statistics\Testing\Simulator\Generators\RealisticVisitorGenerator;
use WP_Statistics\Testing\Simulator\Generators\InvalidDataGenerator;
use WP_Statistics\Testing\Simulator\Generators\AttackPayloadGenerator;
use WP_Statistics\Testing\Simulator\Http\CurlMultiSender;

/**
 * SimulatorRunner - Main orchestrator for the stress test simulation
 *
 * Coordinates all simulation components:
 * - Settings configuration
 * - Resource provisioning
 * - Data generation (realistic, invalid, attack)
 * - Parallel HTTP sending
 * - Progress tracking and checkpointing
 *
 * @package WP_Statistics\Testing\Simulator
 * @since 15.0.0
 */
class SimulatorRunner
{
    /**
     * Simulation configuration
     */
    private SimulatorConfig $config;

    /**
     * Settings configurator
     */
    private ?SettingsConfigurator $settingsConfigurator = null;

    /**
     * Resource provisioner
     */
    private ?ResourceProvisioner $resourceProvisioner = null;

    /**
     * Checkpoint manager
     */
    private ?CheckpointManager $checkpointManager = null;

    /**
     * HTTP sender
     */
    private ?CurlMultiSender $httpSender = null;

    /**
     * Data generators
     */
    private ?RealisticVisitorGenerator $visitorGenerator = null;
    private ?InvalidDataGenerator $invalidGenerator = null;
    private ?AttackPayloadGenerator $attackGenerator = null;

    /**
     * Prepared resources
     */
    private array $resources = [];

    /**
     * Available users for logged-in simulation
     */
    private array $users = [];

    /**
     * Output callback for logging
     */
    private $outputCallback = null;

    /**
     * Whether simulation is running
     */
    private bool $isRunning = false;

    /**
     * Constructor
     *
     * @param SimulatorConfig $config Simulation configuration
     */
    public function __construct(SimulatorConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Set output callback for logging
     *
     * @param callable $callback Function(string $message, string $level): void
     * @return self
     */
    public function setOutputCallback(callable $callback): self
    {
        $this->outputCallback = $callback;
        return $this;
    }

    /**
     * Log a message
     *
     * @param string $message Message to log
     * @param string $level Log level (info, success, warning, error)
     */
    private function log(string $message, string $level = 'info'): void
    {
        if ($this->outputCallback) {
            call_user_func($this->outputCallback, $message, $level);
        }
    }

    /**
     * Run the simulation
     *
     * @return array Final statistics
     */
    public function run(): array
    {
        $this->isRunning = true;
        $startTime = microtime(true);

        try {
            $this->log('Starting WP Statistics stress test simulation...', 'info');

            // Phase 1: Setup
            $this->setup();

            // Phase 2: Provisioning
            $this->provision();

            // Phase 3: Initialize checkpoint
            $this->initializeCheckpoint();

            // Phase 4: Run simulation
            $results = $this->executeSimulation();

            // Phase 5: Finalize
            $this->finalize($results);

            $this->isRunning = false;

            $totalTime = microtime(true) - $startTime;
            $this->log(sprintf('Simulation completed in %.2f seconds', $totalTime), 'success');

            return $results;

        } catch (\Exception $e) {
            $this->isRunning = false;
            $this->log('Simulation failed: ' . $e->getMessage(), 'error');

            if ($this->checkpointManager) {
                $this->checkpointManager->markFailed($e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * Setup phase - configure settings and initialize components
     */
    private function setup(): void
    {
        $this->log('Setting up simulation environment...', 'info');

        // Configure WP Statistics settings
        if ($this->config->autoConfigureSettings) {
            $this->settingsConfigurator = new SettingsConfigurator();
            $this->settingsConfigurator->ensureSettings();
            $this->log('WP Statistics settings configured', 'success');
        }

        // Initialize HTTP sender
        $this->httpSender = new CurlMultiSender(
            $this->config->targetUrl,
            $this->config->parallelWorkers
        );

        $this->httpSender
            ->setConnectTimeout($this->config->connectionTimeout)
            ->setRequestTimeout($this->config->requestTimeout)
            ->setMaxRetries($this->config->maxRetries);

        if ($this->config->requestDelayMs > 0) {
            $this->httpSender->setDelayBetweenRequests($this->config->requestDelayMs * 1000);
        }

        $this->log(sprintf('HTTP sender initialized: %d parallel workers', $this->config->parallelWorkers), 'info');

        // Initialize resource provisioner first (needed by generators)
        $this->resourceProvisioner = new ResourceProvisioner(
            function(string $message, string $level = 'info') {
                $this->log($message, $level);
            },
            $this->config->minPosts,
            $this->config->minPages,
            $this->config->minUsers
        );

        // Initialize generators
        $this->visitorGenerator = new RealisticVisitorGenerator(
            $this->config->dataDir,
            $this->config,
            $this->resourceProvisioner
        );

        if ($this->config->invalidDataRatio > 0) {
            $this->invalidGenerator = new InvalidDataGenerator($this->config->dataDir);
            $this->log(sprintf('Invalid data ratio: %.1f%%', $this->config->invalidDataRatio * 100), 'info');
        }

        if ($this->config->attackPayloadRatio > 0) {
            $this->attackGenerator = new AttackPayloadGenerator($this->config->dataDir);
            $this->log(sprintf('Attack payload ratio: %.1f%%', $this->config->attackPayloadRatio * 100), 'info');
        }
    }

    /**
     * Provisioning phase - ensure WordPress has content
     */
    private function provision(): void
    {
        $this->log('Checking WordPress resources...', 'info');

        // Ensure resources exist
        $this->resources = $this->resourceProvisioner->ensureResources();
        $resourceCount = count($this->resources);

        if ($resourceCount === 0) {
            throw new \RuntimeException('No resources available for simulation. Ensure WordPress has posts/pages.');
        }

        $this->log(sprintf('Found/created %d resources (posts/pages)', $resourceCount), 'success');

        // Ensure users for logged-in simulation
        if ($this->config->loggedInRatio > 0) {
            $this->users = $this->resourceProvisioner->ensureUsers($this->config->minUsers);
            $this->log(sprintf('Found/created %d users for logged-in simulation', count($this->users)), 'info');
        }
    }

    /**
     * Initialize checkpoint for resumability
     */
    private function initializeCheckpoint(): void
    {
        if (!$this->config->enableCheckpoints) {
            $this->log('Checkpoints disabled', 'info');
            return;
        }

        $identifier = $this->config->checkpointId ?? date('Y-m-d_His');
        $this->checkpointManager = new CheckpointManager(
            $this->config->checkpointDir,
            $identifier,
            $this->config->checkpointInterval
        );

        $this->checkpointManager->initialize($this->config);

        if ($this->checkpointManager->isResuming()) {
            $processed = $this->checkpointManager->getProcessedCount();
            $this->log(sprintf('Resuming from checkpoint: %d records already processed', $processed), 'info');
        }
    }

    /**
     * Execute the main simulation loop
     *
     * @return array Final statistics
     */
    private function executeSimulation(): array
    {
        $target = $this->config->targetRecords;
        $startOffset = 0;

        if ($this->checkpointManager && $this->checkpointManager->isResuming()) {
            $startOffset = $this->checkpointManager->getProcessedCount();
            $target = $this->checkpointManager->getRemainingCount();
        }

        $this->log(sprintf('Generating %d requests...', $target), 'info');

        // Create request generator
        $requestGenerator = $this->createRequestGenerator($target, $startOffset);

        // Progress tracking
        $batchSize = min(1000, max(100, $target / 100));
        $lastProgressReport = 0;

        $results = [
            'total'    => $target,
            'sent'     => 0,
            'success'  => 0,
            'failed'   => 0,
            'rejected' => 0,
            'invalid_data_sent'  => 0,
            'attack_data_sent'   => 0,
            'invalid_data_rejected' => 0,
            'attack_data_rejected'  => 0,
        ];

        // Execute with progress callback
        foreach ($this->httpSender->streamRequests($requestGenerator) as $result) {
            $results['sent']++;

            // Track data type
            $dataType = $result['request_data']['_data_type'] ?? 'normal';

            switch ($result['status']) {
                case 'success':
                    $results['success']++;
                    break;
                case 'rejected':
                    $results['rejected']++;
                    if ($dataType === 'invalid') {
                        $results['invalid_data_rejected']++;
                    } elseif ($dataType === 'attack') {
                        $results['attack_data_rejected']++;
                    }
                    break;
                default:
                    $results['failed']++;
            }

            // Track sent data types
            if ($dataType === 'invalid') {
                $results['invalid_data_sent']++;
            } elseif ($dataType === 'attack') {
                $results['attack_data_sent']++;
            }

            // Update checkpoint
            if ($this->checkpointManager) {
                $this->checkpointManager->recordProcessed($result['status'], $dataType);
            }

            // Progress reporting
            if ($results['sent'] - $lastProgressReport >= $batchSize) {
                $this->reportProgress($results, $target);
                $lastProgressReport = $results['sent'];
            }
        }

        // Final progress report
        $this->reportProgress($results, $target);

        return $results;
    }

    /**
     * Create a generator for requests
     *
     * @param int $count Number of requests to generate
     * @param int $seedOffset Seed offset for resumption
     * @return \Generator
     */
    private function createRequestGenerator(int $count, int $seedOffset = 0): \Generator
    {
        for ($i = 0; $i < $count; $i++) {
            $dataType = $this->selectDataType();

            switch ($dataType) {
                case 'invalid':
                    $generated = $this->invalidGenerator->generate();
                    $request = $generated['request_data'];
                    break;

                case 'attack':
                    $generated = $this->attackGenerator->generate();
                    $request = $generated['request_data'];
                    break;

                default:
                    $request = $this->visitorGenerator->generate();
            }

            // Add data type marker for tracking
            $request['_data_type'] = $dataType;

            yield $request;
        }
    }

    /**
     * Select which type of data to generate based on configured ratios
     *
     * @return string 'normal', 'invalid', or 'attack'
     */
    private function selectDataType(): string
    {
        $rand = mt_rand(1, 10000) / 10000;

        if ($this->invalidGenerator && $rand < $this->config->invalidDataRatio) {
            return 'invalid';
        }

        $rand -= $this->config->invalidDataRatio;

        if ($this->attackGenerator && $rand < $this->config->attackPayloadRatio) {
            return 'attack';
        }

        return 'normal';
    }

    /**
     * Report simulation progress
     *
     * @param array $results Current results
     * @param int $target Target count
     */
    private function reportProgress(array $results, int $target): void
    {
        $percent = ($results['sent'] / $target) * 100;
        $successRate = $results['sent'] > 0
            ? ($results['success'] / $results['sent']) * 100
            : 0;

        $eta = '';
        if ($this->checkpointManager) {
            $etaSeconds = $this->checkpointManager->getEstimatedTimeRemaining();
            if ($etaSeconds !== null) {
                $eta = sprintf(' | ETA: %s', $this->formatDuration($etaSeconds));
            }
        }

        $httpStats = $this->httpSender->getStats();
        $rps = $httpStats['requests_per_second'] ?? 0;

        $this->log(sprintf(
            '[%5.1f%%] Sent: %d/%d | Success: %.1f%% | Failed: %d | Rejected: %d | %.1f req/s%s',
            $percent,
            $results['sent'],
            $target,
            $successRate,
            $results['failed'],
            $results['rejected'],
            $rps,
            $eta
        ), 'info');
    }

    /**
     * Format duration in human readable format
     *
     * @param float $seconds Duration in seconds
     * @return string Formatted duration
     */
    private function formatDuration(float $seconds): string
    {
        if ($seconds < 60) {
            return sprintf('%ds', (int)$seconds);
        }

        $minutes = (int)($seconds / 60);
        $secs = (int)($seconds % 60);

        if ($minutes < 60) {
            return sprintf('%dm %ds', $minutes, $secs);
        }

        $hours = (int)($minutes / 60);
        $mins = $minutes % 60;

        return sprintf('%dh %dm', $hours, $mins);
    }

    /**
     * Finalize simulation
     *
     * @param array $results Final results
     */
    private function finalize(array $results): void
    {
        $this->log('Finalizing simulation...', 'info');

        // Complete checkpoint
        if ($this->checkpointManager) {
            $this->checkpointManager->markComplete();
        }

        // Restore settings if configured
        if ($this->settingsConfigurator && $this->config->restoreSettingsAfter) {
            $this->settingsConfigurator->restoreSettings();
            $this->log('Original WP Statistics settings restored', 'info');
        }

        // Log summary
        $this->logSummary($results);
    }

    /**
     * Log final summary
     *
     * @param array $results Final results
     */
    private function logSummary(array $results): void
    {
        $this->log('', 'info');
        $this->log('=== Simulation Summary ===', 'info');
        $this->log(sprintf('Total requests:      %d', $results['sent']), 'info');
        $this->log(sprintf('Successful:          %d (%.1f%%)', $results['success'], ($results['success'] / max(1, $results['sent'])) * 100), 'success');
        $this->log(sprintf('Failed:              %d', $results['failed']), $results['failed'] > 0 ? 'warning' : 'info');
        $this->log(sprintf('Rejected:            %d', $results['rejected']), 'info');

        if ($results['invalid_data_sent'] > 0) {
            $this->log(sprintf('Invalid data sent:    %d (rejected: %d)', $results['invalid_data_sent'], $results['invalid_data_rejected']), 'info');
        }

        if ($results['attack_data_sent'] > 0) {
            $this->log(sprintf('Attack payloads sent: %d (rejected: %d)', $results['attack_data_sent'], $results['attack_data_rejected']), 'info');
        }

        $httpStats = $this->httpSender->getStats();
        $this->log(sprintf('Avg response time:   %.4fs', $httpStats['avg_time']), 'info');
        $this->log(sprintf('Requests/second:     %.2f', $httpStats['requests_per_second']), 'info');

        if ($this->checkpointManager) {
            $summary = $this->checkpointManager->getSummary();
            $this->log(sprintf('Total elapsed time:  %s', $this->formatDuration($summary['elapsed_seconds'])), 'info');
        }
    }

    /**
     * Stop the simulation gracefully
     */
    public function stop(): void
    {
        if (!$this->isRunning) {
            return;
        }

        $this->log('Stopping simulation...', 'warning');

        if ($this->checkpointManager) {
            $this->checkpointManager->pause();
            $this->log('Checkpoint saved. Run with --resume to continue.', 'info');
        }

        $this->isRunning = false;
    }

    /**
     * Check if simulation is running
     *
     * @return bool
     */
    public function isRunning(): bool
    {
        return $this->isRunning;
    }

    /**
     * Get current configuration
     *
     * @return SimulatorConfig
     */
    public function getConfig(): SimulatorConfig
    {
        return $this->config;
    }

    /**
     * Get checkpoint manager
     *
     * @return CheckpointManager|null
     */
    public function getCheckpointManager(): ?CheckpointManager
    {
        return $this->checkpointManager;
    }

    /**
     * Get HTTP sender statistics
     *
     * @return array
     */
    public function getHttpStats(): array
    {
        return $this->httpSender ? $this->httpSender->getStats() : [];
    }

    /**
     * Run a quick test with minimal requests
     *
     * @param int $count Number of requests (default: 10)
     * @return array Results
     */
    public function runQuickTest(int $count = 10): array
    {
        $originalTarget = $this->config->targetRecords;
        $this->config->targetRecords = $count;
        $this->config->enableCheckpoints = false;

        try {
            return $this->run();
        } finally {
            $this->config->targetRecords = $originalTarget;
        }
    }

    /**
     * Validate configuration before running
     *
     * @return array Array of validation errors (empty if valid)
     */
    public function validateConfig(): array
    {
        $errors = [];

        if (empty($this->config->targetUrl)) {
            $errors[] = 'Target URL is required';
        }

        if ($this->config->targetRecords < 1) {
            $errors[] = 'Target records must be at least 1';
        }

        if ($this->config->parallelWorkers < 1 || $this->config->parallelWorkers > 100) {
            $errors[] = 'Parallel workers must be between 1 and 100';
        }

        if (!is_dir($this->config->dataDir)) {
            $errors[] = sprintf('Data directory not found: %s', $this->config->dataDir);
        }

        $totalRatio = $this->config->invalidDataRatio + $this->config->attackPayloadRatio;
        if ($totalRatio > 1) {
            $errors[] = 'Invalid + attack ratio cannot exceed 100%';
        }

        return $errors;
    }

    /**
     * Create runner for a specific scenario
     *
     * @param string $scenario Scenario name
     * @param string $targetUrl Target URL
     * @return self
     */
    public static function forScenario(string $scenario, string $targetUrl): self
    {
        switch ($scenario) {
            case 'stress':
                $config = SimulatorConfig::forStressTest(100000);
                $config->targetUrl = $targetUrl;
                break;

            case 'security':
                $config = SimulatorConfig::forSecurityTest();
                $config->targetUrl = $targetUrl;
                break;

            case 'invalid':
                $config = new SimulatorConfig();
                $config->targetUrl = $targetUrl;
                $config->targetRecords = 1000;
                $config->invalidDataRatio = 0.5;
                break;

            case 'mixed':
                $config = new SimulatorConfig();
                $config->targetUrl = $targetUrl;
                $config->targetRecords = 10000;
                $config->invalidDataRatio = 0.1;
                $config->attackPayloadRatio = 0.05;
                break;

            default:
                $config = new SimulatorConfig();
                $config->targetUrl = $targetUrl;
        }

        return new self($config);
    }
}
