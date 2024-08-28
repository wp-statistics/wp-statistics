<?php

namespace WP_Statistics\Service\Admin\PageInsights\Views;

use Exception;
use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\PageInsights\PageInsightsDataProvider;

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
            'order'     => Request::get('order', 'DESC'),
            'author_id' => Request::get('author_id', '', 'number'),
            'taxonomy'  => Request::get('tx', 'category'),
            'per_page'  => Admin_Template::$item_per_page,
            'page'      => Admin_Template::getCurrentPaged(),
        ];

        if (Request::has('pt')) {
            $args['post_type'] = Request::get('pt', 'post');
        }

        $this->dataProvider = new PageInsightsDataProvider($args);
    }

    public function isLocked()
    {
        $isLocked = false;

        if ($this->isTab('category')) {
            $isLocked = !Helper::isAddOnActive('data-plus') && Helper::isCustomTaxonomy(Request::get('tx', 'category'));
        }

        return $isLocked;
    }

    public function getContentsData()
    {
        return $this->dataProvider->getContentsData();
    }

    public function getCategoryData()
    {
        return $this->dataProvider->getCategoryData();
    }

    public function getAuthorData()
    {
        return $this->dataProvider->getAuthorsData();
    }

    public function render()
    {
        try {
            $template = $this->getCurrentTab();
            $data     = $this->getTabData();

            $filters = [];
            if ($this->isTab('contents')) {
                $filters = ['post-types', 'author'];
            } elseif ($this->isTab('category')) {
                $filters = ['taxonomy'];
            }

            $args = [
                'title'         => esc_html__('Page Insights', 'wp-statistics'),
                'pageName'      => Menus::get_page_slug('pages'),
                'custom_get'    => [
                    'tab'       => $this->getCurrentTab(),
                    'pt'        => Request::get('pt', ''),
                    'tx'        => Request::get('tx', 'category'),
                    'author_id' => Request::get('author_id', '', 'number'),
                    'order_by'  => Request::get('order_by'),
                    'order'     => Request::get('order'),
                ],
                'DateRang'      => Admin_Template::DateRange(),
                'hasDateRang'   => true,
                'showLockedPage'=> $this->isLocked(),
                'data'          => $data,
                'allTimeOption' => true,
                'filters'       => $filters,
                'pagination'    => Admin_Template::paginate_links([
                    'total' => isset($data['total']) ? $data['total'] : 0,
                    'echo'  => false
                ]),
                'tabs'          => [
                    [
                        'link'    => Menus::admin_url('pages', ['tab' => 'contents']),
                        'title'   => esc_html__('Contents', 'wp-statistics'),
                        'tooltip' => esc_html__('Shows visitor stats, views and word count for each content.', 'wp-statistics'),
                        'class'   => $this->isTab('contents') ? 'current' : '',
                    ],
                    [
                        'link'    => Menus::admin_url('pages', ['tab' => 'category']),
                        'title'   => esc_html__('Category Pages', 'wp-statistics'),
                        'tooltip' => esc_html__('Shows the page views for category pages related to the selected taxonomy.', 'wp-statistics'),
                        'class'   => $this->isTab('category') ? 'current' : '',
                    ],
                    [
                        'link'    => Menus::admin_url('pages', ['tab' => 'author']),
                        'title'   => esc_html__('Author Pages', 'wp-statistics'),
                        'tooltip' => esc_html__('View performance metrics for individual authors\' pages.', 'wp-statistics'),
                        'class'   => $this->isTab('author') ? 'current' : '',
                    ],
                    [
                        'link'        => Menus::admin_url('pages', ['tab' => '404']),
                        'title'       => esc_html__('404 Pages', 'wp-statistics'),
                        'class'       => $this->isTab('404') ? 'current' : '',
                        'coming_soon' => true
                    ]
                ]
            ];

            Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header'], $args);
            View::load("pages/page-insights/$template", $args);
            Admin_Template::get_template(['layout/postbox.hide', 'layout/footer'], $args);
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}