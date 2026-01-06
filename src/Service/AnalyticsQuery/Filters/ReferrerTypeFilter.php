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
     * UI input component type.
     *
     * @var string Input type: dropdown
     */
    protected $inputType = 'dropdown';

    /**
     * Allowed comparison operators.
     *
     * @var array Operators: is, is_not, in, not_in
     */
    protected $supportedOperators = ['is', 'is_not', 'in', 'not_in'];

    /**
     * Pages where this filter is available.
     *
     * @var array Groups: visitors, views
     */
    protected $groups = ['visitors', 'views'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Referrer Type', 'wp-statistics');
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
