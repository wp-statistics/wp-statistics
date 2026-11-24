<?php

namespace WP_Statistics\Service\Admin\VisitorInsights\Views;

use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Exception\SystemErrorException;
use WP_STATISTICS\Menus;
use WP_STATISTICS\User;
use WP_Statistics\Service\Admin\ExportImport\ExportTypes;
use WP_Statistics\Service\Admin\VisitorInsights\VisitorInsightsDataProvider;
use WP_Statistics\Utils\Request;
use WP_Statistics\Service\Admin\PageHeader\SingleHeaders;

class SingleVisitorView extends BaseView
{
    protected $visitor_id;

    public function __construct()
    {
        if (!User::Access('read')) {
            throw new SystemErrorException(esc_html__('You do not have permission to view this report.', 'wp-statistics'));
        }

        $this->visitor_id = Request::get('visitor_id', 0, 'number');

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

    /**
     * Short title: the user name, or the IP / hash, following the visitor card privacy rule.
     */
    public function getTitle($visitorData)
    {
        /** @var VisitorDecorator $visitor */
        $visitor = $visitorData['visitor'];

        return $visitor->getDisplayLabel();
    }

    public function render()
    {
        $visitorData = $this->getData();
        $title       = $this->getTitle($visitorData);

        $args = [
            'title'          => $title,
            'pageHeader'     => SingleHeaders::visitor($visitorData['visitor']),
            'backUrl'        => Menus::admin_url('visitors'),
            'backTitle'      => esc_html__('Visitor Insights', 'wp-statistics'),
            'searchBoxTitle' => esc_html__('IP, Hash, Username, or Email', 'wp-statistics'),
            'export'         => [ExportTypes::PDF_PAGE],
            'data'           => $visitorData
        ];
        Admin_Template::get_template(['layout/header', 'layout/title'], $args);
        View::load('pages/visitor-insights/single-visitor', $args);
        Admin_Template::get_template(['layout/footer'], $args);
    }
}