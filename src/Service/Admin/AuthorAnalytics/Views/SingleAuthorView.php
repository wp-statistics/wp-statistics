<?php

namespace WP_Statistics\Service\Admin\AuthorAnalytics\Views;

use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

class SingleAuthorView extends BaseView
{
    public function render()
    {
        try {
            $args = [
                'title'     => esc_html__('Detailed Author Stats Locked: DataPlus Add-On Required', 'wp-statistics'),
                'backUrl'   => Menus::admin_url('author-analytics'),
                'backTitle' => esc_html__('Authors Performance', 'wp-statistics'),
            ];

            Admin_Template::get_template(['layout/header', 'layout/title', 'pages/author-analytics/author-single-locked', 'layout/footer'], $args);
        } catch (\Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}