<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer name filter - filters by referrer name (e.g., Google, Facebook).
 *
 * @since 15.0.0
 */
class ReferrerNameFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[referrer_name]=... */
    protected $name = 'referrer_name';

    /** @var string SQL column: referrer display name from referrers table (e.g., Google, Facebook, Twitter) */
    protected $column = 'referrers.name';

    /** @var string Data type: string for referrer name matching */
    protected $type = 'string';

    /**
     * Required JOIN: sessions -> referrers.
     * Links session's referrer ID to the referrer name lookup table.
     *
     * @var array
     */
    protected $joins = [
        'table' => 'referrers',
        'alias' => 'referrers',
        'on'    => 'sessions.referrer_id = referrers.ID',
    ];

    /** @var array Supported operators: exact match, exclusion, set membership, and partial text matching */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in', 'contains', 'starts_with', 'ends_with'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Referrer Name', 'wp-statistics');
    }
}
