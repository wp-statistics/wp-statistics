<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Visitors source - counts unique visitors.
 *
 * @since 15.0.0
 */
class VisitorsSource extends AbstractSource
{
    protected $name       = 'visitors';
    protected $expression = 'COUNT(DISTINCT sessions.visitor_id)';
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
