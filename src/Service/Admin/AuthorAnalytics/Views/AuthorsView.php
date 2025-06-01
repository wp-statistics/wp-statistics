<?php

namespace WP_Statistics\Service\Admin\AuthorAnalytics\Views;

use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\AuthorAnalytics\AuthorAnalyticsDataProvider;
use WP_Statistics\Utils\Request;

class AuthorsView extends BaseView
{
    /**
     * Get author report data
     *
     * @return array
     */
    public function getData()
    {
        $postType = Request::get('pt', 'post');
        $orderBy  = Request::get('order_by');
        $order    = Request::get('order', 'DESC');

        $args = [
            'post_type' => $postType,
            'per_page'  => Admin_Template::$item_per_page,
            'page'      => Admin_Template::getCurrentPaged()
        ];

        if ($orderBy) {
            $args['order_by'] = $orderBy;
            $args['order']    = $order;
        }

        $authorAnalyticsData = new AuthorAnalyticsDataProvider($args);
        return $authorAnalyticsData->getAuthorsReportData();
    }

    public function renderLocked()
    {
        $args = [
            'page_title'        => esc_html__('Uncover Author Performance at a Glance', 'wp-statistics'),
            'addon_name'        => esc_html__('Data Plus', 'wp-statistics'),
            'addon_slug'        => 'wp-statistics-data-plus',
            'campaign'          => 'data-plus',
            'more_title'        => esc_html__('Learn More', 'wp-statistics'),
            'premium_btn_title' => esc_html__('Discover Author Insights with Premium', 'wp-statistics'),
            'images'            => ['data-plus-single-author.png'],
            'description'       => esc_html__('Track your authors\' impact, top posts, and engagement trends in one place. With Author Analytics, you get the insights needed to boost your content strategy.', 'wp-statistics'),
        ];
        Admin_Template::get_template(['layout/header']);
        View::load("pages/lock-page", $args);
        Admin_Template::get_template(['layout/footer']);
    }

    public function renderContent()
    {
        $data = $this->getData();

        $args = [
            'title'       => esc_html__('Authors', 'wp-statistics'),
            'pageName'    => Menus::get_page_slug('author-analytics'),
            'DateRang'    => Admin_Template::DateRange(),
            'custom_get'  => [
                'type'      => 'authors',
                'pt'        => Request::get('pt', 'post'),
                'order_by'  => Request::get('order_by', 'total_views'),
                'order'     => Request::get('order', 'desc'),
            ],
            'hasDateRang' => true,
            'filters'     => ['post-type'],
            'backUrl'     => Menus::admin_url('author-analytics'),
            'backTitle'   => esc_html__('Authors Performance', 'wp-statistics'),
            'data'        => $data['authors'],
            'paged'       => Admin_Template::getCurrentPaged(),
        ];

        if ($data['total'] > 0) {
            $args['total'] = $data['total'];

            $args['pagination'] = Admin_Template::paginate_links([
                'total' => $data['total'],
                'echo'  => false
            ]);
        }

        Admin_Template::get_template(['layout/header', 'layout/title', "pages/author-analytics/authors-report", 'layout/postbox.toggle', 'layout/footer'], $args);
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
        $postType = Request::get('pt', 'post');
        return !Helper::isAddOnActive('data-plus') && Helper::isCustomPostType($postType);
    }
}