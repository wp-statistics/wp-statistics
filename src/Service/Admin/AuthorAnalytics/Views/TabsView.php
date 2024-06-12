<?php

namespace WP_Statistics\Service\Admin\AuthorAnalytics\Views;

use WP_Statistics\Abstracts\BaseTabView;
use WP_STATISTICS\Admin_Assets;
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\AuthorAnalytics\AuthorAnalyticsDataProvider;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Utils\Request;

class TabsView extends BaseTabView
{
    protected $dataProvider = AuthorAnalyticsDataProvider::class;
    protected $defaultTab = 'performance';
    protected $tabs = [
        'performance',
        'pages'
    ];

    public function getPerformanceData()
    {
        $from       = Request::get('from', date('Y-m-d', strtotime('-1 month')));
        $to         = Request::get('to', date('Y-m-d'));
        $postType   = Request::get('pt', 'post');

        $args = [
            'date'      => ['from' => $from, 'to' => $to],
            'post_type' => $postType,
        ];

        $dataProvider = new $this->dataProvider($args);

        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Author_Analytics_Object', [
            'publish_chart_data'         => $dataProvider->getPublishingChartData(),
            'views_per_posts_chart_data' => [
                'data'       => $dataProvider->getViewsPerPostsChartData(),
                'chartLabel' => sprintf(
                    esc_html__('Views/Published %s', 'wp-statistics'),
                    Helper::getPostTypeName($postType)
                ),
                'yAxisLabel' => sprintf(
                    esc_html__('Published %s', 'wp-statistics'),
                    Helper::getPostTypeName($postType)
                ),
                'xAxisLabel' => sprintf(
                    esc_html__('%s Views', 'wp-statistics'),
                    Helper::getPostTypeName($postType, true)
                )
            ]
        ]);

        return $dataProvider->getAuthorsPerformanceData();
    }

    public function getPagesData()
    {
        $from       = Request::get('from', date('Y-m-d', strtotime('-1 month')));
        $to         = Request::get('to', date('Y-m-d'));
        $postType   = Request::get('pt', 'post');
        $orderBy    = Request::get('order_by');
        $order      = Request::get('order', 'DESC');

        $args = [
            'date'      => ['from' => $from, 'to' => $to],
            'post_type' => $postType,
            'per_page'  => Admin_Template::$item_per_page,
            'page'      => Admin_Template::getCurrentPaged()
        ];

        if ($orderBy) {
            $args['order_by'] = $orderBy;
            $args['order']    = $order;
        }

        $dataProvider = new $this->dataProvider($args);
        return $dataProvider->getAuthorsPagesData();
    }

    public function render()
    {
        try {
            $currentTab = $this->getCurrentTab();
            $tabData    = $this->getTabData();

            $args = [
                'title'       => esc_html__('Author Analytics', 'wp-statistics'),
                'pageName'    => Menus::get_page_slug('author-analytics'),
                'paged'       => Admin_Template::getCurrentPaged(),
                'custom_get'  => ['tab' => $currentTab],
                'DateRang'    => Admin_Template::DateRange(),
                'hasDateRang' => true,
                'filters'     => ['post-type'],
                'data'        => $tabData,
                'tabs'        => [
                    [
                        'link'    => Menus::admin_url('author-analytics', ['tab' => 'performance']),
                        'title'   => esc_html__('Authors Performance', 'wp-statistics'),
                        'tooltip' => esc_html__('The Author Performance page provides insights into the contributions and impact of each author. Use this information to evaluate author productivity and engagement.', 'wp-statistics'),
                        'class'   => $currentTab === 'performance' ? 'current' : '',
                    ],
                    [
                        'link'    => Menus::admin_url('author-analytics', ['tab' => 'pages']),
                        'title'   => esc_html__('Author Pages', 'wp-statistics'),
                        'tooltip' => esc_html__('View performance metrics for individual authors\' pages.', 'wp-statistics'),
                        'class'   => $currentTab === 'pages' ? 'current' : '',
                    ]
                ],
            ];

            if ($currentTab === 'performance') {
                $args['custom_get']['pt'] = Request::get('pt', 'post');
            }

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
        } catch (\Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}