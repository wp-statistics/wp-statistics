<?php

namespace WP_Statistics\Service\Admin\VisitorInsights\Views;

use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Exception\SystemErrorException;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\VisitorInsights\VisitorInsightsDataProvider;
use WP_Statistics\Utils\Request;

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

        if (empty($visitorData['visitor'])) {
            throw new SystemErrorException(esc_html__('Visitor does not exist.', 'wp-statistics'));
        }

        return $visitorData;
    }

    public function getTitle($visitorData)
    {
        /** @var VisitorDecorator $visitor */
        $visitor = $visitorData['visitor'];

        $title = esc_html__('Visitor Report - %s: %s', 'wp-statistics');

        if ($visitor->isLoggedInUser()) {
            $title = sprintf($title, esc_html__('User', 'wp-statistics'), $visitor->getUser()->getDisplayName());
        } else {
            $title = sprintf(
                $title,
                $visitor->isHashedIP() ? esc_html__('Hash', 'wp-statistics') : esc_html__('IP', 'wp-statistics'),
                $visitor->getIP()
            );
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