<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BarChartResponseTrait;

class ModelChartDataProvider extends AbstractChartDataProvider
{
    use BarChartResponseTrait;

    protected $visitorsModel;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->args = array_merge($this->args, [
            'fields' => ['visitor.model']
        ]);

        $this->visitorsModel = new VisitorsModel();
    }


    public function getData()
    {
        $this->initChartData();

        $data = $this->visitorsModel->getVisitorsData($this->args);
        $data = $this->parseData($data);

        $this->setChartLabels($data['labels']);
        $this->setChartData($data['visitors']);

        return $this->getChartData();
    }

    protected function parseData($data)
    {
        $parsedData = [];
        $unknownData = 0;

        if (!empty($data)) {
            foreach ($data as $item) {
                $model = $item->getDevice()->getModel();

                if ($model === ' ') {
                    $model = 'Unknown';
                }

                if (!empty($model) && $model !== 'Unknown') {
                    $models = array_column($parsedData, 'label');

                    if (!in_array($model, $models)) {
                        $parsedData[] = [
                            'label'    => $model,
                            'visitors' => 1
                        ];
                    } else {
                        $index = array_search($model, $models);
                        $parsedData[$index]['visitors']++;
                    }
                }

                if ($model === 'Unknown') {
                    ++$unknownData;
                }
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
                    'visitors' => array_sum(array_column($otherData, 'visitors')) + $unknownData,
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
