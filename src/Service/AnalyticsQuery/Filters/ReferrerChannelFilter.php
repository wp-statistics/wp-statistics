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
    protected $groups    = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Traffic Channel', 'wp-statistics');
    }

    /**
     * {@inheritdoc}
     */
    public function getOptions(): ?array
    {
        return [
            ['value' => 'direct', 'label' => esc_html__('Direct', 'wp-statistics')],
            ['value' => 'search', 'label' => esc_html__('Search', 'wp-statistics')],
            ['value' => 'social', 'label' => esc_html__('Social', 'wp-statistics')],
            ['value' => 'referral', 'label' => esc_html__('Referral', 'wp-statistics')],
            ['value' => 'email', 'label' => esc_html__('Email', 'wp-statistics')],
            ['value' => 'paid', 'label' => esc_html__('Paid', 'wp-statistics')],
        ];
    }
}
