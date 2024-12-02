<?php
namespace WP_Statistics\Service\Admin\Metabox;

use WP_Statistics\Models\OnlineModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Models\ViewsModel;

class MetaboxDataProvider
{
    protected $visitorsModel;
    protected $viewsModel;
    protected $onlineModel;

    public function __construct()
    {
        $this->visitorsModel    = new VisitorsModel();
        $this->viewsModel       = new ViewsModel();
        $this->onlineModel      = new OnlineModel();
    }

    public function getTrafficSummaryData($args = [])
    {
        $visitors   = $this->visitorsModel->getVisitorsSummary($args);
        $views      = $this->viewsModel->getViewsSummary($args);

        $data = [
            'online'    => $this->onlineModel->countOnlines($args),
            'visitors'  => array_values(wp_list_pluck($visitors, 'visitors')),
            'views'     => array_values(wp_list_pluck($views, 'views')),
            'labels'    => array_values(wp_list_pluck($views, 'label')),
            'keys'      => array_keys($views),
        ];

        return $data;
    }
}