<?php

namespace WP_Statistics\Service\Admin\Visitors\Views;

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseView;
use WP_Statistics\Exception\SystemErrorException;
use WP_Statistics\Service\Admin\Visitors\VisitorsDataProvider;

class SingleVisitorView extends BaseView
{
    protected $visitor_id;

    public function __construct()
    {
        $this->visitor_id = Request::get('visitor_id');

        if (empty($this->visitor_id)) {
            throw new SystemErrorException(esc_html__('Invalid visitor id provided.', 'wp-statistics'));
        }

        $this->dataProvider = new VisitorsDataProvider(['visitor_id' => $this->visitor_id]);
    }

    public function getData()
    {
        return $this->dataProvider->getVisitorData();
    }

    public function render()
    {
        $args = [
            'title'          => sprintf(esc_html__('Visitor Report - User ID: %s', 'wp-statistics'), $this->visitor_id),
            'tooltip'        => esc_html__('Visitor Report', 'wp-statistics'),
            'backUrl'        => Menus::admin_url('visitors'),
            'backTitle'      => esc_html__('Visitor and Views Report', 'wp-statistics'),
            'searchBoxTitle' => esc_html__('IP, Hash, Username, or Email', 'wp-statistics'),
            'data'           => $this->getData(),
        ];
        Admin_Template::get_template(['layout/header', 'layout/title'], $args);
        View::load('pages/visitors/single-visitor', $args);
        Admin_Template::get_template(['layout/footer'], $args);
    }
}