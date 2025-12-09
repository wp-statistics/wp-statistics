<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Resolution ID filter - filters by resolution ID.
 *
 * @since 15.0.0
 */
class ResolutionIdFilter extends AbstractFilter
{
    protected $name               = 'resolution_id';
    protected $column             = 'sessions.resolution_id';
    protected $type               = 'integer';
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Resolution ID', 'wp-statistics');
    }
}
