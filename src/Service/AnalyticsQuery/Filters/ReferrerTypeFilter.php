<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer type filter - filters by referrer channel/type.
 *
 * @since 15.0.0
 */
class ReferrerTypeFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[referrer_type]=...
     */
    protected $name = 'referrer_type';

    /**
     * SQL column for WHERE clause.
     *
     * @var string Column path: referrers.channel
     */
    protected $column = 'referrers.channel';

    /**
     * Value type for sanitization.
     *
     * @var string Data type: string
     */
    protected $type = 'string';

    /**
     * Required JOINs to access the column.
     *
     * @var array JOIN: sessions -> referrers
     */
    protected $joins = [
        'table' => 'referrers',
        'alias' => 'referrers',
        'on'    => 'sessions.referrer_id = referrers.ID',
    ];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Referrer Type', 'wp-statistics');
    }
}
