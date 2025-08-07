<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_STATISTICS\Admin_Template;
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
            'fields'   => ['visitor.model', 'COUNT(DISTINCT visitor.ID) as visitors'],
            'group_by' => 'visitor.model',
            'order_by' => 'visitors',
            'decorate' => false,
            'page'     => false,
            'per_page' => false
        ]);

        $this->visitorsModel = new VisitorsModel();
    }


    public function getData()
    {
        $this->initChartData();

        if (!empty($this->args['referred_visitors'])) {
            $data = $this->visitorsModel->getReferredVisitors($this->args);
        } else {
            $data = $this->visitorsModel->getVisitorsData($this->args);
        }

        $data = $this->parseData($data);

        $this->setChartLabels($data['labels']);
        $this->setChartData($data['visitors']);

        return $this->getChartData();
    }

    protected function parseData($data)
    {
        $parsedData = [];

        if (!empty($data)) {
            foreach ($data as $item) {
                $model = Admin_Template::unknownToNotSet($item->model);

                $parsedData[] = [
                    'label'    => $model,
                    'visitors' => $item->visitors
                ];
            }

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
