<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Last seen filter - filters by last activity timestamp.
 *
 * @since 15.0.0
 */
class LastSeenFilter extends AbstractFilter
{
    protected $name               = 'last_seen';
    protected $column             = 'visitors.last_hit';
    protected $type               = 'date';
    protected $inputType          = 'date';
    protected $supportedOperators = ['in_the_last', 'between', 'before', 'after'];
    protected $pages              = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Last Seen', 'wp-statistics');
    }
}
