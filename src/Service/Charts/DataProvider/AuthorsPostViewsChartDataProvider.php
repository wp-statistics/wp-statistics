<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\AuthorsModel;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BaseChartResponseTrait;

class AuthorsPostViewsChartDataProvider extends AbstractChartDataProvider
{
    use BaseChartResponseTrait;

    protected $authorsModel;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->authorsModel = new AuthorsModel();
    }

    public function getData()
    {
        $topAuthorsByViews = $this->authorsModel->getAuthorsByViewsPerPost($this->args);

        $parsedData = $this->parseData($topAuthorsByViews);
        $data       = $this->prepareResult($parsedData);

        return $data;
    }

    protected function parseData($data)
    {
        $parsedData = [];

        if ($data) {
            foreach ($data as $author) {
                $parsedData[] = [
                    'x'      => $author->total_views,
                    'y'      => $author->total_posts,
                    'img'    => esc_url(get_avatar_url($author->id)),
                    'author' => esc_html($author->name)
                ];
            }
        }

        return $parsedData;
    }

    protected function prepareResult($data)
    {
        $this->initChartData();

        $this->setChartDatasets($data);
        $this->setChartLabels([
            'chart' => sprintf(esc_html__('Views/Published %s', 'wp-statistics'),Helper::getPostTypeName($this->args['post_type'])),
            'yAxis' => sprintf(esc_html__('Published %s', 'wp-statistics'), Helper::getPostTypeName($this->args['post_type'])),
            'xAxis' => sprintf(esc_html__('%s Views', 'wp-statistics'), Helper::getPostTypeName($this->args['post_type'], true))
        ]);

        return $this->getChartData();
    }
}
