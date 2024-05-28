<?php 

namespace WP_Statistics\Service\AuthorAnalytics\Views;

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\AuthorAnalytics\AuthorAnalyticsData;
use InvalidArgumentException;

class TabsView
{
    private $tabs = [
        'performance',
        'pages'
    ];

    public function __construct()
    {
        // Throw error when invalid tab provided
        if (isset($_GET['tab']) && !in_array($_GET['tab'], $this->tabs)) {
            throw new InvalidArgumentException(esc_html__('Invalid tab provided.', 'wp-statistics'));
        }
    }

    /**
     * Get performance tab data
     * 
     * @return array
     */
    public function getData()
    {
        $currentTab = $this->getCurrentTab();

        $args = [
            'from'          => isset($_GET['from']) ? sanitize_text_field($_GET['from']) : date('Y-m-d', strtotime('-1 month')),
            'to'            => isset($_GET['to']) ? sanitize_text_field($_GET['to']) : date('Y-m-d'),
            'post_type'     => isset($_GET['pt']) ? sanitize_text_field($_GET['pt']) : Helper::get_list_post_type(),
            'tab'           => $currentTab
        ];

        if ($currentTab == 'pages') {
            $args['per_page']   = Admin_Template::$item_per_page;
            $args['page']       = Admin_Template::getCurrentPaged();
        }

        if (isset($_GET['order_by'])) {
            $args['order_by'] = sanitize_text_field($_GET['order_by']);
        } 
        
        if (isset($_GET['order'])) {
            $args['order'] = sanitize_text_field($_GET['order']);
        }

        $authorAnalyticsData  = new AuthorAnalyticsData($args);

        $dataMethod = 'authors' . ucfirst($currentTab) . 'Data';

        return $authorAnalyticsData->$dataMethod();
    }

    public function getCurrentTab()
    {
        return isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'performance';
    }

    public function view()
    {
        $currentTab = $this->getCurrentTab();
        $tabData    = $this->getData();

        $args = [
            'title'      => esc_html__('Author Analytics', 'wp-statistics'),
            'tooltip'    => esc_html__('Page Tooltip', 'wp-statistics'),
            'pageName'   => Menus::get_page_slug('author-analytics'),
            'paged'      => Admin_Template::getCurrentPaged(),
            'custom_get' => ['tab' => $currentTab],
            'DateRang'   => Admin_Template::DateRange(),
            'filters'    => ['post-type'],
            'data'       => $tabData,
            'tabs'       => [
                [
                    'link'    => Menus::admin_url('author-analytics', ['tab' => 'performance']),
                    'title'   => esc_html__('Authors Performance', 'wp-statistics'),
                    'tooltip' => esc_html__('Tab Tooltip', 'wp-statistics'),
                    'class'   => $currentTab === 'performance' ? 'current' : '',
                ],
                [
                    'link'    => Menus::admin_url('author-analytics', ['tab' => 'pages']),
                    'title'   => esc_html__('Author Pages', 'wp-statistics'),
                    'tooltip' => esc_html__('Tab Tooltip', 'wp-statistics'),
                    'class'   => $currentTab === 'pages' ? 'current' : '',
                ]
            ],
        ];

        if ($currentTab === 'pages') {
            // Remove filters from pages tab
            unset($args['filters']);
            
            // Add pagination to the pages tab
            if ($tabData['total'] > 0) {
                $args['total'] = $tabData['total'];

                $args['pagination'] = Admin_Template::paginate_links([
                    'total' => $tabData['total'],
                    'echo'  => false
                ]);
            }
        }

        Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/author-analytics/authors-$currentTab", 'layout/postbox.hide', 'layout/footer'], $args);
    }
}