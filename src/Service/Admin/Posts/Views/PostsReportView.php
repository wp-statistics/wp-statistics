<?php 
namespace WP_Statistics\Service\Admin\Posts\Views;

use InvalidArgumentException;
use WP_Statistics\Abstracts\BaseView;
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\Posts\PostsDataProvider;
use WP_STATISTICS\User;
use WP_Statistics\Utils\Request;

class PostsReportView extends BaseView
{
    private $authorID;

    public function __construct() 
    {
        $this->authorID = Request::get('author_id', '', 'number');

        // Throw error when invalid author ID provided
        if ($this->authorID && !User::exists($this->authorID)) {
            throw new InvalidArgumentException(
                esc_html__('Invalid author ID provided.', 'wp-statistics')
            );
        }
    }

    public function getData()
    {
        $from       = Request::get('from', date('Y-m-d', strtotime('-1 month')));
        $to         = Request::get('to', date('Y-m-d'));
        $postType   = Request::get('pt', 'post');
        $orderBy    = Request::get('order_by', 'views');
        $order      = Request::get('order', 'DESC');

        $args = [
            'date'      => ['from' => $from, 'to' => $to],
            'post_type' => $postType,
            'order_by'  => $orderBy,
            'order'     => $order,
            'author_id' => $this->authorID,
            'per_page'  => Admin_Template::$item_per_page,
            'page'      => Admin_Template::getCurrentPaged(),
        ];

        $dataProviderClass = new PostsDataProvider($args);
        return $dataProviderClass->getPostsReportData();
    }

    public function render()
    {
        $postType   = Request::get('pt', 'post');
        $data       = $this->getData();

        $args = [
            'title'         => Helper::getPostTypeName($postType),
            'pageName'      => Menus::get_page_slug('author-analytics'),
            'custom_get'    => ['type' => 'posts', 'pt' => $postType],
            'DateRang'      => Admin_Template::DateRange(),
            'hasDateRang'   => true,
            'backUrl'       => Menus::admin_url('author-analytics'),
            'backTitle'     => esc_html__('Authors Performance', 'wp-statistics'),
            'filters'       => ['post-type','author'],
            'data'          => $data,
            'paged'         => Admin_Template::getCurrentPaged()
        ];

        if ($this->authorID) {
            $args['custom_get']['author_id'] = $this->authorID;
        }

        if ($data['total'] > 0) {
            $args['total'] = $data['total'];

            $args['pagination'] = Admin_Template::paginate_links([
                'total' => $data['total'],
                'echo'  => false
            ]);
        }

        Admin_Template::get_template(['layout/header', 'layout/title', 'pages/posts/posts-report', 'layout/postbox.toggle', 'layout/footer'], $args);
    }
}