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
            'fields'   => ['model' => 'visitor.model', 'visitors' => 'COUNT(visitor.ID) as visitors'],
            'group_by' => 'visitor.model',
            'order_by' => 'visitors',
            'decorate' => false,
            'page'     => false,
            'per_page' => false
        ]);

        // If filter is applied, get distinct visitors to avoid data duplication
        if ($this->isFilterApplied()) {
            $this->args['fields']['visitors'] = 'COUNT(DISTINCT visitor.ID) as visitors';
        }

        if (empty($this->args['limit'])) {
            $this->args['limit'] = 5;
        }

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

            // Limit the number of items. If limit is 5, limit items to 4 + other
            $limit = $this->args['limit'] - 1;

            if (count($parsedData) > $limit) {
                // Get top 4 results, and others
                $topData    = array_slice($parsedData, 0, $limit);
                $otherData  = array_slice($parsedData, $limit);

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
