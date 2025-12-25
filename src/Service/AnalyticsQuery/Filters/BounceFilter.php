<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Bounce filter - filters by bounce status.
 *
 * A session is considered a "bounce" when total_views = 1.
 *
 * @since 15.0.0
 */
class BounceFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[bounce]=...
     */
    protected $name = 'bounce';

    /**
     * SQL column for WHERE clause.
     *
     * This is overridden by getColumn() to use dynamic expression.
     *
     * @var string Column path: expression for bounce check
     */
    protected $column = '';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: integer (1=bounced, 0=engaged)
     */
    protected $type = 'integer';

    /**
     * UI input component type.
     *
     * @var string Input type: dropdown with Yes/No options
     */
    protected $inputType = 'dropdown';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is
     */
    protected $supportedOperators = ['is'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: visitors
     */
    protected $groups = ['visitors'];

    /**
     * Required base table to enable this filter.
     *
     * @var string|null Table name: sessions
     */
    protected $requirement = 'sessions';

    /**
     * Get the SQL column for WHERE clause.
     *
     * A session is a bounce when total_views = 1.
     * Returns 1 if bounce (total_views = 1), 0 if not bounce.
     *
     * @return string
     */
    public function getColumn(): string
    {
        // Returns 1 if session has only 1 view (bounce), 0 otherwise
        return '(sessions.total_views = 1)';
    }

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Bounce', 'wp-statistics');
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): ?array
    {
        return [
            ['value' => '1', 'label' => esc_html__('Yes', 'wp-statistics')],
            ['value' => '0', 'label' => esc_html__('No', 'wp-statistics')],
        ];
    }
}
