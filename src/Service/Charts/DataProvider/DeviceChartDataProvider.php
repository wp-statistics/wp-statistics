<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BarChartResponseTrait;

class DeviceChartDataProvider extends AbstractChartDataProvider
{
    use BarChartResponseTrait;

    protected $visitorsModel;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->args = array_merge($this->args, [
            'fields'   => ['visitor.device', 'COUNT(DISTINCT visitor.ID) as visitors'],
            'group_by' => 'visitor.device',
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
        $this->setChartIcons($data['icons']);
        $this->setChartData($data['visitors']);

        return $this->getChartData();
    }

    protected function parseData($data)
    {
        $parsedData = [];

        if (!empty($data)) {
            foreach ($data as $item) {
                if (empty($item->device)) continue;

                $parsedData[] = [
                    'label'    => ucfirst($item->device),
                    'icon'     => DeviceHelper::getDeviceLogo($item->device),
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
                    'icon'     => '',
                    'visitors' => array_sum(array_column($otherData, 'visitors')),
                ];

                $parsedData = array_merge($topData, [$otherItem]);
            }
        }

        $labels     = wp_list_pluck($parsedData, 'label');
        $icons      = wp_list_pluck($parsedData, 'icon');
        $visitors   = wp_list_pluck($parsedData, 'visitors');

        return [
            'labels'    => $labels,
            'icons'     => $icons,
            'visitors'  => $visitors
        ];
    }
}
