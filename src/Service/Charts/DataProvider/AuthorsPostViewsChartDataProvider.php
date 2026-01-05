<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_STATISTICS\Helper;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BaseChartResponseTrait;

class AuthorsPostViewsChartDataProvider extends AbstractChartDataProvider
{
    use BaseChartResponseTrait;

    /**
     * @var AnalyticsQueryHandler
     */
    protected $queryHandler;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->queryHandler = new AnalyticsQueryHandler();
    }

    public function getData()
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['views'],
            'group_by'  => ['author'],
            'date_from' => $this->args['date']['from'] ?? null,
            'date_to'   => $this->args['date']['to'] ?? null,
            'filters'   => $this->getFilters(),
            'format'    => 'table',
            'per_page'  => $this->args['per_page'] ?? 5,
            'order_by'  => 'views',
            'order'     => 'DESC',
        ]);

        $parsedData = $this->parseData($result['data']['rows'] ?? []);
        $data       = $this->prepareResult($parsedData);

        return $data;
    }

    /**
     * Build filters array from args.
     *
     * @return array
     */
    protected function getFilters()
    {
        $filters = [];

        if (!empty($this->args['post_type'])) {
            $filters['post_type'] = $this->args['post_type'];
        }

        return $filters;
    }

    protected function parseData($data)
    {
        $parsedData = [];

        if ($data) {
            foreach ($data as $author) {
                $authorId = $author['author_id'] ?? 0;
                $parsedData[] = [
                    'x'      => $author['views'] ?? 0,
                    'y'      => $this->getAuthorPostCount($authorId),
                    'img'    => esc_url(get_avatar_url($authorId)),
                    'author' => esc_html($author['author_name'] ?? '')
                ];
            }
        }

        return $parsedData;
    }

    /**
     * Get the number of posts for an author.
     *
     * @param int $authorId Author ID.
     * @return int
     */
    protected function getAuthorPostCount($authorId)
    {
        if (empty($authorId)) {
            return 0;
        }

        $postTypes = $this->args['post_type'] ?? Helper::get_list_post_type();

        return (int) count_user_posts($authorId, $postTypes, true);
    }

    protected function prepareResult($data)
    {
        $this->initChartData();

        $this->setChartDatasets($data);
        $this->setChartLabels([
            'chart' => sprintf(esc_html__('Views/Published %s', 'wp-statistics'), Helper::getPostTypeName($this->args['post_type'] ?? '')),
            'yAxis' => sprintf(esc_html__('Published %s', 'wp-statistics'), Helper::getPostTypeName($this->args['post_type'] ?? '')),
            'xAxis' => sprintf(esc_html__('%s Views', 'wp-statistics'), Helper::getPostTypeName($this->args['post_type'] ?? '', true))
        ]);

        return $this->getChartData();
    }
}
