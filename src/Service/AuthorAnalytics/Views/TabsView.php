<?php 

namespace WP_Statistics\Service\AuthorAnalytics\Views;

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\AuthorAnalytics\Data\AuthorsPerformanceData;
use WP_Statistics\Service\AuthorAnalytics\Data\AuthorsPagesData;
use InvalidArgumentException;

class TabsView
{
    private $tabs = [
        'performance' => AuthorsPerformanceData::class,
        'pages'       => AuthorsPagesData::class
    ];

    public function __construct()
    {
        // Throw error when invalid tab provided
        if (isset($_GET['tab']) && !array_key_exists($_GET['tab'], $this->tabs)) {
            throw new InvalidArgumentException(esc_html__('Invalid tab provided.', 'wp-statistics'));
        }
    }

    /**
     * Get performance tab data
     * 
     * @return array
     */
    public function getTabData($currentTab)
    {
        $args = [
            'from'      => isset($_GET['from']) ? sanitize_text_field($_GET['from']) : date('Y-m-d', strtotime('-1 month')),
            'to'        => isset($_GET['to']) ? sanitize_text_field($_GET['to']) : date('Y-m-d'),
            'post_type' => isset($_GET['pt']) ? sanitize_text_field($_GET['pt']) : Helper::get_list_post_type()
        ];

        if (!isset($this->tabs[$currentTab])) {
            throw new InvalidArgumentException('Tab does not have a data provider class.');
        }

        $dataProviderClass  = $this->tabs[$currentTab];

        /** @var stdClass */
        $data = new $dataProviderClass($args);

        return $data->get();
    }

    public function view()
    {
        $currentTab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'performance';
        $tabData    = $this->getTabData($currentTab);

        $args = [
            'title'      => esc_html__('Author Analytics', 'wp-statistics'),
            'tooltip'    => esc_html__('Page Tooltip', 'wp-statistics'),
            'pageName'   => Menus::get_page_slug('author-analytics'),
            'pagination' => Admin_Template::getCurrentPaged(),
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
            $args['filters'][] = 'author';
        }

        Admin_Template::get_template(['layout/header', 'layout/tabbed-page-header', "pages/author-analytics/authors-$currentTab", 'layout/postbox.hide', 'layout/footer'], $args);
    }
}