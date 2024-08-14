<?php

namespace WP_Statistics\Service\Admin\AuthorAnalytics\Views;

use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Assets;
use WP_STATISTICS\Admin_Template;
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

    public function render()
    {
        try {
            $data       = $this->getData();
            $template   = $this->isLocked() ? 'authors-performance-locked' : 'authors-performance';

            $args = [
                'title'       => esc_html__('Author Analytics', 'wp-statistics'),
                'pageName'    => Menus::get_page_slug('author-analytics'),
                'paged'       => Admin_Template::getCurrentPaged(),
                'custom_get'  => ['pt' => Request::get('pt', 'post')],
                'DateRang'    => Admin_Template::DateRange(),
                'hasDateRang' => true,
                'filters'     => ['post-type'],
                'data'        => $data
            ];

            Admin_Template::get_template(['layout/header', 'layout/title', "pages/author-analytics/$template", 'layout/postbox.hide', 'layout/footer'], $args);
        } catch (\Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}