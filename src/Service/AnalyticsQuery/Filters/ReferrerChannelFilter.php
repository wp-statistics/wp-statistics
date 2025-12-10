<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer channel filter - filters by referrer channel (e.g., search, social, direct).
 *
 * @since 15.0.0
 */
class ReferrerChannelFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[referrer_channel]=... */
    protected $name = 'referrer_channel';

    /** @var string SQL column: traffic channel type from referrers table (direct, search, social, etc.) */
    protected $column = 'referrers.channel';

    /** @var string Data type: string for channel matching */
    protected $type = 'string';

    /**
     * Required JOIN: sessions -> referrers.
     * Links session's referrer ID to get the traffic channel classification.
     *
     * @var array
     */
    protected $joins = [
        'table' => 'referrers',
        'alias' => 'referrers',
        'on'    => 'sessions.referrer_id = referrers.ID',
    ];

    /** @var array Supported operators: exact match and exclusion */
    protected $supportedOperators = ['is', 'is_not'];

    /** @var string UI component: dropdown with predefined traffic channels */
    protected $inputType = 'dropdown';

    /** @var array Available on: visitors page for traffic source analysis */
    protected $groups = ['visitors'];

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
