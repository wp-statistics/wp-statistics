<?php

namespace WP_Statistics\Service\Shortcode\Contracts;

/**
 * Interface for statistic handlers.
 *
 * Each handler is responsible for a specific category of statistics
 * (e.g., analytics stats, WordPress stats).
 *
 * @since 15.0.0
 */
interface StatHandlerInterface
{
    /**
     * Get the list of stat types this handler supports.
     *
     * @return array List of stat type identifiers.
     */
    public function getSupportedStats(): array;

    /**
     * Check if this handler supports the given stat type.
     *
     * @param string $stat Stat type identifier.
     * @return bool True if supported.
     */
    public function supports(string $stat): bool;

    /**
     * Get the value for a given stat type.
     *
     * @param string $stat Stat type identifier.
     * @param array  $args Additional arguments (time, id, type, provider, etc.).
     * @return mixed The stat value.
     */
    public function handle(string $stat, array $args = []);
}
