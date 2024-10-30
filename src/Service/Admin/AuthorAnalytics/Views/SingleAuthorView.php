<?php

namespace WP_Statistics\Service\Admin\AuthorAnalytics\Views;

use Exception;
use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

class SingleAuthorView extends BaseView
{
    public function render()
    {
        try {
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
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}