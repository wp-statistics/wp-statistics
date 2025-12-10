<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * IP filter - filters by visitor IP address.
 *
 * @since 15.0.0
 */
class IpFilter extends AbstractFilter
{
    /** @var string Filter identifier for API requests: filters[ip]=... */
    protected $name = 'ip';

    /** @var string SQL column: visitor IP address or anonymized hash from sessions table */
    protected $column = 'sessions.ip';

    /** @var string Data type: string for IP address or hash matching */
    protected $type = 'string';

    /** @var string UI component: text input for free-form IP/hash entry */
    protected $inputType = 'text';

    /** @var array Supported operators: exact match, exclusion, and partial matching */
    protected $supportedOperators = ['is', 'is_not', 'contains'];

    /** @var array Available on: visitors page for visitor identification */
    protected $groups = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('IP Address/Hash', 'wp-statistics');
    }
}
