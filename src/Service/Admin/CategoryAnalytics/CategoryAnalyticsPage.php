<?php

namespace WP_Statistics\Service\Admin\CategoryAnalytics;

use WP_Statistics\Utils\Request;
use WP_Statistics\Abstracts\MultiViewPage;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Admin\CategoryAnalytics\Views\CategoryReportView;
use WP_Statistics\Service\Admin\CategoryAnalytics\Views\TabsView;
use WP_Statistics\Service\Admin\CategoryAnalytics\Views\SingleView;
use WP_Statistics\Service\Admin\FilterHandler\FilterGenerator;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

class CategoryAnalyticsPage extends MultiViewPage
{

    protected $pageSlug = 'category-analytics';
    protected $defaultView = 'tab';
    protected $views = [
        'tab'       => TabsView::class,
        'single'    => SingleView::class,
        'report'    => CategoryReportView::class
    ];

    public function __construct()
    {
        parent::__construct();

        $this->setFilters();
    }

    protected function setFilters() {
        $this->filters = FilterGenerator::create()
            ->dropdown('pt', [
                'label' => esc_html__('Post Type', 'wp-statistics'),
                'panel' => true,
                'attributes'  => [
                    'data-type' => 'post-types',
                    'data-source' => 'getPostTypes',
                ],
            ])
            ->dropdown('author_id', [
                'label' => esc_html__('Author', 'wp-statistics'),
                'panel' => true,
                'attributes'  => [
                    'data-type' => 'author',
                    'data-source' => 'getAuthor',
                ],
            ])
            ->dropdown('tx', [
                'label' => esc_html__('Taxonomy', 'wp-statistics'),
                'panel' => true,
                'attributes'  => [
                    'data-type' => 'taxonomy',
                    'data-source' => 'getTaxonomies',
                ],
            ])
            ->get();
        
        return $this->filters;
    }

    protected function init()
    {
        $this->disableScreenOption();
        $this->inaccurateDataNotice();
    }

    private function inaccurateDataNotice()
    {
        $taxPostTypes = Helper::getPostTypesByTaxonomy(Request::get('tx', 'category'));

        foreach ($taxPostTypes as $postType) {
            if (!post_type_supports($postType, 'author')) {
                $message = sprintf(
                    __('The post type of this category doesn’t support authors, affecting the accuracy of category performance data. To fix this, please enable author support for this post type. <a href="%s" target="_blank">Learn more</a>.', 'wp-statistics'),
                    'https://wp-statistics.com/resources/enabling-author-support-for-your-post-types/?utm_source=wp-statistics&utm_medium=link&utm_campaign=doc'
                );
        
                Notice::addNotice($message, 'inaccurate_data_notice', 'warning', false);
                break;
            }
        }
    }
}
