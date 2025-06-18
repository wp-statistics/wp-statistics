<?php

namespace WP_Statistics\Service\Admin\PageInsights\Views;

use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Abstracts\BaseTabView;
use WP_Statistics\Service\Admin\PageInsights\PageInsightsDataProvider;

class TabsView extends BaseTabView
{
    protected $defaultTab = 'overview';
    protected $tabs = [
        'overview',
        'top',
        'category',
        'author',
        '404',
    ];

    public function __construct()
    {
        $args = [
            'order'     => Request::get('order', 'DESC'),
            'author_id' => Request::get('author_id', '', 'number'),
            'url'       => Request::get('url', ''),
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

    public function getOverviewData()
    {
        return $this->dataProvider->getOverviewData();
    }

    public function getTopData()
    {
        return $this->dataProvider->getTopData();
    }

    public function getCategoryData()
    {
        return $this->dataProvider->getCategoryData();
    }

    public function getAuthorData()
    {
        return $this->dataProvider->getAuthorsData();
    }

    public function get404Data()
    {
        return $this->dataProvider->get404Data();
    }

    public function render()
    {
        $template    = $this->getCurrentTab();
        $data        = $this->getTabData();
        $queryParams = [
            'tab'       => $this->getCurrentTab(),
            'order_by'  => Request::get('order_by'),
            'order'     => Request::get('order')
        ];

        $filters = [];
        if ($this->isTab(['top', 'entry-pages', 'exit-pages'])) {
            $filters = ['post-types', 'page-insight'];

            $queryParams['pt']        = Request::get('pt', '');
            $queryParams['author_id'] = Request::get('author_id', '', 'number');
            $queryParams['url']       = Request::get('url', '');
        } elseif ($this->isTab('category')) {
            $filters = ['taxonomy'];

            $queryParams['tx'] = Request::get('tx', 'category');
        }

        $args = [
            'title'         => esc_html__('Page Insights', 'wp-statistics'),
            'pageName'      => Menus::get_page_slug('pages'),
            'custom_get'    => $queryParams,
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
                    'link'    => Menus::admin_url('pages', ['tab' => 'overview']),
                    'title'   => esc_html__('Overview', 'wp-statistics'),
                    'class'   => $this->isTab('overview') ? 'current' : '',
                ],
                [
                    'link'    => Menus::admin_url('pages', ['tab' => 'top']),
                    'title'   => esc_html__('Top Pages', 'wp-statistics'),
                    'tooltip' => esc_html__('Shows visitor stats, views and word count for each content.', 'wp-statistics'),
                    'class'   => $this->isTab('top') ? 'current' : '',
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
                    'link'      => Menus::admin_url('pages', ['tab' => '404']),
                    'title'     => esc_html__('404 Pages', 'wp-statistics'),
                    'class'     => $this->isTab('404') ? 'current' : '',
                    'tooltip'   => esc_html__('View URLs that led visitors to 404 errors.', 'wp-statistics'),
                ],
                [
                    'id'      => 'entry_pages',
                    'link'    => Menus::admin_url('pages', ['tab' => 'entry-pages']),
                    'title'   => esc_html__('Entry Pages', 'wp-statistics'),
                    'tooltip' => esc_html__('To view this report, you need to have the Data Plus add-on.', 'wp-statistics'),
                    'class'   => $this->isTab('entry-pages') ? 'current' : '',
                    'locked'  => !Helper::isAddOnActive('data-plus')
                ],
                [
                    'id'        => 'exit_pages',
                    'link'      => Menus::admin_url('pages', ['tab' => 'exit-pages']),
                    'title'     => esc_html__('Exit Pages', 'wp-statistics'),
                    'tooltip'   => esc_html__('To view this report, you need to have the Data Plus add-on.', 'wp-statistics'),
                    'class'     => $this->isTab('exit-pages') ? 'current' : '',
                    'locked'    => !Helper::isAddOnActive('data-plus')
                ],
            ]
        ];

        // If Data Plus is active, relocate array items
        if (Helper::isAddOnActive('data-plus')) {
            $tabs = $args['tabs'];

            $entryPage = $exitPage = null;

            foreach ($tabs as $key => $tab) {
                if (isset($tab['id']) && $tab['id'] === 'entry_pages') $entryPage = $key;
                if (isset($tab['id']) && $tab['id'] === 'exit_pages') $exitPage = $key;
            }

            // Relocate array items when Data Plus is active
            $tabs = Helper::relocateArrayItems($tabs, $entryPage, 2);
            $tabs = Helper::relocateArrayItems($tabs, $exitPage, 3);

            $args['tabs'] = $tabs;
        }

        Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header'], $args);
        View::load("pages/page-insights/$template", $args);
        do_action("wp_statistics_{$this->getCurrentPage()}_{$this->getCurrentTab()}_template", $args);
        Admin_Template::get_template(['layout/postbox.hide', 'layout/footer'], $args);
    }
}
