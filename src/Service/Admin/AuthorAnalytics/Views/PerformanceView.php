<?php

namespace WP_Statistics\Service\Admin\AuthorAnalytics\Views;

use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Assets;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\AuthorAnalytics\AuthorAnalyticsDataProvider;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Utils\Request;

class PerformanceView extends BaseView
{
    public function __construct()
    {
        $args = [
            'post_type' => Request::get('pt', 'post')
        ];

        $this->dataProvider = new AuthorAnalyticsDataProvider($args);
    }

    public function getData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Author_Analytics_Object', $this->dataProvider->getAuthorsChartData());

        return $this->dataProvider->getAuthorsPerformanceData();
    }

    public function isLocked()
    {
        return !Helper::isAddOnActive('data-plus') && Helper::isCustomPostType(Request::get('pt', 'post'));
    }

    public function renderContent()
    {
        $args = [
            'title'       => esc_html__('Author Analytics', 'wp-statistics'),
            'pageName'    => Menus::get_page_slug('author-analytics'),
            'paged'       => Admin_Template::getCurrentPaged(),
            'custom_get'  => ['pt' => Request::get('pt', 'post')],
            'DateRang'    => Admin_Template::DateRange(),
            'hasDateRang' => true,
            'filters'     => ['post-type'],
            'data'        => $this->getData()
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', "pages/author-analytics/authors-performance", 'layout/postbox.hide', 'layout/footer'], $args);
    }

    public function renderLocked()
    {
        $args = [
            'page_title'        => esc_html__('Uncover Author Performance at a Glance', 'wp-statistics'),
            'addon_name'        => esc_html__('Data Plus', 'wp-statistics'),
            'addon_slug'        => 'wp-statistics-data-plus',
            'campaign'          => 'author-analystics',
            'more_title'        => esc_html__('Learn More', 'wp-statistics'),
            'premium_btn_title' => esc_html__('Discover Author Insights with Premium', 'wp-statistics'),
            'images'            => ['data-plus-single-author.png'],
            'description'       => esc_html__('Track your authors\' impact, top posts, and engagement trends in one place. With Author Analytics, you get the insights needed to boost your content strategy.', 'wp-statistics'),
        ];
        Admin_Template::get_template(['layout/header']);
        View::load("pages/lock-page", $args);
        Admin_Template::get_template(['layout/footer']);
    }

    public function render()
    {
        try {
            if ($this->isLocked()) {
                $this->renderLocked();
            } else {
                $this->renderContent();
            }
        } catch (\Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}