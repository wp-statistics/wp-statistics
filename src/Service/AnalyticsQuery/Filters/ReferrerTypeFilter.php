<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer type filter - filters by referrer channel/type.
 *
 * @since 15.0.0
 */
class ReferrerTypeFilter extends AbstractFilter
{
    protected $name   = 'referrer_type';
    protected $column = 'referrers.channel';
    protected $type   = 'string';
    protected $joins  = [
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
