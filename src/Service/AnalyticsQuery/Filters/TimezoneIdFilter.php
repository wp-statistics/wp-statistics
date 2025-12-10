<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Timezone ID filter - filters by timezone ID.
 *
 * @since 15.0.0
 */
class TimezoneIdFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[timezone_id]=... */
    protected $name = 'timezone_id';

    /** @var string SQL column: foreign key ID to timezones table (for programmatic filtering) */
    protected $column = 'sessions.timezone_id';

    /** @var string Data type: integer for database ID matching */
    protected $type = 'integer';

    /** @var array Supported operators: exact match, exclusion, and set membership */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Timezone ID', 'wp-statistics');
    }
}
