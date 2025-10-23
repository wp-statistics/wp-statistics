<?php
namespace WP_Statistics\Service\Admin\ContentAnalytics;

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Admin\Posts\WordCountService;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;
use WP_Statistics\Utils\Request;

class ContentAnalyticsDataProvider
{
    protected $args;
    private $postsModel;
    private $viewsModel;
    private $visitorsModel;
    private $taxonomyModel;

    public function __construct($args)
    {
        $this->args = $args;

        $this->postsModel       = new PostsModel();
        $this->viewsModel       = new ViewsModel();
        $this->visitorsModel    = new VisitorsModel();
        $this->taxonomyModel    = new TaxonomyModel();
    }

    public function getChartsData()
    {
        $args = Helper::mapArrayKeys($this->args, [
            'post_id'   => 'resource_id',
            'post_type' => 'resource_type'
        ]);

        $performanceDataProvider  = ChartDataProviderFactory::performanceChart($args);
        $searchEngineDataProvider = ChartDataProviderFactory::searchEngineChart(array_merge($args, ['source_channel' => ['search', 'paid_search']]));
        $platformDataProvider     = ChartDataProviderFactory::platformCharts($args);

        return [
            'post_type'                 => Helper::getPostTypeName(Request::get('tab', 'post')),
            'performance_chart_data'    => $performanceDataProvider->getData(),
            'search_engine_chart_data'  => $searchEngineDataProvider->getData(),
            'os_chart_data'             => $platformDataProvider->getOsData(),
            'browser_chart_data'        => $platformDataProvider->getBrowserData(),
            'device_chart_data'         => $platformDataProvider->getDeviceData(),
            'model_chart_data'          => $platformDataProvider->getModelData(),
        ];
    }

    public function getPostTypeData()
    {
        $mappedArgs = Helper::mapArrayKeys($this->args, [
            'post_type' => 'resource_type'
        ]);

        $posts     = $this->postsModel->countPosts($this->args);
        $prevPosts = $this->postsModel->countPosts(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));

        $visitors     = $this->visitorsModel->countVisitors($mappedArgs);
        $prevVisitors = $this->visitorsModel->countVisitors(array_merge($mappedArgs, ['date' => DateRange::getPrevPeriod()]));

        $views     = $this->viewsModel->countViews($mappedArgs);
        $prevViews = $this->viewsModel->countViews(array_merge($mappedArgs, ['date' => DateRange::getPrevPeriod()]));

        $comments        = $this->postsModel->countComments($this->args);
        $prevComments    = $this->postsModel->countComments(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));
        $avgComments     = Helper::divideNumbers($comments, $posts);
        $prevAvgComments = Helper::divideNumbers($prevComments, $prevPosts);

        $visitorsCountry = $this->visitorsModel->getVisitorsGeoData(array_merge($mappedArgs, ['per_page' => 10]));

        $summary = ChartDataProviderFactory::summaryChart($mappedArgs)->getData();

        $referrersData = $this->visitorsModel->getReferrers($mappedArgs);

        $topPostsByView     = $this->postsModel->getPostsViewsData($this->args);
        $topPostsByComment  = $this->postsModel->getPostsCommentsData($this->args);
        $recentPostsData    = $this->postsModel->getPostsViewsData(array_merge($this->args, ['order_by' => 'post_date', 'show_no_views' => true]));

        $taxonomies         = $this->taxonomyModel->getTaxonomiesData($this->args);

        $result = [
            'glance' => [
                'posts' => [
                    'value'  => $posts,
                    'change' => Helper::calculatePercentageChange($prevPosts, $posts)
                ],
                'views' => [
                    'value'  => $views,
                    'change' => Helper::calculatePercentageChange($prevViews, $views)
                ],
                'visitors' => [
                    'value'  => $visitors,
                    'change' => Helper::calculatePercentageChange($prevVisitors, $visitors)
                ],
                'comments' => [
                    'value'  => $comments,
                    'change' => Helper::calculatePercentageChange($prevComments, $comments),
                ],
                'comments_avg' => [
                    'value'  => Helper::divideNumbers($comments, $posts),
                    'change' => Helper::calculatePercentageChange($prevAvgComments, $avgComments)
                ]
            ],
            'summary'           => $summary,
            'taxonomies'        => $taxonomies,
            'visitors_country'  => $visitorsCountry,
            'referrers'         => $referrersData,
            'posts'             => [
                'top_viewing'   => $topPostsByView,
                'top_commented' => $topPostsByComment,
                'recent'        => $recentPostsData
            ]
        ];

        if (WordCountService::isActive()) {
            $words    = $this->postsModel->countWords($this->args);
            $avgWords = Helper::divideNumbers($words, $posts);

            $result['glance']['words'] = [
                'value' => $words
            ];

            $result['glance']['words_avg'] = [
                'value' => $avgWords
            ];
        }

        return $result;
    }

    public function getSingleResourceData()
    {
        $views     = $this->viewsModel->countViews($this->args);
        $prevViews = $this->viewsModel->countViews(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));

        $visitors     = $this->visitorsModel->countVisitors($this->args);
        $prevVisitors = $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));

        $visitorsCountry = $this->visitorsModel->getVisitorsGeoData(array_merge($this->args, ['per_page' => 10]));

        $summary = ChartDataProviderFactory::summaryChart(array_merge($this->args, ['include_total' => true]))->getData();

        $referrersData = $this->visitorsModel->getReferrers($this->args);

        return [
            'visitors_country'  => $visitorsCountry,
            'summary'           => $summary,
            'referrers'         => $referrersData,
            'glance'            => [
                'views'     => [
                    'value'  => $views,
                    'change' => Helper::calculatePercentageChange($prevViews, $views)
                ],
                'visitors'  => [
                    'value'  => $visitors,
                    'change' => Helper::calculatePercentageChange($prevVisitors, $visitors)
                ]
            ]
        ];
    }

    public function getSinglePostData()
    {
        $mappedArgs = Helper::mapArrayKeys($this->args, [
            'post_id' => 'resource_id'
        ]);

        $views     = $this->viewsModel->countViews($mappedArgs);
        $prevViews = $this->viewsModel->countViews(array_merge($mappedArgs, ['date' => DateRange::getPrevPeriod()]));

        $visitors        = $this->visitorsModel->countVisitors($mappedArgs);
        $prevVisitors    = $this->visitorsModel->countVisitors(array_merge($mappedArgs, ['date' => DateRange::getPrevPeriod()]));
        $visitorsCountry = $this->visitorsModel->getVisitorsGeoData(array_merge($mappedArgs, ['per_page' => 10]));

        $entryPages     = $this->visitorsModel->countEntryPageVisitors($mappedArgs);
        $prevEntryPages = $this->visitorsModel->countEntryPageVisitors(array_merge($mappedArgs, ['date' => DateRange::getPrevPeriod()]));

        $exitPages     = $this->visitorsModel->countExitPageVisitors($mappedArgs);
        $prevExitPages = $this->visitorsModel->countExitPageVisitors(array_merge($mappedArgs, ['date' => DateRange::getPrevPeriod()]));
        $exitRate      = Helper::calculatePercentage($exitPages, $visitors);
        $prevExitRate  = Helper::calculatePercentage($prevExitPages, $prevVisitors);

        $bounceRate     = $this->visitorsModel->getBounceRate($mappedArgs);
        $prevBounceRate = $this->visitorsModel->getBounceRate(array_merge($mappedArgs, ['date' => DateRange::getPrevPeriod()]));

        $comments       = $this->postsModel->countComments($this->args);
        $prevComments   = $this->postsModel->countComments(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));

        $referrersData = $this->visitorsModel->getReferrers($mappedArgs);

        $summary = ChartDataProviderFactory::summaryChart(array_merge($mappedArgs, ['include_total' => true]))->getData();

        $result = [
            'visitors_country'  => $visitorsCountry,
            'summary'           => $summary,
            'referrers'         => $referrersData,
            'glance'            => [
                'views'     => [
                    'value'  => $views,
                    'change' => Helper::calculatePercentageChange($prevViews, $views)
                ],
                'visitors'  => [
                    'value'  => $visitors,
                    'change' => Helper::calculatePercentageChange($prevVisitors, $visitors)
                ],
                'entry_page' => [
                    'value'  => $entryPages,
                    'change' => Helper::calculatePercentageChange($prevEntryPages, $entryPages)
                ],
                'exit_page' => [
                    'value'  => $exitPages,
                    'change' => Helper::calculatePercentageChange($prevExitPages, $exitPages)
                ],
                'bounce_rate' => [
                    'value'  => $bounceRate . '%',
                    'change' => round($bounceRate - $prevBounceRate, 1)
                ],
                'exit_rate' => [
                    'value'  => $exitRate . '%',
                    'change' => round($exitRate - $prevExitRate, 1)
                ],
                'comments'  => [
                    'value'  => $comments,
                    'change' => Helper::calculatePercentageChange($prevComments, $comments),
                ]
            ]
        ];

        if (WordCountService::isActive()) {
            $totalWords = $this->postsModel->countWords($this->args);

            $result['glance']['words'] = [
                'value' => $totalWords
            ];
        }

        return $result;
    }
}