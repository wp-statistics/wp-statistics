<?php

namespace WP_Statistics\Testing\Simulator;

/**
 * CheckpointManager - Manages checkpoint state for resumable simulation runs
 *
 * Features:
 * - Periodic state saving
 * - Automatic checkpoint file management
 * - State validation on resume
 * - Progress tracking across restarts
 *
 * @package WP_Statistics\Testing\Simulator
 * @since 15.0.0
 */
class CheckpointManager
{
    /**
     * Path to checkpoint file
     */
    private string $checkpointFile;

    /**
     * How often to save checkpoints (every N records)
     */
    private int $saveInterval;

    /**
     * Current checkpoint state
     */
    private array $state = [];

    /**
     * Records processed since last save
     */
    private int $recordsSinceLastSave = 0;

    /**
     * Whether a valid checkpoint was loaded
     */
    private bool $resumedFromCheckpoint = false;

    /**
     * Start time of current run
     */
    private float $runStartTime;

    /**
     * Constructor
     *
     * @param string $checkpointDir Directory for checkpoint files
     * @param string $identifier Unique identifier for this simulation run
     * @param int $saveInterval Save checkpoint every N records (default: 10000)
     */
    public function __construct(string $checkpointDir, string $identifier, int $saveInterval = 10000)
    {
        if (!is_dir($checkpointDir)) {
            mkdir($checkpointDir, 0755, true);
        }

        $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '_', $identifier);
        $this->checkpointFile = rtrim($checkpointDir, '/') . '/checkpoint_' . $safeId . '.json';
        $this->saveInterval = max(100, $saveInterval);
        $this->runStartTime = microtime(true);

        $this->initializeState();
    }

    /**
     * Initialize or load existing checkpoint state
     */
    private function initializeState(): void
    {
        if (file_exists($this->checkpointFile)) {
            $this->loadCheckpoint();
        } else {
            $this->resetState();
        }
    }

    /**
     * Reset to initial state
     */
    private function resetState(): void
    {
        $this->state = [
            'version'            => '1.0.0',
            'identifier'         => basename($this->checkpointFile, '.json'),
            'created_at'         => date('Y-m-d H:i:s'),
            'updated_at'         => date('Y-m-d H:i:s'),
            'status'             => 'running',
            'config_hash'        => null,
            'processed'          => 0,
            'target'             => 0,
            'successful'         => 0,
            'failed'             => 0,
            'rejected'           => 0,
            'invalid_data_count' => 0,
            'attack_data_count'  => 0,
            'seed_offset'        => 0,
            'last_timestamp'     => null,
            'elapsed_seconds'    => 0,
            'runs'               => [],
        ];
    }

    /**
     * Load checkpoint from file
     *
     * @return bool True if loaded successfully
     */
    private function loadCheckpoint(): bool
    {
        try {
            $contents = file_get_contents($this->checkpointFile);
            if ($contents === false) {
                $this->resetState();
                return false;
            }

            $data = json_decode($contents, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->resetState();
                return false;
            }

            // Validate checkpoint structure
            if (!$this->validateCheckpoint($data)) {
                $this->resetState();
                return false;
            }

            $this->state = $data;
            $this->state['updated_at'] = date('Y-m-d H:i:s');
            $this->resumedFromCheckpoint = ($data['status'] === 'running' || $data['status'] === 'paused');

            // Add run entry
            $this->state['runs'][] = [
                'started_at'      => date('Y-m-d H:i:s'),
                'processed_start' => $this->state['processed'],
                'resumed'         => $this->resumedFromCheckpoint,
            ];

            return true;
        } catch (\Exception $e) {
            $this->resetState();
            return false;
        }
    }

    /**
     * Validate checkpoint data structure
     *
     * @param array $data Checkpoint data
     * @return bool
     */
    private function validateCheckpoint(array $data): bool
    {
        $requiredKeys = ['version', 'status', 'processed', 'target'];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $data)) {
                return false;
            }
        }

        // Don't resume completed simulations
        if ($data['status'] === 'completed') {
            return false;
        }

        return true;
    }

    /**
     * Initialize checkpoint for a new simulation
     *
     * @param SimulatorConfig $config Simulation configuration
     * @return self
     */
    public function initialize(SimulatorConfig $config): self
    {
        // If resuming, verify config compatibility
        if ($this->resumedFromCheckpoint && $this->state['config_hash']) {
            $newHash = $this->generateConfigHash($config);
            if ($this->state['config_hash'] !== $newHash) {
                // Config changed, cannot resume safely
                $this->resetState();
                $this->resumedFromCheckpoint = false;
            }
        }

        // Set target and config hash
        $this->state['target'] = $config->targetRecords;
        $this->state['config_hash'] = $this->generateConfigHash($config);

        if (!$this->resumedFromCheckpoint) {
            $this->state['runs'][] = [
                'started_at'      => date('Y-m-d H:i:s'),
                'processed_start' => 0,
                'resumed'         => false,
            ];
        }

        $this->save();
        return $this;
    }

    /**
     * Generate hash of configuration for change detection
     *
     * @param SimulatorConfig $config
     * @return string
     */
    private function generateConfigHash(SimulatorConfig $config): string
    {
        // Hash key configuration parameters
        $configData = [
            'target'         => $config->targetRecords,
            'invalid_ratio'  => $config->invalidDataRatio,
            'attack_ratio'   => $config->attackPayloadRatio,
            'date_from'      => $config->dateFrom,
            'date_to'        => $config->dateTo,
            'logged_in'      => $config->loggedInRatio,
        ];

        return md5(json_encode($configData));
    }

    /**
     * Record a processed request
     *
     * @param string $status 'success', 'failed', or 'rejected'
     * @param string|null $dataType 'normal', 'invalid', or 'attack'
     */
    public function recordProcessed(string $status, ?string $dataType = null): void
    {
        $this->state['processed']++;

        switch ($status) {
            case 'success':
                $this->state['successful']++;
                break;
            case 'failed':
                $this->state['failed']++;
                break;
            case 'rejected':
                $this->state['rejected']++;
                break;
        }

        if ($dataType === 'invalid') {
            $this->state['invalid_data_count']++;
        } elseif ($dataType === 'attack') {
            $this->state['attack_data_count']++;
        }

        $this->state['last_timestamp'] = date('Y-m-d H:i:s');
        $this->state['elapsed_seconds'] = microtime(true) - $this->runStartTime + ($this->state['elapsed_seconds'] ?? 0);

        // Auto-save at intervals
        $this->recordsSinceLastSave++;
        if ($this->recordsSinceLastSave >= $this->saveInterval) {
            $this->save();
        }
    }

    /**
     * Update seed offset for generator resumption
     *
     * @param int $offset New seed offset
     */
    public function updateSeedOffset(int $offset): void
    {
        $this->state['seed_offset'] = $offset;
    }

    /**
     * Mark simulation as completed
     */
    public function markComplete(): void
    {
        $this->state['status'] = 'completed';
        $this->state['completed_at'] = date('Y-m-d H:i:s');

        // Update last run
        $runIndex = count($this->state['runs']) - 1;
        if ($runIndex >= 0) {
            $this->state['runs'][$runIndex]['completed_at'] = date('Y-m-d H:i:s');
            $this->state['runs'][$runIndex]['processed_end'] = $this->state['processed'];
        }

        $this->save();
    }

    /**
     * Mark simulation as failed
     *
     * @param string $error Error message
     */
    public function markFailed(string $error): void
    {
        $this->state['status'] = 'failed';
        $this->state['error'] = $error;
        $this->state['failed_at'] = date('Y-m-d H:i:s');

        // Update last run
        $runIndex = count($this->state['runs']) - 1;
        if ($runIndex >= 0) {
            $this->state['runs'][$runIndex]['failed_at'] = date('Y-m-d H:i:s');
            $this->state['runs'][$runIndex]['error'] = $error;
            $this->state['runs'][$runIndex]['processed_end'] = $this->state['processed'];
        }

        $this->save();
    }

    /**
     * Pause simulation (save state for later resume)
     */
    public function pause(): void
    {
        $this->state['status'] = 'paused';
        $this->state['paused_at'] = date('Y-m-d H:i:s');

        // Update last run
        $runIndex = count($this->state['runs']) - 1;
        if ($runIndex >= 0) {
            $this->state['runs'][$runIndex]['paused_at'] = date('Y-m-d H:i:s');
            $this->state['runs'][$runIndex]['processed_end'] = $this->state['processed'];
        }

        $this->save();
    }

    /**
     * Save checkpoint to file
     *
     * @return bool
     */
    public function save(): bool
    {
        $this->state['updated_at'] = date('Y-m-d H:i:s');
        $this->recordsSinceLastSave = 0;

        $json = json_encode($this->state, JSON_PRETTY_PRINT);
        if ($json === false) {
            return false;
        }

        // Write to temp file first, then rename for atomicity
        $tempFile = $this->checkpointFile . '.tmp';
        if (file_put_contents($tempFile, $json) === false) {
            return false;
        }

        return rename($tempFile, $this->checkpointFile);
    }

    /**
     * Delete checkpoint file
     *
     * @return bool
     */
    public function delete(): bool
    {
        if (file_exists($this->checkpointFile)) {
            return unlink($this->checkpointFile);
        }
        return true;
    }

    /**
     * Check if resuming from a checkpoint
     *
     * @return bool
     */
    public function isResuming(): bool
    {
        return $this->resumedFromCheckpoint;
    }

    /**
     * Get number of records already processed (for resume)
     *
     * @return int
     */
    public function getProcessedCount(): int
    {
        return $this->state['processed'];
    }

    /**
     * Get seed offset for generator resumption
     *
     * @return int
     */
    public function getSeedOffset(): int
    {
        return $this->state['seed_offset'] ?? 0;
    }

    /**
     * Get remaining records to process
     *
     * @return int
     */
    public function getRemainingCount(): int
    {
        return max(0, $this->state['target'] - $this->state['processed']);
    }

    /**
     * Get progress percentage
     *
     * @return float
     */
    public function getProgressPercent(): float
    {
        if ($this->state['target'] <= 0) {
            return 0;
        }
        return min(100, round(($this->state['processed'] / $this->state['target']) * 100, 2));
    }

    /**
     * Get estimated time remaining in seconds
     *
     * @return float|null Null if not enough data to estimate
     */
    public function getEstimatedTimeRemaining(): ?float
    {
        if ($this->state['processed'] < 100 || $this->state['elapsed_seconds'] < 1) {
            return null;
        }

        $rate = $this->state['processed'] / $this->state['elapsed_seconds'];
        $remaining = $this->getRemainingCount();

        return $remaining / $rate;
    }

    /**
     * Get current state
     *
     * @return array
     */
    public function getState(): array
    {
        return $this->state;
    }

    /**
     * Get summary statistics
     *
     * @return array
     */
    public function getSummary(): array
    {
        $elapsed = $this->state['elapsed_seconds'] ?? 0;
        $rate = $elapsed > 0 ? $this->state['processed'] / $elapsed : 0;

        return [
            'status'           => $this->state['status'],
            'processed'        => $this->state['processed'],
            'target'           => $this->state['target'],
            'remaining'        => $this->getRemainingCount(),
            'progress_percent' => $this->getProgressPercent(),
            'successful'       => $this->state['successful'],
            'failed'           => $this->state['failed'],
            'rejected'         => $this->state['rejected'],
            'invalid_data'     => $this->state['invalid_data_count'],
            'attack_data'      => $this->state['attack_data_count'],
            'elapsed_seconds'  => round($elapsed, 2),
            'rate_per_second'  => round($rate, 2),
            'eta_seconds'      => $this->getEstimatedTimeRemaining(),
            'runs_count'       => count($this->state['runs']),
            'is_resuming'      => $this->resumedFromCheckpoint,
        ];
    }

    /**
     * Get checkpoint file path
     *
     * @return string
     */
    public function getCheckpointFile(): string
    {
        return $this->checkpointFile;
    }

    /**
     * Check if a checkpoint file exists
     *
     * @param string $checkpointDir Checkpoint directory
     * @param string $identifier Simulation identifier
     * @return bool
     */
    public static function checkpointExists(string $checkpointDir, string $identifier): bool
    {
        $safeId = preg_replace('/[^a-zA-Z0-9_-]/', '_', $identifier);
        $file = rtrim($checkpointDir, '/') . '/checkpoint_' . $safeId . '.json';
        return file_exists($file);
    }

    /**
     * List all checkpoints in directory
     *
     * @param string $checkpointDir Checkpoint directory
     * @return array Array of checkpoint info
     */
    public static function listCheckpoints(string $checkpointDir): array
    {
        $checkpoints = [];
        $pattern = rtrim($checkpointDir, '/') . '/checkpoint_*.json';

        foreach (glob($pattern) as $file) {
            $contents = file_get_contents($file);
            if ($contents === false) {
                continue;
            }

            $data = json_decode($contents, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                continue;
            }

            $checkpoints[] = [
                'file'      => $file,
                'identifier' => $data['identifier'] ?? basename($file, '.json'),
                'status'    => $data['status'] ?? 'unknown',
                'processed' => $data['processed'] ?? 0,
                'target'    => $data['target'] ?? 0,
                'updated_at' => $data['updated_at'] ?? null,
            ];
        }

        return $checkpoints;
    }

    /**
     * Clean up old completed checkpoints
     *
     * @param string $checkpointDir Checkpoint directory
     * @param int $maxAge Maximum age in seconds (default: 7 days)
     * @return int Number of deleted files
     */
    public static function cleanupOldCheckpoints(string $checkpointDir, int $maxAge = 604800): int
    {
        $deleted = 0;
        $cutoff = time() - $maxAge;
        $pattern = rtrim($checkpointDir, '/') . '/checkpoint_*.json';

        foreach (glob($pattern) as $file) {
            $mtime = filemtime($file);
            if ($mtime === false || $mtime > $cutoff) {
                continue;
            }

            // Only delete completed checkpoints
            $contents = file_get_contents($file);
            if ($contents === false) {
                continue;
            }

            $data = json_decode($contents, true);
            if ($data && ($data['status'] ?? '') === 'completed') {
                if (unlink($file)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }
}
