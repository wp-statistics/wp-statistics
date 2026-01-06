<?php

namespace WP_Statistics\Service\Shortcode;

use WP_Statistics\Service\Shortcode\Contracts\StatHandlerInterface;

/**
 * Registry for stat handlers.
 *
 * Manages registration and lookup of stat handlers.
 * Provides a centralized way to resolve which handler
 * should process a given stat type.
 *
 * @since 15.0.0
 */
class StatRegistry
{
    /**
     * Registered handlers.
     *
     * @var StatHandlerInterface[]
     */
    private $handlers = [];

    /**
     * Stat-to-handler mapping cache.
     *
     * @var array<string, StatHandlerInterface>
     */
    private $statMap = [];

    /**
     * Register a stat handler.
     *
     * @param StatHandlerInterface $handler Handler instance.
     * @return self
     */
    public function register(StatHandlerInterface $handler): self
    {
        $this->handlers[] = $handler;

        // Build stat map for fast lookup
        foreach ($handler->getSupportedStats() as $stat) {
            $this->statMap[$stat] = $handler;
        }

        return $this;
    }

    /**
     * Check if a stat type is supported.
     *
     * @param string $stat Stat type identifier.
     * @return bool
     */
    public function has(string $stat): bool
    {
        return isset($this->statMap[$stat]);
    }

    /**
     * Get the handler for a stat type.
     *
     * @param string $stat Stat type identifier.
     * @return StatHandlerInterface|null
     */
    public function getHandler(string $stat): ?StatHandlerInterface
    {
        return $this->statMap[$stat] ?? null;
    }

    /**
     * Resolve a stat value using the appropriate handler.
     *
     * @param string $stat Stat type identifier.
     * @param array  $args Additional arguments.
     * @return mixed The stat value or empty string if not found.
     */
    public function resolve(string $stat, array $args = [])
    {
        $handler = $this->getHandler($stat);

        if ($handler === null) {
            /**
             * Filter for custom stat types not handled by registered handlers.
             *
             * @since 15.0.0
             * @param mixed $value Default value.
             * @param string $stat Stat type.
             * @param array $args Arguments.
             */
            return apply_filters('wp_statistics_shortcode_custom_stat', '', $stat, $args);
        }

        return $handler->handle($stat, $args);
    }

    /**
     * Get all supported stat types.
     *
     * @return array List of all supported stat types.
     */
    public function getSupportedStats(): array
    {
        return array_keys($this->statMap);
    }

    /**
     * Get all registered handlers.
     *
     * @return StatHandlerInterface[]
     */
    public function getHandlers(): array
    {
        return $this->handlers;
    }
}
