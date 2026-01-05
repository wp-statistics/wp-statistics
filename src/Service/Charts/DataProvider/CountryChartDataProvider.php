<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\Country;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BarChartResponseTrait;

class CountryChartDataProvider extends AbstractChartDataProvider
{
    use BarChartResponseTrait;

    /**
     * @var AnalyticsQueryHandler
     */
    protected $queryHandler;

    /**
     * Continent name to code mapping.
     *
     * @var array
     */
    protected static $continentNameToCode = [
        'Africa'        => 'AF',
        'Antarctica'    => 'AN',
        'Asia'          => 'AS',
        'Europe'        => 'EU',
        'North America' => 'NA',
        'Oceania'       => 'OC',
        'South America' => 'SA',
    ];

    public function __construct($args)
    {
        parent::__construct($args);

        $this->queryHandler = new AnalyticsQueryHandler();
    }


    public function getData()
    {
        $this->initChartData();

        $queryParams = [
            'sources'   => ['visitors'],
            'group_by'  => ['country'],
            'date_from' => $this->args['date']['from'] ?? null,
            'date_to'   => $this->args['date']['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1000,
        ];

        // Add continent filter if provided
        if (!empty($this->args['continent'])) {
            $continentName = $this->args['continent'];
            $continentCode = self::$continentNameToCode[$continentName] ?? $continentName;
            $queryParams['filters'] = [
                'continent' => $continentCode,
            ];
        }

        $result = $this->queryHandler->handle($queryParams);
        $data   = $this->parseData($result['data']['rows'] ?? []);

        $this->setChartLabels($data['labels']);
        $this->setChartData($data['visitors']);
        $this->setChartIcons($data['icons']);

        return $this->getChartData();
    }

    protected function parseData($data)
    {
        $parsedData = [];

        if (!empty($data)) {
            foreach ($data as $row) {
                $countryCode = $row['country_code'] ?? '';
                $visitors    = intval($row['visitors'] ?? 0);

                if (!empty($countryCode)) {
                    $parsedData[] = [
                        'label'    => Country::getName($countryCode),
                        'icon'     => Country::getFlag($countryCode),
                        'visitors' => $visitors
                    ];
                }
            }

            // Sort data by visitors
            usort($parsedData, function ($a, $b) {
                return $b['visitors'] - $a['visitors'];
            });

            if (count($parsedData) > 4) {
                // Get top 4 results, and others
                $topData   = array_slice($parsedData, 0, 4);
                $otherData = array_slice($parsedData, 4);

                // Show the rest of the results as others, and sum up the visitors
                $otherItem = [
                    'label'    => esc_html__('Other', 'wp-statistics'),
                    'icon'     => '',
                    'visitors' => array_sum(array_column($otherData, 'visitors')),
                ];

                $parsedData = array_merge($topData, [$otherItem]);
            }
        }

        $labels   = wp_list_pluck($parsedData, 'label');
        $visitors = wp_list_pluck($parsedData, 'visitors');
        $icons    = wp_list_pluck($parsedData, 'icon');

        return [
            'labels'   => $labels,
            'visitors' => $visitors,
            'icons'    => $icons,
        ];
    }
}
