<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Referrer channel filter - filters by referrer channel (e.g., search, social, direct).
 *
 * @since 15.0.0
 */
class ReferrerChannelFilter extends AbstractFilter
{
    /**
     * Filter key for API requests.
     *
     * @var string Filter identifier: filters[referrer_channel]=...
     */
    protected $name = 'referrer_channel';

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
     * Allowed comparison operators.
     *
     * @var array Operators: is, is_not, in, not_in
     */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * UI input component type.
     *
     * @var string Input type: dropdown
     */
    protected $inputType = 'dropdown';

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: visitors
     */
    protected $groups = ['visitors', 'views', 'individual-content', 'referrals', 'content', 'categories', 'individual-category', 'individual-author'];

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
