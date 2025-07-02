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
            'fields' => ['visitor.device']
        ]);

        // Get all results
        $this->args['page']     = false;
        $this->args['per_page'] = false;

        $this->visitorsModel = new VisitorsModel();
    }


    public function getData()
    {
        $this->initChartData();

        $data = $this->visitorsModel->getVisitorsData($this->args);
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
                $device = $item->getDevice()->getType();

                if (!empty($device)) {
                    $devices = array_column($parsedData, 'label');

                    if (!in_array($device, $devices)) {
                        $parsedData[] = [
                            'label'    => $device,
                            'icon'     => DeviceHelper::getDeviceLogo($device),
                            'visitors' => 1
                        ];
                    } else {
                        $index = array_search($device, $devices);
                        $parsedData[$index]['visitors']++;
                    }
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
