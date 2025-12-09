<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer channel filter - filters by referrer channel (e.g., search, social, direct).
 *
 * @since 15.0.0
 */
class ReferrerChannelFilter extends AbstractFilter
{
    protected $name   = 'referrer_channel';
    protected $column = 'referrers.channel';
    protected $type   = 'string';
    protected $joins  = [
        'table' => 'referrers',
        'alias' => 'referrers',
        'on'    => 'sessions.referrer_id = referrers.ID',
    ];
    protected $supportedOperators = ['is', 'is_not'];

    protected $inputType = 'dropdown';
    protected $options   = [
        ['value' => 'direct', 'label' => 'Direct'],
        ['value' => 'search', 'label' => 'Search'],
        ['value' => 'social', 'label' => 'Social'],
        ['value' => 'referral', 'label' => 'Referral'],
        ['value' => 'email', 'label' => 'Email'],
        ['value' => 'paid', 'label' => 'Paid'],
    ];
    protected $pages = [
        'visitors-overview',
        'visitors',
        'views',
        'geographic',
        'referrers',
    ];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Traffic Channel', 'wp-statistics');
    }
}
