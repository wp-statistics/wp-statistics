<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Exit page source - counts views where this page was the exit page of a session.
 *
 * When filtering by resource_id, this counts how many times that page was the
 * last page visited in a session (i.e., where visitors left the site).
 *
 * @since 15.0.0
 */
class ExitPageSource extends AbstractSource
{
    protected $name       = 'exit_page';
    protected $expression = 'SUM(CASE WHEN views.ID = sessions.last_view_id THEN 1 ELSE 0 END)';
    protected $table      = 'sessions';
    protected $type       = 'integer';
    protected $format     = 'number';

    /**
     * {@inheritdoc}
     */
    public function supportsSummaryTable(): bool
    {
        return false;
    }
}
