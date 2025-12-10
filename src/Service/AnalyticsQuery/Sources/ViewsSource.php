<?php

namespace WP_Statistics\Service\AnalyticsQuery\Sources;

/**
 * Views source - counts page views.
 *
 * @since 15.0.0
 */
class ViewsSource extends AbstractSource
{
    protected $name       = 'views';
    protected $expression = 'COUNT(views.ID)';
    protected $table      = 'views';
    protected $type       = 'integer';
    protected $format     = 'number';
}
