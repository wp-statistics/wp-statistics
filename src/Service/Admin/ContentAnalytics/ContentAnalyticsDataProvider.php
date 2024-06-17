<?php 

namespace WP_Statistics\Service\Admin\ContentAnalytics;


class ContentAnalyticsDataProvider
{
    protected $args;
    
    public function __construct($args)
    {
        $this->args = $args;
    }
}