<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BarChartResponseTrait;
use WP_Statistics\Service\Charts\Traits\SimpleBarChartDataTrait;

class DeviceChartDataProvider extends AbstractChartDataProvider
{
    use BarChartResponseTrait;
    use SimpleBarChartDataTrait;

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
            'group_by'  => ['device_type'],
            'date_from' => $this->args['date']['from'] ?? null,
            'date_to'   => $this->args['date']['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $data = $this->parseBarChartData(
            $result['data']['rows'] ?? [],
            'device_type',
            [DeviceHelper::class, 'getDeviceLogo']
        );

        $this->setChartLabels($data['labels']);
        $this->setChartIcons($data['icons']);
        $this->setChartData($data['visitors']);

        return $this->getChartData();
    }
}
