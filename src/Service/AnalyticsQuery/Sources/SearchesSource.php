<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Searches source - counts total search events.
 *
 * Counts the number of times searches were performed, including
 * multiple searches within the same session.
 *
 * @since 15.0.0
 */
class SearchesSource extends AbstractSource
{
    protected $name       = 'searches';
    protected $expression = 'COUNT(views.ID)';
    protected $table      = 'views';
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
