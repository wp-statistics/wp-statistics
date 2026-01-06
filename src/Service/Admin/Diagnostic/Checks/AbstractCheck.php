<?php

namespace WP_Statistics\Service\Admin\Diagnostic\Checks;

use WP_Statistics\Service\Admin\Diagnostic\DiagnosticResult;

/**
 * Abstract base class for diagnostic checks.
 *
 * Provides common functionality and helper methods for implementing checks.
 *
 * @since 15.0.0
 */
abstract class AbstractCheck implements CheckInterface
{
    /**
     * Create a passing result for this check.
     *
     * @param string $message Success message.
     * @param array  $details Additional details.
     * @return DiagnosticResult
     */
    protected function pass(string $message, array $details = []): DiagnosticResult
    {
        return DiagnosticResult::pass(
            $this->getKey(),
            $this->getLabel(),
            $message,
            $details,
            $this->getHelpUrl()
        );
    }

    /**
     * Create a warning result for this check.
     *
     * @param string $message Warning message.
     * @param array  $details Additional details.
     * @return DiagnosticResult
     */
    protected function warning(string $message, array $details = []): DiagnosticResult
    {
        return DiagnosticResult::warning(
            $this->getKey(),
            $this->getLabel(),
            $message,
            $details,
            $this->getHelpUrl()
        );
    }

    /**
     * Create a failing result for this check.
     *
     * @param string $message Failure message.
     * @param array  $details Additional details.
     * @return DiagnosticResult
     */
    protected function fail(string $message, array $details = []): DiagnosticResult
    {
        return DiagnosticResult::fail(
            $this->getKey(),
            $this->getLabel(),
            $message,
            $details,
            $this->getHelpUrl()
        );
    }

    /**
     * By default, checks are not lightweight (assume heavy).
     * Override in subclasses that are lightweight.
     *
     * @return bool
     */
    public function isLightweight(): bool
    {
        return false;
    }

    /**
     * By default, no help URL.
     * Override in subclasses to provide help URLs.
     *
     * @return string|null
     */
    public function getHelpUrl(): ?string
    {
        return null;
    }
}
