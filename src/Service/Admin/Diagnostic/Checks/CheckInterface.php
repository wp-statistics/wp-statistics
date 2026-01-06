<?php

namespace WP_Statistics\Service\Admin\Diagnostic\Checks;

use WP_Statistics\Service\Admin\Diagnostic\DiagnosticResult;

/**
 * Interface for diagnostic checks.
 *
 * Implement this interface to create custom diagnostic checks
 * that can be run by the DiagnosticManager.
 *
 * @since 15.0.0
 */
interface CheckInterface
{
    /**
     * Get the unique key for this check.
     *
     * @return string
     */
    public function getKey(): string;

    /**
     * Get the human-readable label for this check.
     *
     * @return string
     */
    public function getLabel(): string;

    /**
     * Get a description of what this check verifies.
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Run the diagnostic check.
     *
     * @return DiagnosticResult
     */
    public function run(): DiagnosticResult;

    /**
     * Get the help URL for when this check fails.
     *
     * @return string|null
     */
    public function getHelpUrl(): ?string;

    /**
     * Check if this is a lightweight (fast) check.
     *
     * Lightweight checks can be run on every page load.
     * Heavy checks (like HTTP requests) should return false.
     *
     * @return bool
     */
    public function isLightweight(): bool;
}
