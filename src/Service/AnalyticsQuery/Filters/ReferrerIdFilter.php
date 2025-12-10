<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer ID filter - filters by referrer ID.
 *
 * @since 15.0.0
 */
class ReferrerIdFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[referrer_id]=... */
    protected $name = 'referrer_id';

    /** @var string SQL column: foreign key ID to referrers table (for programmatic filtering) */
    protected $column = 'sessions.referrer_id';

    /** @var string Data type: integer for database ID matching */
    protected $type = 'integer';

    /** @var array Supported operators: exact match, exclusion, and set membership */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Referrer ID', 'wp-statistics');
    }
}
