<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Country ID filter - filters by country ID.
 *
 * @since 15.0.0
 */
class CountryIdFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[country_id]=... */
    protected $name = 'country_id';

    /** @var string SQL column: foreign key ID to countries table (for programmatic filtering) */
    protected $column = 'sessions.country_id';

    /** @var string Data type: integer for database ID matching */
    protected $type = 'integer';

    /** @var array Supported operators: exact match, exclusion, set membership, and numeric comparisons */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'gt', 'gte', 'lt', 'lte'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Country ID', 'wp-statistics');
    }
}
