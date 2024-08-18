<?php 

namespace WP_Statistics\Service\Admin\Visitors;

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Models\OnlineModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;

class VisitorsDataProvider
{
    protected $args;
    protected $visitorsModel;
    protected $onlineModel;
    protected $viewsModel;
    
    public function __construct($args)
    {
        $this->args = $args;

        $this->visitorsModel = new VisitorsModel();
        $this->onlineModel   = new OnlineModel();
        $this->viewsModel    = new ViewsModel();
    }

    public function getVisitorsData()
    {
        return [
            'data'  => $this->visitorsModel->getVisitorsData(array_merge($this->args, [
                'page_info' => true,
                'user_info' => true,
                'order_by'  => 'date',
                'order'     => 'DESC',
                'page'      => Admin_Template::getCurrentPaged(),
                'per_page'  => Admin_Template::$item_per_page,
            ])),
            'total' => $this->visitorsModel->countVisitors($this->args)
        ];
    }

    public function getOnlineVisitorsData()
    {
        return [
            'data'  => $this->onlineModel->getOnlineVisitorsData(array_merge($this->args, [
                'order_by'  => 'date',
                'order'     => 'DESC',
                'page'      => Admin_Template::getCurrentPaged(),
                'per_page'  => Admin_Template::$item_per_page
            ])),
            'total' => $this->onlineModel->countOnlines($this->args)
        ];
    }

    public function getTopVisitorsData()
    {
        return [
            'data'  => $this->visitorsModel->getVisitorsData(array_merge($this->args, [
                'page_info' => true,
                'user_info' => true,
                'order_by'  => 'hits',
                'order'     => 'DESC',
                'page'      => Admin_Template::getCurrentPaged(),
                'per_page'  => Admin_Template::$item_per_page,
            ])),
            'total' => $this->visitorsModel->countVisitors($this->args)
        ];
    }

    public function getVisitorData()
    {
        $visitorInfo    = $this->visitorsModel->getVisitorData($this->args);
        $userInfo       = !empty($visitorInfo->user_id) ? new \WP_User($visitorInfo->user_id) : [];
        $visitorJourney = $this->visitorsModel->getVisitorJourney($this->args);

        return [
            'visitor_info'      => $visitorInfo,
            'visitor_journey'   => $visitorJourney,
            'user_info'         => $userInfo
        ];
    }
}