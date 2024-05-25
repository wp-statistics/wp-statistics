<?php 

namespace WP_Statistics\Service\AuthorAnalytics\Data;

use WP_STATISTICS\Admin_Assets;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Models\PagesModel;
use WP_Statistics\Models\PostsModel;

class AuthorsPerformanceData
{
    protected $args;
    protected $authorModel;
    protected $pagesModel;
    protected $postsModel;

    
    public function __construct($args)
    {
        $this->args = $args;

        $this->authorModel  = new AuthorsModel();
        $this->pagesModel   = new PagesModel();
        $this->postsModel   = new PostsModel();

        $this->localizeData();
    }

    public function localizeData()
    {
        wp_localize_script(Admin_Assets::$prefix, 'Wp_Statistics_Author_Analytics_Object', [
            'publish_overview_chart_data' => $this->generatePublishingChartData()
        ]);
    }

    protected function generatePublishingChartData()
    {
        $publishingData = $this->postsModel->publishOverview(array_intersect_key($this->args, ['post_type']));
        $publishingData = array_combine(array_column($publishingData, 'date'), array_column($publishingData, 'posts'));

        $end    = time();
        $date  = strtotime('-365 days');

        // Get number of posts published per day during last 365 days
        while ($date <= $end) {
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

    public function get()
    {
        $totalAuthors    = $this->authorModel->countAuthors();
        $activeAuthors   = $this->authorModel->countAuthors($this->args);
        $totalPosts      = $this->postsModel->countPosts($this->args);
        $totalWords      = $this->postsModel->countWords($this->args);
        $totalComments   = $this->postsModel->countComments($this->args);
        $totalViews      = $this->pagesModel->countViews($this->args);

        return [
            'authors' => [
                'total' => $totalAuthors,
                'active'=> $activeAuthors,
                'avg'   => $totalPosts / $activeAuthors
            ],
            'views'   => [
                'total' => $totalViews,
                'avg'   => $totalViews / $totalPosts
            ],
            'posts'   => [
                'words'     => [
                    'total' => $totalWords,
                    'avg'   => $totalWords / $totalPosts
                ],
                'comments'  => [
                    'total' => $totalComments,
                    'avg'   => $totalComments / $totalPosts
                ]
            ]
        ];
    }

}