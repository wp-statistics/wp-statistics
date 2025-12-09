<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Timezone ID filter - filters by timezone ID.
 *
 * @since 15.0.0
 */
class TimezoneIdFilter extends AbstractFilter
{
    protected $name               = 'timezone_id';
    protected $column             = 'sessions.timezone_id';
    protected $type               = 'integer';
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Timezone ID', 'wp-statistics');
    }
}
