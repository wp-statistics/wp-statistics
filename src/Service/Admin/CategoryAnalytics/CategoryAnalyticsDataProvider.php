<?php 

namespace WP_Statistics\Service\Admin\CategoryAnalytics;


class CategoryAnalyticsDataProvider
{
    protected $args;
    
    public function __construct($args)
    {
        $this->args = $args;
    }
}