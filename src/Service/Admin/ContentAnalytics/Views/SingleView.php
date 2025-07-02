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
            'page_title'        => esc_html__('Data Plus: Advanced Analytics for Deeper Insights', 'wp-statistics'),
            'page_second_title' => esc_html__('WP Statistics Premium: Beyond Just Data Plus', 'wp-statistics'),
            'addon_name'        => esc_html__('Data Plus', 'wp-statistics'),
            'addon_slug'        => 'wp-statistics-data-plus',
            'campaign'          => 'content',
            'more_title'        => esc_html__('Learn More About Data Plus', 'wp-statistics'),
            'premium_btn_title' => esc_html__('Upgrade Now to Unlock All Premium Features!', 'wp-statistics'),
            'images'            => ['data-plus-advanced-filtering.png','data-plus-category.png','data-plus-comparison-widget.png','data-plus-download-tracker-recents.png'],
            'description'       => esc_html__('Data Plus is a premium add-on for WP Statistics that unlocks powerful analytics features, providing a complete view of your site’s performance. Take advantage of advanced tools that help you understand visitor behavior, enhance your content, and track engagement on a new level. With Data Plus, you can make data-driven decisions to grow your site more effectively.', 'wp-statistics'),
            'second_description'=> esc_html__('When you upgrade to WP Statistics Premium, you don’t just get Data Plus — you gain access to all premium add-ons, delivering detailed insights and tools for every aspect of your site.', 'wp-statistics')
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
            'allTimeOption' => true
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', "pages/content-analytics/single", 'layout/footer'], $args);
    }

    public function render()
    {
        try {
            if ($this->isLocked()) {
                $this->renderLocked();
            } else {
                $this->renderContent();
            }
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }

    public function isLocked()
    {
        return !Helper::isAddOnActive('data-plus') && in_array(get_post_type($this->postId), Helper::getCustomPostTypes());
    }
}