<?php

namespace WP_Statistics\Service\AnalyticsQuery\Filters;

/**
 * Session duration filter - filters by session length in seconds.
 *
 * @since 15.0.0
 */
class SessionDurationFilter extends AbstractFilter
{
    protected $name               = 'session_duration';
    protected $column             = 'sessions.duration';
    protected $type               = 'integer';
    protected $inputType          = 'number';
    protected $supportedOperators = ['gt', 'lt', 'between'];
    protected $groups             = ['visitors'];

    /**
     * {@inheritdoc}
     */
    public function getLabel(): string
    {
        return esc_html__('Session Duration', 'wp-statistics');
    }
}
