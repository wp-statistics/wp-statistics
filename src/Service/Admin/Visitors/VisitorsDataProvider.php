<?php 

namespace WP_Statistics\Service\Admin\Visitors;

use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;

class VisitorsDataProvider
{
    protected $args;
    protected $visitorsModel;
    protected $viewsModel;
    
    public function __construct($args)
    {
        $this->args = $args;

        $this->visitorsModel = new VisitorsModel();
        $this->viewsModel    = new ViewsModel();
    }
}