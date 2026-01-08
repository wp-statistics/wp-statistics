<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Entry page source - counts views where this page was the entry page of a session.
 *
 * When filtering by resource_id, this counts how many times that page was the
 * first page visited in a session (i.e., the landing page).
 *
 * @since 15.0.0
 */
class EntryPageSource extends AbstractSource
{
    protected $name       = 'entry_page';
    protected $expression = 'SUM(CASE WHEN views.ID = sessions.initial_view_id THEN 1 ELSE 0 END)';
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
