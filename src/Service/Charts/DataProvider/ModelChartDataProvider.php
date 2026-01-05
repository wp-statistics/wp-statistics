<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BarChartResponseTrait;

class ModelChartDataProvider extends AbstractChartDataProvider
{
    use BarChartResponseTrait;

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
        $this->initChartData();

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['device_model'],
            'date_from' => $this->args['date']['from'] ?? null,
            'date_to'   => $this->args['date']['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $data = $this->parseData($result['data']['rows'] ?? []);

        $this->setChartLabels($data['labels']);
        $this->setChartData($data['visitors']);

        return $this->getChartData();
    }

    protected function parseData($data)
    {
        $parsedData = [];
        $unknownData = 0;

        if (!empty($data)) {
            foreach ($data as $row) {
                $model    = $row['device_model'] ?? '';
                $visitors = intval($row['visitors'] ?? 0);

                if (!empty($model) && $model !== 'Unknown') {
                    $parsedData[] = [
                        'label'    => $model,
                        'visitors' => $visitors
                    ];
                } else {
                    $unknownData += $visitors;
                }
            }

            if ($unknownData > 0) {
                $parsedData[] = [
                    'label'    => esc_html__('Unknown', 'wp-statistics'),
                    'visitors' => $unknownData
                ];
            }

            // Sort data by visitors
            usort($parsedData, function ($a, $b) {
                return $b['visitors'] - $a['visitors'];
            });

            if (count($parsedData) > 4) {
                // Get top 4 results, and others
                $topData    = array_slice($parsedData, 0, 4);
                $otherData  = array_slice($parsedData, 4);

                // Show the rest of the results as others, and sum up the visitors
                $otherItem    = [
                    'label'    => esc_html__('Other', 'wp-statistics'),
                    'visitors' => array_sum(array_column($otherData, 'visitors')),
                ];

                $parsedData = array_merge($topData, [$otherItem]);
            }
        }

        $labels     = wp_list_pluck($parsedData, 'label');
        $visitors   = wp_list_pluck($parsedData, 'visitors');

        return [
            'labels'    => $labels,
            'visitors'  => $visitors
        ];
    }
}
