<?php

namespace WP_Statistics\Service\AnalyticsQuery\Formatters;

use WP_Statistics\Service\AnalyticsQuery\Contracts\FormatterInterface;
use WP_Statistics\Service\AnalyticsQuery\Query\Query;
use WP_Statistics\Service\AnalyticsQuery\Cache\CacheManager;

/**
 * Abstract base class for response formatters.
 *
 * Provides common functionality for all formatters including
 * metadata generation and helper methods.
 *
 * @since 15.0.0
 */
abstract class AbstractFormatter implements FormatterInterface
{
    /**
     * Cache manager for TTL information.
     *
     * @var CacheManager|null
     */
    protected $cacheManager;

    /**
     * Constructor.
     *
     * @param CacheManager|null $cacheManager Cache manager instance.
     */
    public function __construct(?CacheManager $cacheManager = null)
    {
        $this->cacheManager = $cacheManager;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $format): bool
    {
        return $format === $this->getName();
    }

    /**
     * Build base metadata for the response.
     *
     * @param Query $query The query object.
     * @return array Base metadata.
     */
    protected function buildBaseMeta(Query $query): array
    {
        $meta = [
            'date_from' => $query->getDateFrom(),
            'date_to'   => $query->getDateTo(),
        ];

        if ($this->cacheManager) {
            $meta['cached']    = false;
            $meta['cache_ttl'] = $this->cacheManager->getTTL($query->toArray());
        }

        return $meta;
    }

    /**
     * Calculate change percentage between current and previous values.
     *
     * @param float $current  Current value.
     * @param float $previous Previous value.
     * @return float|null Change percentage, or null if previous is zero.
     */
    protected function calculateChange(float $current, float $previous): ?float
    {
        if ($previous == 0) {
            return $current > 0 ? 100.0 : null;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }

    /**
     * Format a change percentage as a string with sign.
     *
     * @param float|null $change Change percentage.
     * @return string Formatted change string (e.g., "+17.6%", "-5.2%").
     */
    protected function formatChangeString(?float $change): string
    {
        if ($change === null) {
            return 'N/A';
        }

        $sign = $change >= 0 ? '+' : '';
        return $sign . $change . '%';
    }

    /**
     * Get the group by alias from the registry.
     *
     * @param string $groupByName Group by name.
     * @return string The alias or the original name if not found.
     */
    protected function getGroupByAlias(string $groupByName): string
    {
        $registry   = \WP_Statistics\Service\AnalyticsQuery\Registry\GroupByRegistry::getInstance();
        $groupByObj = $registry->get($groupByName);

        return $groupByObj ? $groupByObj->getAlias() : $groupByName;
    }

    /**
     * Get human-readable label for a group by field.
     *
     * @param string $groupByName Group by name.
     * @return string Human-readable label.
     */
    protected function getGroupByLabel(string $groupByName): string
    {
        $labels = [
            'date'        => __('Date', 'wp-statistics'),
            'month'       => __('Month', 'wp-statistics'),
            'week'        => __('Week', 'wp-statistics'),
            'hour'        => __('Hour', 'wp-statistics'),
            'country'     => __('Country', 'wp-statistics'),
            'city'        => __('City', 'wp-statistics'),
            'continent'   => __('Continent', 'wp-statistics'),
            'browser'     => __('Browser', 'wp-statistics'),
            'os'          => __('Operating System', 'wp-statistics'),
            'device_type' => __('Device Type', 'wp-statistics'),
            'resolution'  => __('Resolution', 'wp-statistics'),
            'referrer'    => __('Referrer', 'wp-statistics'),
            'page'        => __('Page', 'wp-statistics'),
            'language'    => __('Language', 'wp-statistics'),
            'visitor'     => __('Visitor', 'wp-statistics'),
        ];

        return $labels[$groupByName] ?? ucfirst(str_replace('_', ' ', $groupByName));
    }

    /**
     * Get human-readable label for a source/metric.
     *
     * @param string $sourceName Source name.
     * @return string Human-readable label.
     */
    protected function getSourceLabel(string $sourceName): string
    {
        $labels = [
            'visitors'             => __('Visitors', 'wp-statistics'),
            'views'                => __('Views', 'wp-statistics'),
            'sessions'             => __('Sessions', 'wp-statistics'),
            'bounce_rate'          => __('Bounce Rate', 'wp-statistics'),
            'avg_session_duration' => __('Avg. Session Duration', 'wp-statistics'),
            'avg_time_on_page'     => __('Avg. Time on Page', 'wp-statistics'),
            'pages_per_session'    => __('Pages per Session', 'wp-statistics'),
            'visitor_status'       => __('Visitor Status', 'wp-statistics'),
            'total_duration'       => __('Total Duration', 'wp-statistics'),
        ];

        return $labels[$sourceName] ?? ucfirst(str_replace('_', ' ', $sourceName));
    }
}
