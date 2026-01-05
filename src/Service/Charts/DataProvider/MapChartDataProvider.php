<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\Country;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\MapChartResponseTrait;

class MapChartDataProvider extends AbstractChartDataProvider
{
    use MapChartResponseTrait;

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
            'group_by'  => ['country'],
            'date_from' => $this->args['date']['from'] ?? null,
            'date_to'   => $this->args['date']['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $parsedData = $this->parseData($result['data']['rows'] ?? []);

        $labels  = wp_list_pluck($parsedData, 'label');
        $flags   = wp_list_pluck($parsedData, 'flag');
        $codes   = wp_list_pluck($parsedData, 'code');
        $data    = wp_list_pluck($parsedData, 'visitors');
        $rawData = wp_list_pluck($parsedData, 'visitors_raw');

        $this->setChartLabels($labels);
        $this->setChartFlags($flags);
        $this->setChartCountryCodes($codes);
        $this->setChartData($data);
        $this->setChartRawData($rawData);

        return $this->getChartData();
    }

    protected function parseData($data)
    {
        $parsedData = [];

        foreach ($data as $row) {
            $countryCode = $row['country_code'] ?? '';
            $visitors    = intval($row['visitors'] ?? 0);

            if (empty($countryCode)) {
                continue;
            }

            // Format the visitors count
            $formattedVisitors = number_format($visitors);

            $parsedData[] = [
                'label'        => Country::getName($countryCode),
                'code'         => $countryCode,
                'visitors'     => $formattedVisitors,
                'visitors_raw' => $visitors,
                'flag'         => Country::getFlag($countryCode)
            ];
        }

        return $parsedData;
    }
}
