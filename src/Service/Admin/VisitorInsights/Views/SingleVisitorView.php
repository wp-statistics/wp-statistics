<?php

namespace WP_Statistics\Service\Admin\VisitorInsights\Views;

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseView;
use WP_Statistics\Exception\SystemErrorException;
use WP_STATISTICS\IP;
use WP_Statistics\Service\Admin\VisitorInsights\VisitorInsightsDataProvider;

class SingleVisitorView extends BaseView
{
    protected $visitor_id;

    public function __construct()
    {
        $this->visitor_id = Request::get('visitor_id');

        if (empty($this->visitor_id)) {
            throw new SystemErrorException(esc_html__('Please provide a valid visitor ID.', 'wp-statistics'));
        }

        $this->dataProvider = new VisitorInsightsDataProvider(['visitor_id' => $this->visitor_id]);
    }

    public function getData()
    {
        $visitorData = $this->dataProvider->getVisitorData();

        if (empty($visitorData['visitor_info'])) {
            throw new SystemErrorException(esc_html__('Visitor does not exist.', 'wp-statistics'));
        }

        return $visitorData;
    }

    public function getTitle($visitorData)
    {
        $title = esc_html__('Visitor Report - %s: %s', 'wp-statistics');

        if (!empty($visitorData['user_info'])) {
            $title = sprintf($title, esc_html__('User', 'wp-statistics'), $visitorData['user_info']->display_name);
        } else if (IP::IsHashIP($visitorData['visitor_info']->ip)) {
            $title = sprintf($title, esc_html__('Hash', 'wp-statistics'), substr($visitorData['visitor_info']->ip, 6, 10));
        } else {
            $title = sprintf($title, esc_html__('IP', 'wp-statistics'), $visitorData['visitor_info']->ip);
        }

        return $title;
    }

    public function render()
    {
        $visitorData = $this->getData();
        $title       = $this->getTitle($visitorData);

        $args = [
            'title'          => $title,
            'backUrl'        => Menus::admin_url('visitors'),
            'backTitle'      => esc_html__('Visitor Insights', 'wp-statistics'),
            'searchBoxTitle' => esc_html__('IP, Hash, Username, or Email', 'wp-statistics'),
            'data'           => $visitorData
        ];
        Admin_Template::get_template(['layout/header', 'layout/title'], $args);
        View::load('pages/visitor-insights/single-visitor', $args);
        Admin_Template::get_template(['layout/footer'], $args);
    }
}