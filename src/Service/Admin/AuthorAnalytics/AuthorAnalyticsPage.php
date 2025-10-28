<?php

namespace WP_Statistics\Service\Admin\AuthorAnalytics;

use WP_Statistics\Abstracts\MultiViewPage;
use WP_Statistics\Service\Admin\AuthorAnalytics\Views\AuthorsView;
use WP_Statistics\Service\Admin\AuthorAnalytics\Views\PerformanceView;
use WP_Statistics\Service\Admin\AuthorAnalytics\Views\SingleAuthorView;
use WP_Statistics\Service\Admin\FilterHandler\FilterGenerator;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Admin\Posts\WordCountService;

class AuthorAnalyticsPage extends MultiViewPage
{
    protected $pageSlug = 'author-analytics';

    protected $defaultView = 'performance';

    protected $views = [
        'performance'   => PerformanceView::class,
        'authors'       => AuthorsView::class,
        'single-author' => SingleAuthorView::class
    ];

    /**
     * @var WordCountService
     */
    private $wordsCount;

    public function __construct()
    {
        parent::__construct();

        $this->setFilters();
    }

    protected function setFilters() {
        $this->filters = FilterGenerator::create()
            ->dropdown('pt', [
                'label'      => esc_html__('Post Type', 'wp-statistics'),
                'panel'      => true,
                'attributes' => [
                    'data-type'    => 'post-type',
                    'data-default' => ''
                ],
                'predefined' => self::getPostTypes()
            ])
            ->get();

        return $this->filters;
    }

    public function getPostTypes()
    {
        $args = [];

        $postTypes = Helper::get_list_post_type();

        $queryKey   = 'pt';
        $baseUrl    = htmlspecialchars_decode(esc_url(remove_query_arg([$queryKey])));

        foreach ($postTypes as $postType) {
            $args[] = [
                'slug'    => esc_html($postType),
                'name'    => esc_html(Helper::getPostTypeName($postType)),
                'url'     => add_query_arg([$queryKey => $postType], $baseUrl),
                'premium' => Helper::isCustomPostType($postType) && !Helper::isAddOnActive('data-plus')
            ];
        }

        return [
            'args'                => $args,
            'baseUrl'             => $baseUrl,
            'selectedOption'      => Request::get($queryKey, 'post'),
            'lockCustomPostTypes' => !Helper::isAddOnActive('data-plus')
        ];
    }

    protected function init()
    {
        $this->wordsCount = new WordCountService();

        $this->disableScreenOption();
        $this->inaccurateDataNotice();
    }

    private function inaccurateDataNotice()
    {
        $postType = Request::get('pt', 'post');

        if (!post_type_supports($postType, 'author')) {
            $message = sprintf(
                __('This post type doesn’t support authors, affecting the accuracy of author performance data. To fix this, please enable author support for this post type. <a href="%s" target="_blank">Learn more</a>.', 'wp-statistics'),
                'https://wp-statistics.com/resources/enabling-author-support-for-your-post-types/?utm_source=wp-statistics&utm_medium=link&utm_campaign=doc'
            );

            Notice::addNotice($message, 'inaccurate_data_notice', 'warning', false);
        }
    }
}
