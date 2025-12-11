<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Views per session filter - filters by pages viewed per session.
 *
 * @since 15.0.0
 */
class ViewsPerSessionFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[views_per_session]=...
     */
    protected $name = 'views_per_session';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: sessions.pages_count
     */
    protected $column = 'sessions.pages_count';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer
     */
    protected $type = 'integer';

    /**
     * UI input component type.
     *
     * @var string Input type: number
     */
    protected $inputType = 'number';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is, gt, lt
     */
    protected $supportedOperators = ['is', 'gt', 'lt'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: visitors
     */
    protected $groups = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Views per Session', 'wp-statistics');
    }
}
