<?php 

namespace WP_Statistics\Service\AuthorAnalytics\Views;

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;
use WP_STATISTICS\User;
use InvalidArgumentException;

class SingleAuthorView
{
    public function __construct() 
    {
        // Throw error when invalid author ID provided
        if (isset($_GET['author_id']) && !User::exists(intval($_GET['author_id']))) {
            throw new InvalidArgumentException(esc_html__('Invalid author ID provided.', 'wp-statistics'));
        }
    }

    public function view()
    {
        $authorID = isset($_GET['author_id']) ? sanitize_text_field($_GET['author_id']) : '';
        $author   = get_userdata($authorID);

        $args = [
            'title'       => esc_html__('Author: ', 'wp-statistics') . $author->display_name,
            'pageName'    => Menus::get_page_slug('author-analytics'),
            'pagination'  => Admin_Template::getCurrentPaged(),
            'custom_get'  => ['author_id' => $authorID],
            'DateRang'    => Admin_Template::DateRange(),
            'HasDateRang' => true,
            'backUrl'     => Menus::admin_url('author-analytics'),
            'backTitle'   => esc_html__('Authors Performance', 'wp-statistics'),
            'filters'     => ['post-type'],
        ];

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/author-analytics/author-single', 'layout/postbox.toggle', 'layout/footer'], $args);
    }
}