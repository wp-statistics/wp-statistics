<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * First seen filter - filters by first visit date.
 *
 * @since 15.0.0
 */
class FirstSeenFilter extends AbstractFilter
{
    protected $name               = 'first_seen';
    protected $column             = 'visitors.first_hit';
    protected $type               = 'date';
    protected $inputType          = 'date';
    protected $supportedOperators = ['between', 'before', 'after'];
    protected $pages              = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('First Seen', 'wp-statistics');
    }
}
