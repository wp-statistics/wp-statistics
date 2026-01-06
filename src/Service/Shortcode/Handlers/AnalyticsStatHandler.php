<?php

namespace WP_Statistics\Service\Shortcode\Handlers;

use WP_Statistics\Components\DateRange;
use WP_Statistics\Service\Resources\ResourcesFactory;
use WP_Statistics\Service\Shortcode\Contracts\StatHandlerInterface;

/**
 * Handler for analytics-based statistics.
 *
 * Handles stats that come from the WP Statistics tracking system:
 * visits, visitors, page views, searches, referrers, online users.
 *
 * @since 15.0.0
 */
class AnalyticsStatHandler implements StatHandlerInterface
{
    /**
     * Supported stat types.
     *
     * @var array
     */
    private const SUPPORTED_STATS = [
        'usersonline',
        'visits',
        'visitors',
        'pagevisits',
        'pagevisitors',
        'searches',
        'referrer',
    ];

    /**
     * {@inheritdoc}
     */
    public function getSupportedStats(): array
    {
        return self::SUPPORTED_STATS;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $stat): bool
    {
        return in_array($stat, self::SUPPORTED_STATS, true);
    }

    /**
     * {@inheritdoc}
     */
    public function handle(string $stat, array $args = [])
    {
        $time = $args['time'] ?? null;

        switch ($stat) {
            case 'usersonline':
                return $this->getOnlineUsers();

            case 'visits':
                return $this->getVisits($time);

            case 'visitors':
                return $this->getVisitors($time);

            case 'pagevisits':
                return $this->getPageVisits($args);

            case 'pagevisitors':
                return $this->getPageVisitors($args);

            case 'searches':
                return $this->getSearches($args);

            case 'referrer':
                return $this->getReferrers($time);

            default:
                return 0;
        }
    }

    /**
     * Get online users count.
     *
     * @return int
     */
    private function getOnlineUsers(): int
    {
        return (int) wp_statistics_useronline([], 'total_rows');
    }

    /**
     * Get total visits (page views).
     *
     * @param mixed $time Time period.
     * @return int
     */
    private function getVisits($time): int
    {
        $dateRange = $this->resolveDateRange($time);

        $totals = wp_statistics_query([
            'sources'   => ['views'],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'format'    => 'flat',
        ], 'totals');

        return (int) ($totals['views'] ?? 0);
    }

    /**
     * Get unique visitors count.
     *
     * @param mixed $time Time period.
     * @return int
     */
    private function getVisitors($time): int
    {
        $dateRange = $this->resolveDateRange($time);

        $totals = wp_statistics_query([
            'sources'   => ['visitors'],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'format'    => 'flat',
        ], 'totals');

        return (int) ($totals['visitors'] ?? 0);
    }

    /**
     * Get page-specific views.
     *
     * @param array $args Arguments with resource_id, resource_type, time.
     * @return int
     */
    private function getPageVisits(array $args): int
    {
        $dateRange = $this->resolveDateRange($args['time'] ?? null);
        $filters   = $this->buildResourceFilters($args);

        $totals = wp_statistics_query([
            'sources'   => ['views'],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'filters'   => $filters,
            'format'    => 'flat',
        ], 'totals');

        return (int) ($totals['views'] ?? 0);
    }

    /**
     * Get page-specific visitors.
     *
     * @param array $args Arguments with resource_id, resource_type, time.
     * @return int
     */
    private function getPageVisitors(array $args): int
    {
        $dateRange = $this->resolveDateRange($args['time'] ?? null);
        $filters   = $this->buildResourceFilters($args);

        $totals = wp_statistics_query([
            'sources'   => ['visitors'],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'filters'   => $filters,
            'format'    => 'flat',
        ], 'totals');

        return (int) ($totals['visitors'] ?? 0);
    }

    /**
     * Get search engine traffic.
     *
     * @param array $args Arguments with provider, time.
     * @return int
     */
    private function getSearches(array $args): int
    {
        $provider  = $args['provider'] ?? 'all';
        $time      = $args['time'] ?? null;
        $dateRange = $this->resolveDateRange($time);

        $filters = [
            'referrer_channel' => 'search'
        ];

        if (strtolower($provider) !== 'all') {
            $filters['referrer_name'] = $provider;
        }

        $totals = wp_statistics_query([
            'sources'   => ['visitors'],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'filters'   => $filters,
            'format'    => 'flat',
        ], 'totals');

        return (int) ($totals['visitors'] ?? 0);
    }

    /**
     * Get referrer count.
     *
     * @param mixed $time Time period.
     * @return int
     */
    private function getReferrers($time): int
    {
        return (int) wp_statistics_referrer($time, [], 'totals');
    }

    /**
     * Build resource filters for page-specific queries.
     *
     * Uses ResourcesFactory to look up the internal resource ID from the
     * WordPress post ID and type, since views.resource_id references
     * resources.ID (not the WP post ID).
     *
     * @param array $args Arguments with 'id' (WP post ID) and 'type' (post type).
     * @return array Filters array.
     */
    private function buildResourceFilters(array $args): array
    {
        $filters = [];

        if (!empty($args['id']) && !empty($args['type'])) {
            $resource = ResourcesFactory::getByResourceId((int) $args['id'], $args['type']);

            if ($resource && $resource->getId()) {
                $filters['resource_id'] = $resource->getId();
            }
        }

        return $filters;
    }

    /**
     * Resolve date range from time parameter.
     *
     * @param mixed $time Time input.
     * @return array Date range with 'from' and 'to' keys.
     */
    private function resolveDateRange($time): array
    {
        if (empty($time)) {
            return [
                'from' => '2000-01-01',
                'to'   => date('Y-m-d'),
            ];
        }

        if (is_array($time) && isset($time['from'], $time['to'])) {
            return $time;
        }

        if (is_string($time)) {
            return DateRange::resolveDate($time);
        }

        return [
            'from' => '2000-01-01',
            'to'   => date('Y-m-d'),
        ];
    }
}
