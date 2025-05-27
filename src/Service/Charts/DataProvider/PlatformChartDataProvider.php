<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BarChartResponseTrait;
use WP_STATISTICS\UserAgent;

class PlatformChartDataProvider extends AbstractChartDataProvider
{
    use BarChartResponseTrait;

    protected $data;
    protected $visitorsModel;

    public function __construct($args)
    {
        parent::__construct($args);

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
                /** @var VisitorDecorator $item */
                $platform   = $item->getOs()->getName();
                $agent      = $item->getBrowser()->getRaw();
                $device     = $item->getDevice()->getType();
                $model      = $item->getDevice()->getModel();

                // OS data
                if (!empty($platform)) {
                    $platforms = array_column($parsedData['os'], 'label');

                    if (!in_array($platform, $platforms)) {
                        $parsedData['os'][] = [
                            'label'    => $platform,
                            'icon'     => DeviceHelper::getPlatformLogo($platform),
                            'visitors' => 1
                        ];
                    } else {
                        $index = array_search($platform, $platforms);
                        $parsedData['os'][$index]['visitors']++;
                    }
                }

                // Browser data
                if (!empty($agent)) {
                    $agents = array_column($parsedData['browser'], 'label');

                    if (!in_array($agent, $agents)) {
                        $parsedData['browser'][] = [
                            'label'    => $agent,
                            'icon'     => DeviceHelper::getBrowserLogo($agent),
                            'visitors' => 1
                        ];
                    } else {
                        $index = array_search($agent, $agents);
                        $parsedData['browser'][$index]['visitors']++;
                    }
                }

                // Device data
                if (!empty($device)) {
                    $devices = array_column($parsedData['device'], 'label');

                    if (!in_array($device, $devices)) {
                        $parsedData['device'][] = [
                            'label'    => $device,
                            'visitors' => 1
                        ];
                    } else {
                        $index = array_search($device, $devices);
                        $parsedData['device'][$index]['visitors']++;
                    }
                }

                // Model data
                if (!empty($model)) {
                    $models = array_column($parsedData['model'], 'label');

                    if (!in_array($model, $models)) {
                        $parsedData['model'][] = [
                            'label'    => $model,
                            'visitors' => 1
                        ];
                    } else {
                        $index = array_search($model, $models);
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
