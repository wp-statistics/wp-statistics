<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Exit rate source - percentage of views where the page was an exit page.
 *
 * Exit rate is calculated as: (number of exits / total views) * 100
 * This represents the percentage of pageviews that were the last in a session.
 *
 * @since 15.0.0
 */
class ExitRateSource extends AbstractSource
{
    protected $name       = 'exit_rate';
    protected $expression = 'ROUND(SUM(CASE WHEN views.ID = sessions.last_view_id THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(views.ID), 0), 1)';
    protected $table      = 'sessions';
    protected $type       = 'float';
    protected $format     = 'percent';

    /**
     * {@inheritdoc}
     */
    public function supportsSummaryTable(): bool
    {
        return false;
    }
}
