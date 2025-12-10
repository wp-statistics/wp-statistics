<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Views per session filter - filters by pages viewed per session.
 *
 * @since 15.0.0
 */
class ViewsPerSessionFilter extends AbstractFilter
{
    protected $name               = 'views_per_session';
    protected $column             = 'sessions.pages_count';
    protected $type               = 'integer';
    protected $inputType          = 'number';
    protected $supportedOperators = ['is', 'gt', 'lt'];
    protected $groups             = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Views per Session', 'wp-statistics');
    }
}
