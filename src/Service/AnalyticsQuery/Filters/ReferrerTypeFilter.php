<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer type filter - filters by referrer channel/type.
 *
 * @since 15.0.0
 */
class ReferrerTypeFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[referrer_type]=... */
    protected $name = 'referrer_type';

    /** @var string SQL column: traffic channel/type from referrers table (alias for channel field) */
    protected $column = 'referrers.channel';

    /** @var string Data type: string for channel type matching */
    protected $type = 'string';

    /**
     * Required JOIN: sessions -> referrers.
     * Links session's referrer ID to get the traffic type classification.
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
        return esc_html__('Referrer Type', 'wp-statistics');
    }
}
