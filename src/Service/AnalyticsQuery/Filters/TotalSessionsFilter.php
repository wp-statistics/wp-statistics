<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Total sessions filter - filters by total sessions.
 *
 * @since 15.0.0
 */
class TotalSessionsFilter extends AbstractFilter
{
    protected $name               = 'total_sessions';
    protected $column             = 'visitors.sessions_count';
    protected $type               = 'integer';
    protected $inputType          = 'number';
    protected $supportedOperators = ['gt', 'lt', 'between'];
    protected $pages              = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Total Sessions', 'wp-statistics');
    }
}
