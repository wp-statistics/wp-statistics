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
use WP_Statistics\Service\Admin\ExportImport\ExportTypes;

class SingleView extends BaseView
{
    protected $dataProvider;
    private $postId;

    public function __construct()
    {
        $this->postId = Request::get('post_id', false, 'number');

        // If post does not exist, show error
        if (!$this->postId || !get_post($this->postId)) {
            throw new SystemErrorException(
                esc_html__('Invalid post id provided.', 'wp-statistics')
            );
        }

        $this->dataProvider = new ContentAnalyticsDataProvider([
            'post_id'       => $this->postId,
            'resource_type' => Helper::getPostTypes(),
            'query_param'   => Helper::isAddOnActive('data-plus') ? Request::get('qp', '', 'number') : ''
        ]);
    }

    public function getData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Content_Analytics_Object', $this->dataProvider->getChartsData());

        return $this->dataProvider->getSinglePostData();
    }

    public function renderLocked()
    {
        $args = [
            'page_title' => __('Deeper reports for every post and page', 'wp-statistics'),
            'benefits'   => [
                __('Custom post types and taxonomies', 'wp-statistics'),
                __('Link clicks and downloads per post', 'wp-statistics'),
                __('Weekly comparison and hourly traffic', 'wp-statistics'),
            ],
            'addon_name'        => esc_html__('Data Plus', 'wp-statistics'),
            'addon_slug'        => 'wp-statistics-data-plus',
            'campaign'          => 'data-plus',
            'images'            => ['data-plus-advanced-filtering.png','data-plus-category.png','data-plus-comparison-widget.png','data-plus-download-tracker-recents.png'],
        ];

        Admin_Template::get_template(['layout/header']);
        View::load("pages/lock-page", $args);
        Admin_Template::get_template(['layout/footer']);
    }

    public function renderContent()
    {
        $args = [
            'backUrl'       => Menus::admin_url('content-analytics', ['tab' => get_post_type($this->postId)]),
            'custom_get'    => ['type' => 'single', 'post_id' => Request::get('post_id', '', 'number'), 'qp' => Request::get('qp', '', 'number')],
            'backTitle'     => esc_html__('Content Analytics', 'wp-statistics'),
            'pageName'      => Menus::get_page_slug('content-analytics'),
            'DateRang'      => Admin_Template::DateRange(),
            'filters'       => ['query-params'],
            'hasDateRang'   => true,
            'data'          => $this->getData(),
            'allTimeOption' => true,
            'export'        => [ExportTypes::CSV_METRICS, ExportTypes::PDF_PAGE]
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', "pages/content-analytics/single", 'layout/footer'], $args);
    }

    public function render()
    {
        if ($this->isLocked()) {
            $this->renderLocked();
        } else {
            $this->renderContent();
        }
    }

    public function isLocked()
    {
        return !Helper::isAddOnActive('data-plus') && in_array(get_post_type($this->postId), Helper::getCustomPostTypes());
    }
}