<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer filter - filters by referrer domain.
 *
 * @since 15.0.0
 */
class ReferrerFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[referrer]=... */
    protected $name = 'referrer';

    /** @var string SQL column: referrer domain from referrers table (e.g., google.com, facebook.com) */
    protected $column = 'referrers.domain';

    /** @var string Data type: string for domain matching */
    protected $type = 'string';

    /**
     * Required JOIN: sessions -> referrers.
     * Links session's referrer ID to the referrer details lookup table.
     *
     * @var array
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
        return esc_html__('Referrer', 'wp-statistics');
    }
}
