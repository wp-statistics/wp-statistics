<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Views per session filter - filters by pages viewed per session.
 *
 * @since 15.0.0
 */
class ViewsPerSessionFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[views_per_session]=... */
    protected $name = 'views_per_session';

    /** @var string SQL column: page count per session from sessions table */
    protected $column = 'sessions.pages_count';

    /** @var string Data type: integer for page count comparisons */
    protected $type = 'integer';

    /** @var string UI component: number input for views count entry */
    protected $inputType = 'number';

    /** @var array Supported operators: exact match, greater than, and less than */
    protected $supportedOperators = ['is', 'gt', 'lt'];

    /** @var array Available on: visitors page for engagement analysis */
    protected $groups = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Views per Session', 'wp-statistics');
    }
}
