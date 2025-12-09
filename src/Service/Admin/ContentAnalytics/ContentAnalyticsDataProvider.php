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
                    'value'         => $posts,
                    'change'        => Helper::calculatePercentageChange($prevPosts, $posts),
                    'current_value' => $posts,
                    'prev_value'    => $prevPosts
                ],
                'views' => [
                    'value'         => $views,
                    'change'        => Helper::calculatePercentageChange($prevViews, $views),
                    'current_value' => $views,
                    'prev_value'    => $prevViews
                ],
                'visitors' => [
                    'value'         => $visitors,
                    'change'        => Helper::calculatePercentageChange($prevVisitors, $visitors),
                    'current_value' => $visitors,
                    'prev_value'    => $prevVisitors
                ],
                'comments' => [
                    'value'         => $comments,
                    'change'        => Helper::calculatePercentageChange($prevComments, $comments),
                    'current_value' => $comments,
                    'prev_value'    => $prevComments
                ],
                'comments_avg' => [
                    'value'         => Helper::divideNumbers($comments, $posts),
                    'change'        => Helper::calculatePercentageChange($prevAvgComments, $avgComments),
                    'current_value' => $avgComments,
                    'prev_value'    => $prevAvgComments
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
                    'value'         => $views,
                    'change'        => Helper::calculatePercentageChange($prevViews, $views),
                    'current_value' => $views,
                    'prev_value'    => $prevViews
                ],
                'visitors'  => [
                    'value'         => $visitors,
                    'change'        => Helper::calculatePercentageChange($prevVisitors, $visitors),
                    'current_value' => $visitors,
                    'prev_value'    => $prevVisitors
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

        // Exit rate: If visitors = 0, mark as not applicable
        $exitRateNotApplicable     = ($visitors == 0);
        $prevExitRateNotApplicable = ($prevVisitors == 0);
        $exitRate                  = $exitRateNotApplicable ? 0 : Helper::calculatePercentage($exitPages, $visitors);
        $prevExitRate              = $prevExitRateNotApplicable ? 0 : Helper::calculatePercentage($prevExitPages, $prevVisitors);

        $bounceRate     = $this->visitorsModel->getBounceRate($mappedArgs);
        $prevBounceRate = $this->visitorsModel->getBounceRate(array_merge($mappedArgs, ['date' => DateRange::getPrevPeriod()]));

        // Bounce rate: If visitors = 0, mark as not applicable
        $bounceRateNotApplicable     = ($visitors == 0);
        $prevBounceRateNotApplicable = ($prevVisitors == 0);

        $comments       = $this->postsModel->countComments($this->args);
        $prevComments   = $this->postsModel->countComments(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));

        $referrersData = $this->visitorsModel->getReferrers($mappedArgs);

        $summary = ChartDataProviderFactory::summaryChart(array_merge($mappedArgs, ['include_total' => true]))->getData();

        // Format bounce/exit rate values - remove unnecessary decimals
        $bounceRateFormatted = $bounceRateNotApplicable ? '–' : (floor($bounceRate) == $bounceRate ? (int) $bounceRate : $bounceRate) . '%';
        $exitRateFormatted   = $exitRateNotApplicable ? '–' : (floor($exitRate) == $exitRate ? (int) $exitRate : $exitRate) . '%';

        $result = [
            'visitors_country'  => $visitorsCountry,
            'summary'           => $summary,
            'referrers'         => $referrersData,
            'glance'            => [
                'views'     => [
                    'value'         => $views,
                    'change'        => Helper::calculatePercentageChange($prevViews, $views),
                    'current_value' => $views,
                    'prev_value'    => $prevViews
                ],
                'visitors'  => [
                    'value'         => $visitors,
                    'change'        => Helper::calculatePercentageChange($prevVisitors, $visitors),
                    'current_value' => $visitors,
                    'prev_value'    => $prevVisitors
                ],
                'entry_page' => [
                    'value'         => $entryPages,
                    'change'        => Helper::calculatePercentageChange($prevEntryPages, $entryPages),
                    'current_value' => $entryPages,
                    'prev_value'    => $prevEntryPages
                ],
                'exit_page' => [
                    'value'         => $exitPages,
                    'change'        => Helper::calculatePercentageChange($prevExitPages, $exitPages),
                    'current_value' => $exitPages,
                    'prev_value'    => $prevExitPages
                ],
                'bounce_rate' => [
                    'value'          => $bounceRateFormatted,
                    'change'         => ($bounceRateNotApplicable && $prevBounceRateNotApplicable) ? 0 : round($bounceRate - $prevBounceRate, 1),
                    'not_applicable' => $bounceRateNotApplicable,
                    'current_value'  => $bounceRateNotApplicable ? '–' : $bounceRate . '%',
                    'prev_value'     => $prevBounceRateNotApplicable ? '–' : $prevBounceRate . '%'
                ],
                'exit_rate' => [
                    'value'          => $exitRateFormatted,
                    'change'         => ($exitRateNotApplicable && $prevExitRateNotApplicable) ? 0 : round($exitRate - $prevExitRate, 1),
                    'not_applicable' => $exitRateNotApplicable,
                    'current_value'  => $exitRateNotApplicable ? '–' : $exitRate . '%',
                    'prev_value'     => $prevExitRateNotApplicable ? '–' : $prevExitRate . '%'
                ],
                'comments'  => [
                    'value'         => $comments,
                    'change'        => Helper::calculatePercentageChange($prevComments, $comments),
                    'current_value' => $comments,
                    'prev_value'    => $prevComments
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