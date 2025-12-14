<?php

namespace WP_Statistics\Service\AnalyticsQuery\Formatters;

use WP_Statistics\Service\AnalyticsQuery\Query\Query;

/**
 * Flat response formatter.
 *
 * Produces a flattened structure optimized for easy iteration.
 * Use cases: Simple data processing, lightweight widgets, mobile apps.
 *
 * Output structure:
 * {
 *   "success": true,
 *   "items": [...],
 *   "totals": {...},
 *   "meta": {...}
 * }
 *
 * @since 15.0.0
 */
class FlatFormatter extends AbstractFormatter
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'flat';
    }

    /**
     * {@inheritdoc}
     */
    public function format(Query $query, array $result): array
    {
        $rows       = $result['rows'] ?? [];
        $showTotals = $query->showTotals();

        $response = [
            'success' => true,
            'items'   => $rows,
            'meta'    => $this->buildBaseMeta($query),
        ];

        // Add totals if requested
        if ($showTotals && isset($result['totals']) && $result['totals'] !== null) {
            $response['totals'] = $result['totals'];
        }

        // Add comparison info if present
        if (isset($result['compare_from'])) {
            $response['meta']['compare_from'] = $result['compare_from'];
            $response['meta']['compare_to']   = $result['compare_to'];
        }

        return $response;
    }
}
