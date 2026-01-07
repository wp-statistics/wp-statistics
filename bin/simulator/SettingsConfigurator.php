<?php

namespace WP_Statistics\Testing\Simulator;

use WP_Statistics\Components\Option;

/**
 * SettingsConfigurator - Auto-configure required WP Statistics settings for simulation
 *
 * The HTTP simulator requires specific settings to be enabled:
 * - bypass_ad_blockers: Use admin-ajax.php endpoint
 * - use_cache_plugin: Enable client-side tracking
 *
 * @package WP_Statistics\Testing\Simulator
 * @since 15.0.0
 */
class SettingsConfigurator
{
    /**
     * Required settings for the simulator to work
     */
    private const REQUIRED_SETTINGS = [
        'bypass_ad_blockers' => '1',  // Use admin-ajax.php endpoint
        'use_cache_plugin'   => '1',  // Enable client-side tracking
    ];

    /**
     * Logger callback
     * @var callable|null
     */
    private $logger;

    /**
     * Whether settings were modified
     * @var bool
     */
    private bool $modified = false;

    /**
     * Original settings before modification (for restoration)
     * @var array
     */
    private array $originalSettings = [];

    /**
     * Constructor
     *
     * @param callable|null $logger Optional logger callback
     */
    public function __construct(?callable $logger = null)
    {
        $this->logger = $logger;
    }

    /**
     * Log a message
     *
     * @param string $message
     */
    private function log(string $message): void
    {
        if ($this->logger) {
            call_user_func($this->logger, $message);
        }
    }

    /**
     * Check if all required settings are configured correctly
     *
     * @return bool True if all settings are valid
     */
    public function validateSettings(): bool
    {
        foreach (self::REQUIRED_SETTINGS as $key => $expectedValue) {
            $currentValue = Option::getValue($key);
            if ($currentValue != $expectedValue) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the current status of required settings
     *
     * @return array Associative array with setting status
     */
    public function getSettingsStatus(): array
    {
        $status = [];

        foreach (self::REQUIRED_SETTINGS as $key => $expectedValue) {
            $currentValue = Option::getValue($key);
            $status[$key] = [
                'current'  => $currentValue,
                'expected' => $expectedValue,
                'valid'    => $currentValue == $expectedValue,
            ];
        }

        return $status;
    }

    /**
     * Ensure all required settings are configured
     *
     * @param bool $autoFix Whether to automatically fix invalid settings
     * @return bool True if settings are valid (after potential auto-fix)
     */
    public function ensureSettings(bool $autoFix = true): bool
    {
        $settings = get_option('wp_statistics_settings', []);
        $needsUpdate = false;

        foreach (self::REQUIRED_SETTINGS as $key => $expectedValue) {
            $currentValue = $settings[$key] ?? null;

            if ($currentValue != $expectedValue) {
                if ($autoFix) {
                    // Store original value for potential restoration
                    $this->originalSettings[$key] = $currentValue;
                    $settings[$key] = $expectedValue;
                    $needsUpdate = true;
                    $this->log("[Setup] {$key}: {$currentValue} -> {$expectedValue}");
                } else {
                    $this->log("[Warning] {$key} is not configured correctly. Expected: {$expectedValue}, Current: {$currentValue}");
                }
            } else {
                $this->log("[Setup] {$key}: enabled");
            }
        }

        if ($needsUpdate) {
            update_option('wp_statistics_settings', $settings);
            $this->modified = true;
            $this->log("[Setup] Required WP Statistics settings configured.");
        }

        return $this->validateSettings();
    }

    /**
     * Restore original settings (if they were modified)
     *
     * @return bool True if restoration was performed
     */
    public function restoreSettings(): bool
    {
        if (!$this->modified || empty($this->originalSettings)) {
            return false;
        }

        $settings = get_option('wp_statistics_settings', []);

        foreach ($this->originalSettings as $key => $originalValue) {
            if ($originalValue === null) {
                unset($settings[$key]);
            } else {
                $settings[$key] = $originalValue;
            }
            $this->log("[Restore] {$key}: {$originalValue}");
        }

        update_option('wp_statistics_settings', $settings);
        $this->modified = false;
        $this->originalSettings = [];
        $this->log("[Restore] Original settings restored.");

        return true;
    }

    /**
     * Check if settings were modified
     *
     * @return bool
     */
    public function wasModified(): bool
    {
        return $this->modified;
    }

    /**
     * Get list of required settings
     *
     * @return array
     */
    public static function getRequiredSettings(): array
    {
        return self::REQUIRED_SETTINGS;
    }

    /**
     * Print settings status to console
     *
     * @return void
     */
    public function printStatus(): void
    {
        echo "[Setup] Checking WP Statistics settings...\n";

        foreach ($this->getSettingsStatus() as $key => $info) {
            $status = $info['valid'] ? 'enabled' : "INVALID (expected: {$info['expected']}, got: {$info['current']})";
            echo "[Setup] {$key}: {$status}\n";
        }
    }
}
