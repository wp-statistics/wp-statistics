<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * IP filter - filters by visitor IP address.
 *
 * @since 15.0.0
 */
class IpFilter extends AbstractFilter
{
    protected $name               = 'ip';
    protected $column             = 'sessions.ip';
    protected $type               = 'string';
    protected $inputType          = 'text';
    protected $supportedOperators = ['is', 'is_not', 'contains'];
    protected $pages              = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('IP Address/Hash', 'wp-statistics');
    }
}
