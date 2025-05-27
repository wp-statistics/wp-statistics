<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BarChartResponseTrait;

class BrowserChartDataProvider extends AbstractChartDataProvider
{
    use BarChartResponseTrait;

    protected $visitorsModel;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->args = array_merge($this->args, [
            'fields' => ['visitor.agent']
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
        $this->setChartData($data['visitors']);
        $this->setChartIcons($data['icons']);

        return $this->getChartData();
    }

    protected function parseData($data)
    {
        $parsedData = [];

        if (!empty($data)) {
            foreach ($data as $item) {
                /** @var VisitorDecorator $item */
                $agent = $item->getBrowser()->getRaw();

                // Browser data
                if (!empty($agent)) {
                    $agents = array_column($parsedData, 'label');

                    if (!in_array($agent, $agents)) {
                        $parsedData[] = [
                            'label'    => $agent,
                            'icon'     => DeviceHelper::getBrowserLogo($agent),
                            'visitors' => 1
                        ];
                    } else {
                        $index = array_search($agent, $agents);
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
        $visitors   = wp_list_pluck($parsedData, 'visitors');
        $icons      = wp_list_pluck($parsedData, 'icon');

        return [
            'labels'    => $labels,
            'visitors'  => $visitors,
            'icons'     => $icons,
        ];
    }
}
