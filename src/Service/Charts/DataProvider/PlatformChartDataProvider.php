<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BarChartResponseTrait;

class PlatformChartDataProvider extends AbstractChartDataProvider
{
    use BarChartResponseTrait;

    /**
     * @var AnalyticsQueryHandler
     */
    protected $queryHandler;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->args = wp_parse_args($args, [
            'limit' => 5
        ]);

        $this->queryHandler = new AnalyticsQueryHandler();
    }

    public function getOsData()
    {
        $this->initChartData();

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['os'],
            'date_from' => $this->args['date']['from'] ?? null,
            'date_to'   => $this->args['date']['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $data = $this->parseOsData($result['data']['rows'] ?? []);

        $this->setChartLabels($data['labels']);
        $this->setChartData($data['visitors']);
        $this->setChartIcons($data['icons']);

        return $this->getChartData();
    }

    public function getBrowserData()
    {
        $this->initChartData();

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['browser'],
            'date_from' => $this->args['date']['from'] ?? null,
            'date_to'   => $this->args['date']['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $data = $this->parseBrowserData($result['data']['rows'] ?? []);

        $this->setChartLabels($data['labels']);
        $this->setChartData($data['visitors']);
        $this->setChartIcons($data['icons']);

        return $this->getChartData();
    }

    public function getDeviceData()
    {
        $this->initChartData();

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['device_type'],
            'date_from' => $this->args['date']['from'] ?? null,
            'date_to'   => $this->args['date']['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $data = $this->parseDeviceData($result['data']['rows'] ?? []);

        $this->setChartLabels($data['labels']);
        $this->setChartData($data['visitors']);

        return $this->getChartData();
    }

    public function getModelData()
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

        $data = $this->parseModelData($result['data']['rows'] ?? []);

        $this->setChartLabels($data['labels']);
        $this->setChartData($data['visitors']);

        return $this->getChartData();
    }

    protected function parseOsData($data)
    {
        $parsedData = [];

        if (!empty($data)) {
            foreach ($data as $row) {
                $platform = $row['os'] ?? '';
                $visitors = intval($row['visitors'] ?? 0);

                if (!empty($platform)) {
                    $parsedData[] = [
                        'label'    => $platform,
                        'icon'     => DeviceHelper::getPlatformLogo($platform),
                        'visitors' => $visitors
                    ];
                }
            }

            $parsedData = $this->applyLimitWithOthers($parsedData);
        }

        return [
            'labels'   => wp_list_pluck($parsedData, 'label'),
            'visitors' => wp_list_pluck($parsedData, 'visitors'),
            'icons'    => wp_list_pluck($parsedData, 'icon'),
        ];
    }

    protected function parseBrowserData($data)
    {
        $parsedData = [];

        if (!empty($data)) {
            foreach ($data as $row) {
                $browser  = $row['browser'] ?? '';
                $visitors = intval($row['visitors'] ?? 0);

                if (!empty($browser)) {
                    $parsedData[] = [
                        'label'    => $browser,
                        'icon'     => DeviceHelper::getBrowserLogo($browser),
                        'visitors' => $visitors
                    ];
                }
            }

            $parsedData = $this->applyLimitWithOthers($parsedData);
        }

        return [
            'labels'   => wp_list_pluck($parsedData, 'label'),
            'visitors' => wp_list_pluck($parsedData, 'visitors'),
            'icons'    => wp_list_pluck($parsedData, 'icon'),
        ];
    }

    protected function parseDeviceData($data)
    {
        $parsedData = [];

        if (!empty($data)) {
            foreach ($data as $row) {
                $device   = $row['device_type'] ?? '';
                $visitors = intval($row['visitors'] ?? 0);

                if (!empty($device)) {
                    $parsedData[] = [
                        'label'    => $device,
                        'visitors' => $visitors
                    ];
                }
            }

            $parsedData = $this->applyLimitWithOthers($parsedData);
        }

        return [
            'labels'   => wp_list_pluck($parsedData, 'label'),
            'visitors' => wp_list_pluck($parsedData, 'visitors'),
        ];
    }

    protected function parseModelData($data)
    {
        $parsedData = [];

        if (!empty($data)) {
            foreach ($data as $row) {
                $model    = $row['device_model'] ?? '';
                $visitors = intval($row['visitors'] ?? 0);

                if (!empty($model)) {
                    $parsedData[] = [
                        'label'    => $model,
                        'visitors' => $visitors
                    ];
                }
            }

            $parsedData = $this->applyLimitWithOthers($parsedData);
        }

        return [
            'labels'   => wp_list_pluck($parsedData, 'label'),
            'visitors' => wp_list_pluck($parsedData, 'visitors'),
        ];
    }

    /**
     * Apply limit with "Other" aggregation.
     *
     * @param array $parsedData Data to apply limit to.
     * @return array Filtered data with "Other" aggregation if needed.
     */
    protected function applyLimitWithOthers($parsedData)
    {
        // Sort data by visitors
        usort($parsedData, function ($a, $b) {
            return $b['visitors'] - $a['visitors'];
        });

        // Limit the number of items. If limit is 5, limit items to 4 + other
        $limit = $this->args['limit'] - 1;

        if (count($parsedData) > $limit) {
            // Get top N-1 results, and others
            $topData   = array_slice($parsedData, 0, $limit);
            $otherData = array_slice($parsedData, $limit);

            // Show the rest of the results as others, and sum up the visitors
            $otherItem = [
                'label'    => esc_html__('Other', 'wp-statistics'),
                'icon'     => '',
                'visitors' => array_sum(array_column($otherData, 'visitors')),
            ];

            $parsedData = array_merge($topData, [$otherItem]);
        }

        return $parsedData;
    }
}
