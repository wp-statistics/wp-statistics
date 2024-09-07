<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BaseChartResponseTrait;

class PublishOverviewChartDataProvider extends AbstractChartDataProvider
{
    use BaseChartResponseTrait;

    public $args;
    protected $postsModel;

    public function __construct($args)
    {
        $this->args = $args;

        $this->postsModel = new PostsModel();
    }

    public function getData()
    {
        // Get data from database
        $publishingData = $this->postsModel->countDailyPosts(array_merge($this->args, ['date' => DateRange::get('12months')]));

        // Parse and prepare data
        $parsedData     = $this->parseData($publishingData);
        $result         = $this->prepareResult($parsedData);

        return $result;
    }

    protected function parseData($data)
    {
        $publishingData = wp_list_pluck($data, 'posts', 'date');

        $today  = time();
        $date   = strtotime('-365 days');

        $parsedData = [];

        // Get number of posts published per day during last 365 days
        while ($date <= $today) {
            $currentDate    = date('Y-m-d', $date);
            $numberOfPosts  = isset($publishingData[$currentDate]) ? intval($publishingData[$currentDate]) : 0;

            $parsedData[] = [
                'x' => $currentDate, // date in Y-m-d format
                'y' => date('N', $date), // day of week
                'd' => date_i18n(Helper::getDefaultDateFormat(), strtotime($currentDate)), // date in default format
                'v' => $numberOfPosts // number of posts
            ];

            $date += DAY_IN_SECONDS;
        }

        return $parsedData;
    }

    protected function prepareResult($data)
    {
        $this->initChartData();

        $this->setChartDatasets($data);

        return $this->getChartData();
    }
}
