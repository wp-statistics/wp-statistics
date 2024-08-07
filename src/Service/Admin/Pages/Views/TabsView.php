<?php 

namespace WP_Statistics\Service\Admin\Pages\Views;

use Exception;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\Pages\PagesDataProvider;

class TabsView extends BaseTabView 
{
    protected $defaultTab = 'contents';
    protected $tabs = [
        'contents',
        'category',
        'author',
        '404',
    ];

    public function __construct()
    {
        $args = [
            'date'      => [
                'from'  => Request::get('from', date('Y-m-d', strtotime('-29 day'))), 
                'to'    => Request::get('to', date('Y-m-d'))
            ],
            'order'     => Request::get('order', 'DESC'),
            'author_id' => Request::get('author_id', '', 'number'),
            'per_page'  => Admin_Template::$item_per_page,
            'page'      => Admin_Template::getCurrentPaged(),
        ];

        if ($this->isTab('contents')) {
            $args['order_by'] = Request::get('order_by', 'visitors');
        }

        if (Request::has('pt')) {
            $args['post_type'] = Request::get('pt', 'post');
        }

        $this->dataProvider = new PagesDataProvider($args);
    }

    public function getContentsData()
    {
        return $this->dataProvider->getContentsData();
    }

    public function getCategoryData()
    {

    }

    public function getAuthorData()
    {

    }

    public function render()
    {
        try {
            $currentTab = $this->getCurrentTab();
            $data       = $this->getTabData();
    
            $args = [
                'title'         => esc_html__('Pages', 'wp-statistics'),
                'pageName'      => Menus::get_page_slug('pages'),
                'custom_get'    => ['tab' => $currentTab],
                'DateRang'      => Admin_Template::DateRange(),
                'hasDateRang'   => true,
                'data'          => $data,
                'allTimeOption' => true,
                'filters'       => ['post-types','author'],
                'pagination'    => Admin_Template::paginate_links([
                    'total' => isset($data['total']) ? $data['total'] : 0,
                    'echo'  => false
                ]),
                'tabs'          => [
                    [
                        'link'    => Menus::admin_url('pages', ['tab' => 'contents']),
                        'title'   => esc_html__('Contents', 'wp-statistics'),
                        'tooltip' => esc_html__('Contents tooltip', 'wp-statistics'),
                        'class'   => $this->isTab('contents') ? 'current' : '',
                    ],
                    [
                        'link'    => Menus::admin_url('pages', ['tab' => 'category']),
                        'title'   => esc_html__('Category Pages', 'wp-statistics'),
                        'tooltip' => esc_html__('Category Pages tooltip', 'wp-statistics'),
                        'class'   => $this->isTab('category') ? 'current' : '',
                    ],
                    [
                        'link'    => Menus::admin_url('pages', ['tab' => 'author']),
                        'title'   => esc_html__('Author Pages', 'wp-statistics'),
                        'tooltip' => esc_html__('Author Pages tooltip', 'wp-statistics'),
                        'class'   => $this->isTab('author') ? 'current' : '',
                    ],
                    [
                        'link'          => Menus::admin_url('pages', ['tab' => '404']),
                        'title'         => esc_html__('404 Pages', 'wp-statistics'),
                        'class'         => $this->isTab('404') ? 'current' : '',
                        'coming_soon'   => true
                    ]
                ]
            ];

            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header'], $args);
            View::load("pages/pages/$currentTab", $args);
            Admin_Template::get_template(['layout/postbox.hide', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}