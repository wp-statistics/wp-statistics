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
        $orderBy    = Request::get('order_by', 'visitors');
        $order      = Request::get('order', 'DESC');

        $args = [
            'order_by'  => $orderBy,
            'order'     => $order,
            'author_id' => $this->authorID,
            'per_page'  => Admin_Template::$item_per_page,
            'page'      => Admin_Template::getCurrentPaged(),
        ];

        if (Request::has('pt')) {
            $args['post_type'] = Request::get('pt', 'post');;
        }

        $dataProviderClass = new PostsDataProvider($args);
        return $dataProviderClass->getPostsReportData();
    }

    public function render()
    {
        $data       = $this->getData();
        $parentPage = Menus::getCurrentPage();
        $template   = 'posts-report';

        $queryParams = [
            'type'      => 'posts',
            'order_by'  => Request::get('order_by', 'visitors'),
            'order'     => Request::get('order', 'desc'),
        ];

        if (Request::has('pt')) {
            $queryParams['pt'] = Request::get('pt');
        }

        $args = [
            'title'         => Request::has('pt') ? Helper::getPostTypeName(Request::get('pt')) : esc_html__('Contents', 'wp-statistics'),
            'pageName'      => Menus::get_page_slug($parentPage['page_url']),
            'custom_get'    => $queryParams,
            'DateRang'      => Admin_Template::DateRange(),
            'hasDateRang'   => true,
            'backUrl'       => Menus::admin_url($parentPage['page_url']),
            'backTitle'     => $parentPage['title'],
            'allTimeOption' => true,
            'filters'       => ['post-types','author'],
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

        Admin_Template::get_template(['layout/header', 'layout/title', "pages/posts/$template", 'layout/postbox.toggle', 'layout/footer'], $args);
    }
}