<?php

namespace WP_Statistics\Service\Admin\ContentAnalytics\Views;

use Exception;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseView;
use WP_Statistics\Exception\SystemErrorException;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\ContentAnalytics\ContentAnalyticsDataProvider;
use WP_STATISTICS\Admin_Assets;

class SingleResourceView extends BaseView
{
    protected $dataProvider;
    private $resourceUri;

    public function __construct()
    {
        $this->resourceUri = Request::get('uri');

        if (empty($this->resourceUri)) {
            throw new SystemErrorException(
                esc_html__('Invalid URI provided.', 'wp-statistics')
            );
        }

        $this->dataProvider = new ContentAnalyticsDataProvider([
            'uri' => $this->resourceUri
        ]);
    }

    public function getData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Content_Analytics_Object', $this->dataProvider->getSingleResourceChartData());

        return $this->dataProvider->getSingleResourceData();
    }

    public function render()
    {
        $args = [
            'custom_get'    => ['type' => 'single-resource', 'uri' => Request::get('uri')],
            'pageName'      => Menus::get_page_slug('content-analytics'),
            'DateRang'      => Admin_Template::DateRange(),
            'hasDateRang'   => true,
            'data'          => $this->getData(),
            'allTimeOption' => true
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', "pages/content-analytics/single-resource", 'layout/footer'], $args);
    }
}