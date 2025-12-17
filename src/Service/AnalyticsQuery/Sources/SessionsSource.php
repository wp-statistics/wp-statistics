<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Sessions source - counts total sessions.
 *
 * @since 15.0.0
 */
class SessionsSource extends AbstractSource
{
    protected $name       = 'sessions';
    protected $expression = 'COUNT(sessions.ID)';
    protected $table      = 'sessions';
    protected $type       = 'integer';
    protected $format     = 'number';

    /**
     * {@inheritdoc}
     */
    public function supportsSummaryTable(): bool
    {
        return true;
    }
}
