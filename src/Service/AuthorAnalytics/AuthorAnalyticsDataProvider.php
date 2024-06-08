<?php 

namespace WP_Statistics\Service\AuthorAnalytics;

use WP_STATISTICS\Country;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\PagesModel;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\TaxonomyModel;
use WP_Statistics\Models\VisitorsModel;

class AuthorAnalyticsDataProvider
{
    protected $args;
    protected $authorModel;
    protected $pagesModel;
    protected $postsModel;
    protected $visitorsModel;
    protected $taxonomyModel;

    
    public function __construct($args)
    {
        $this->args = $args;

        $this->authorModel   = new AuthorsModel();
        $this->pagesModel    = new PagesModel();
        $this->postsModel    = new PostsModel();
        $this->visitorsModel = new VisitorsModel();
        $this->taxonomyModel = new TaxonomyModel();
    }

    public function getViewsPerPostsChartData()
    {
        $args               = array_merge($this->args, ['per_page' => -1]);
        $topAuthorsByViews  = $this->authorModel->getAuthorsByViewsPerPost($args);

        $data = [];
        
        if ($topAuthorsByViews) {
            foreach ($topAuthorsByViews as $author) {
                $data[] = [
                    'x'     => $author->total_views,  
                    'y'     => $author->total_posts,  
                    'img'   => esc_url(get_avatar_url($author->id))
                ];
            }
        }

        return $data;
    }

    public function getPublishingChartData()
    {
        // Just filter by post type
        $args = Helper::filterArrayByKeys($this->args, ['post_type', 'author_id']);

        $publishingData = $this->postsModel->getPostPublishOverview($args);
        $publishingData = wp_list_pluck($publishingData, 'posts', 'date');

        $today  = time();
        $date   = strtotime('-365 days');

        // Get number of posts published per day during last 365 days
        while ($date <= $today) {
            $currentDate    = date('Y-m-d', $date);
            $numberOfPosts  = isset($publishingData[$currentDate]) ? intval($publishingData[$currentDate]) : 0;

            $data[] = [
                'x' => $currentDate,
                'y' => date('N', $date),
                'd' => $currentDate,
                'v' => $numberOfPosts
            ];
    
            $date += 86400;
        }

        return $data;
    }

    /**
     * Organize and count visitors by platform, agent, and country.
     *
     * @param array $args 
     * @return array $result
     */
    public function getParsedVisitorsData($args)
    {
        $data   = $this->visitorsModel->getVisitors();
        $result = [
            'platform'  => [],
            'agent'     => [],
            'country'   => []
        ];

        if (!empty($data)) {
            foreach ($data as $item) {
                if (empty($result['platform'][$item->platform])) {
                    $result['platform'][$item->platform] = 1;
                } else {
                    $result['platform'][$item->platform]++;
                }
    
                if (empty($result['agent'][$item->agent])) {
                    $result['agent'][$item->agent] = 1;
                } else {
                    $result['agent'][$item->agent]++;
                }
    
                if (empty($result['country'][$item->location])) {
                    $result['country'][$item->location] = 1;
                } else {
                    $result['country'][$item->location]++;
                }
            }
    
            // Sort and limit country
            arsort($result['country']);
            $result['country'] = array_slice($result['country'], 0, 10);
        }

        return $result;
    }

    public function getAuthorsPerformanceData()
    {
        // Authors data
        $totalAuthors         = $this->authorModel->countAuthors();
        $activeAuthors        = $this->authorModel->countAuthors($this->args);
        $topPublishingAuthors = $this->authorModel->getAuthorsByPostPublishes($this->args);
        $topViewingAuthors    = $this->authorModel->getTopViewingAuthors($this->args);
        $topAuthorsByComment  = $this->authorModel->getAuthorsByCommentsPerPost($this->args);
        $topAuthorsByViews    = $this->authorModel->getAuthorsByViewsPerPost($this->args);
        $topAuthorsByWords    = $this->authorModel->getAuthorsByWordsPerPost($this->args);

        // Views data
        $totalViews           = $this->pagesModel->countViews($this->args);

        // Posts data
        $totalWords           = $this->postsModel->countWords($this->args);
        $totalComments        = $this->postsModel->countComments($this->args);
        $totalPosts           = $this->postsModel->countPosts($this->args);

        return [
            'authors' => [
                'total'             => $totalAuthors,
                'active'            => $activeAuthors,
                'avg'               => Helper::divideNumbers($totalPosts, $activeAuthors),
                'top_publishing'    => $topPublishingAuthors,
                'top_viewing'       => $topViewingAuthors,
                'top_by_comments'   => $topAuthorsByComment,
                'top_by_views'      => $topAuthorsByViews,
                'top_by_words'      => $topAuthorsByWords
            ],
            'views'   => [
                'total' => $totalViews,
                'avg'   => Helper::divideNumbers($totalViews, $totalPosts)
            ],
            'posts'   => [
                'words'     => [
                    'total' => $totalWords,
                    'avg'   => Helper::divideNumbers($totalWords, $totalPosts)
                ],
                'comments'  => [
                    'total' => $totalComments,
                    'avg'   => Helper::divideNumbers($totalComments, $totalPosts)
                ]
            ]
        ];
    }

    public function getAuthorsPagesData()
    {
        $authors = $this->authorModel->getAuthorsByViewsPerPost($this->args);
        $total   = $this->authorModel->countAuthors($this->args);

        return [
            'authors' => $authors,
            'total'   => $total
        ];
    }


    public function getAuthorsReportData()
    {
        $authors = $this->authorModel->getAuthorsPerformanceData($this->args);
        $total   = $this->authorModel->countAuthors($this->args);

        return [
            'authors'   => $authors,
            'total'     => $total
        ];
    }

    public function getAuthorsPostsData()
    {
        $posts  = $this->postsModel->getPostsReportData($this->args);
        $total  = $this->postsModel->countPosts($this->args);

        return [
            'posts'   => $posts,
            'total'   => $total
        ];
    }

    public function getVisitSummary()
    {
        return [
            'today'     => [
                'label'     => esc_html__('Today', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => 'today'])),
                'views'     => $this->pagesModel->countViews(array_merge($this->args, ['date' => 'today'])),
            ],
            'yesterday' => [
                'label'     => esc_html__('Yesterday', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => 'yesterday'])),
                'views'     => $this->pagesModel->countViews(array_merge($this->args, ['date' => 'yesterday'])),
            ],
            '7days'     => [
                'label'     => esc_html__('Last 7 days', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => '7days'])),
                'views'     => $this->pagesModel->countViews(array_merge($this->args, ['date' => '7days'])),
            ],
            '30days'    => [
                'label'     => esc_html__('Last 30 days', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => '30days'])),
                'views'     => $this->pagesModel->countViews(array_merge($this->args, ['date' => '30days'])),
            ],
            '60days'    => [
                'label'     => esc_html__('Last 60 days', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => '60days'])),
                'views'     => $this->pagesModel->countViews(array_merge($this->args, ['date' => '60days'])),
            ],
            '120days'   => [
                'label'     => esc_html__('Last 120 days', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => '120days'])),
                'views'     => $this->pagesModel->countViews(array_merge($this->args, ['date' => '120days'])),
            ],
            'year'      => [
                'label'     => esc_html__('Last 12 months', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => 'year'])),
                'views'     => $this->pagesModel->countViews(array_merge($this->args, ['date' => 'year'])),
            ],
            'this_year' => [
                'label'     => esc_html__('This year (Jan - Today)', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => 'this_year'])),
                'views'     => $this->pagesModel->countViews(array_merge($this->args, ['date' => 'this_year'])),
            ],
            'last_year' => [
                'label'     => esc_html__('Last Year', 'wp-statistics'),
                'visitors'  => $this->visitorsModel->countVisitors(array_merge($this->args, ['date' => 'last_year'])),
                'views'     => $this->pagesModel->countViews(array_merge($this->args, ['date' => 'last_year'])),
            ],
        ];
    }

    public function getAuthorSingleData()
    {
        $totalViews         = $this->pagesModel->countViews($this->args);

        $totalWords         = $this->postsModel->countWords($this->args);
        $totalComments      = $this->postsModel->countComments($this->args);
        $totalPosts         = $this->postsModel->countPosts($this->args);
        $totalVisitors      = $this->visitorsModel->countVisitors($this->args);

        $taxonomies         = $this->taxonomyModel->countTaxonomiesPosts($this->args);
        $topPostsByView     = $this->postsModel->getPostsViewsData($this->args);
        $topPostsByComment  = $this->postsModel->getPostsCommentsData($this->args);
        $topPostsByWords    = $this->postsModel->getPostsWordsData($this->args);

        $visitorsData       = $this->getParsedVisitorsData($this->args);
        $visitsData         = $this->getVisitSummary();

        $data = [
            'visit_summary' => $visitsData,
            'visitors_data' => $visitorsData,
            'taxonomies'    => $taxonomies,
            'overview'      => [
                'posts'         => [
                    'total'     => $totalPosts
                ],
                'views'     => [
                    'total'     => $totalViews,
                    'avg'       => Helper::divideNumbers($totalViews, $totalPosts)
                ],
                'visitors'  => [
                    'total'     => $totalVisitors,
                    'avg'       => Helper::divideNumbers($totalVisitors, $totalPosts)
                ],
                'words'     => [
                    'total'     => $totalWords,
                    'avg'       => Helper::divideNumbers($totalWords, $totalPosts)
                ],
                'comments'  => [
                    'total'     => $totalComments,
                    'avg'       => Helper::divideNumbers($totalComments, $totalPosts)
                ]
            ],
            'posts'         => [
                'top_views'     => $topPostsByView,
                'top_comments'  => $topPostsByComment,
                'top_words'     => $topPostsByWords
            ]
        ];

        return $data;
    }
}