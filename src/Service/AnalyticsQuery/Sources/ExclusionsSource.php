<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Exclusions source - counts total exclusions.
 *
 * Counts the number of exclusions from the exclusions table.
 *
 * @since 15.0.0
 */
class ExclusionsSource extends AbstractSource
{
    protected $name       = 'exclusions';
    protected $expression = 'SUM(exclusions.count)';
    protected $table      = 'exclusions';
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
