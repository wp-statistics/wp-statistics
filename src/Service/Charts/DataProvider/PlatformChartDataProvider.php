<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BarChartResponseTrait;
use WP_STATISTICS\UserAgent;

class PlatformChartDataProvider extends AbstractChartDataProvider
{
    use BarChartResponseTrait;

    public $args;
    protected $data;
    protected $visitorsModel;

    public function __construct($args)
    {
        $this->args = $args;

        $this->visitorsModel = new VisitorsModel();

        $this->data = $this->getVisitorsData();
    }

    private function getVisitorsData()
    {
        $rawData = $this->visitorsModel->getVisitorsData($this->args);
        return $this->parseData($rawData);
    }

    public function getOsData()
    {
        $this->initChartData();

        $labels = wp_list_pluck($this->data['os'], 'label');
        $data   = wp_list_pluck($this->data['os'], 'visitors');
        $icons  = wp_list_pluck($this->data['os'], 'icon');

        $this->setChartLabels($labels);
        $this->setChartData($data);
        $this->setChartIcons($icons);

        return $this->getChartData();
    }

    public function getBrowserData()
    {
        $this->initChartData();

        $labels = wp_list_pluck($this->data['browser'], 'label');
        $data   = wp_list_pluck($this->data['browser'], 'visitors');
        $icons  = wp_list_pluck($this->data['browser'], 'icon');

        $this->setChartLabels($labels);
        $this->setChartData($data);
        $this->setChartIcons($icons);

        return $this->getChartData();
    }

    public function getDeviceData()
    {
        $this->initChartData();

        $labels = wp_list_pluck($this->data['device'], 'label');
        $data   = wp_list_pluck($this->data['device'], 'visitors');

        $this->setChartLabels($labels);
        $this->setChartData($data);

        return $this->getChartData();
    }

    public function getModelData()
    {
        $this->initChartData();

        $labels = wp_list_pluck($this->data['model'], 'label');
        $data   = wp_list_pluck($this->data['model'], 'visitors');

        $this->setChartLabels($labels);
        $this->setChartData($data);

        return $this->getChartData();
    }

    protected function parseData($data)
    {
        $parsedData = [
            'os'        => [],
            'browser'   => [],
            'device'    => [],
            'model'     => []
        ];

        if (!empty($data)) {
            foreach ($data as $item) {
                // Remove device subtype, for example: mobile:smart -> mobile
                $item->device = !empty($item->device) ? ucfirst(Helper::getDeviceCategoryName($item->device)) : esc_html__('Unknown', 'wp-statistics');

                // OS data
                if (!empty($item->platform) && $item->platform !== 'Unknown') {
                    $platforms = array_column($parsedData['os'], 'label');

                    if (!in_array($item->platform, $platforms)) {
                        $parsedData['os'][] = [
                            'label'    => $item->platform,
                            'icon'     => UserAgent::getPlatformLogo($item->platform),
                            'visitors' => 1
                        ];
                    } else {
                        $index = array_search($item->platform, $platforms);
                        $parsedData['os'][$index]['visitors']++;
                    }
                }

                // Browser data
                if (!empty($item->agent) && $item->agent !== 'Unknown') {
                    $agents = array_column($parsedData['browser'], 'label');

                    if (!in_array($item->agent, $agents)) {
                        $parsedData['browser'][] = [
                            'label'    => $item->agent,
                            'icon'     => UserAgent::getBrowserLogo($item->agent),
                            'visitors' => 1
                        ];
                    } else {
                        $index = array_search($item->agent, $agents);
                        $parsedData['browser'][$index]['visitors']++;
                    }
                }

                // Device data
                if (!empty($item->device) && $item->device !== 'Unknown') {
                    $devices = array_column($parsedData['device'], 'label');

                    if (!in_array($item->device, $devices)) {
                        $parsedData['device'][] = [
                            'label'    => $item->device,
                            'visitors' => 1
                        ];
                    } else {
                        $index = array_search($item->device, $devices);
                        $parsedData['device'][$index]['visitors']++;
                    }
                }

                // Model data
                if (!empty($item->model) && $item->model !== 'Unknown') {
                    $models = array_column($parsedData['model'], 'label');

                    if (!in_array($item->model, $models)) {
                        $parsedData['model'][] = [
                            'label'    => $item->model,
                            'visitors' => 1
                        ];
                    } else {
                        $index = array_search($item->model, $models);
                        $parsedData['model'][$index]['visitors']++;
                    }
                }
            }

            foreach ($parsedData as $key => &$data) {
                // Sort data by visitors
                usort($data, function ($a, $b) {
                    return $b['visitors'] - $a['visitors'];
                });

                if (count($data) > 4) {
                    // Get top 4 results, and others
                    $topData    = array_slice($data, 0, 4);
                    $otherData  = array_slice($data, 4);

                    // Show the rest of the results as others, and sum up the visitors
                    $otherItem    = [
                        'label'    => esc_html__('Other', 'wp-statistics'),
                        'icon'     => '',
                        'visitors' => array_sum(array_column($otherData, 'visitors')),
                    ];

                    $parsedData[$key] = array_merge($topData, [$otherItem]);
                }
            }
        }

        return $parsedData;
    }
}
