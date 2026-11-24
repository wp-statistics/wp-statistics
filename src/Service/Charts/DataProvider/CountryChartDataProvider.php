<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\Country;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BarChartResponseTrait;
use WP_STATISTICS\Helper;

class CountryChartDataProvider extends AbstractChartDataProvider
{
    use BarChartResponseTrait;

    protected $visitorsModel;

    public function __construct($args)
    {
        parent::__construct($args);

        // Visitors without a country count too (as "(not set)"), so the shares add up to all visitors,
        // the same base as the Geographic > Countries tab.
        $this->args = array_merge($this->args, [
            'order_by'  => 'visitors',
            // NULL, empty and unknown locations come back as one "(not set)" row
            'merge_unknown_country' => true,
            'page'      => false,
            'per_page'  => false
        ]);

        $this->visitorsModel = new VisitorsModel();
    }


    public function getData()
    {
        $this->initChartData();

        $data = $this->visitorsModel->getVisitorsGeoData($this->args);
        $data = $this->parseData($data);

        $this->setChartLabels($data['labels']);
        $this->setChartData($data['visitors']);
        $this->setChartIcons($data['icons']);
        $this->setChartPercentages($data['percentages']);
        $this->setChartTotal($data['total']);

        return $this->getChartData();
    }

    /**
     * Top countries plus one "Other N countries" row, with each share taken from the real total
     * (every visitor in the range, including those without a country), not from the rows shown.
     * Visitors without a country (empty location or the unknown code) are one "(not set)" row: in the top
     * rows when it is big enough, else its own row after "Other N countries" (never counted as a country).
     *
     * @param array $data Rows from VisitorsModel::getVisitorsGeoData(), sorted by visitors.
     * @return array{labels: array, icons: array, visitors: array, percentages: array, total: int}
     */
    protected function parseData($data)
    {
        $parsedData = [
            'labels'      => [],
            'icons'       => [],
            'visitors'    => [],
            'percentages' => [],
            'total'       => 0,
        ];

        if (!is_array($data) || empty($data)) {
            return $parsedData;
        }

        $data = self::mergeUnknownCountries($data);

        $limit    = !empty($this->args['limit']) ? max(1, intval($this->args['limit'])) : 5;
        $visitors = intval(array_sum(array_map('intval', array_column($data, 'visitors'))));
        $topData  = array_slice($data, 0, $limit);
        $rest     = array_slice($data, $limit);

        // "(not set)" outside the top rows gets its own row instead of hiding in "Other N countries".
        $unknown = null;
        foreach ($rest as $index => $item) {
            if ($item->country === Country::$unknown_location) {
                $unknown = $item;
                unset($rest[$index]);
                break;
            }
        }

        $parsedData['total'] = $visitors;

        foreach ($topData as $item) {
            $parsedData['labels'][]      = Country::getName($item->country);
            $parsedData['icons'][]       = Country::flag($item->country);
            $parsedData['visitors'][]    = intval($item->visitors);
            $parsedData['percentages'][] = Helper::calculatePercentage(intval($item->visitors), $visitors);
        }

        if (!empty($rest)) {
            $otherVisitors = intval(array_sum(array_map('intval', array_column($rest, 'visitors'))));
            $otherCount    = count($rest);

            $parsedData['labels'][]      = sprintf(
                /* translators: %s: number of countries grouped into the "Other" row */
                _n('Other %s country', 'Other %s countries', $otherCount, 'wp-statistics'),
                number_format_i18n($otherCount)
            );
            $parsedData['icons'][]       = '';
            $parsedData['visitors'][]    = $otherVisitors;
            $parsedData['percentages'][] = Helper::calculatePercentage($otherVisitors, $visitors);
        }

        if ($unknown) {
            $parsedData['labels'][]      = Country::getName(Country::$unknown_location);
            $parsedData['icons'][]       = Country::flag(Country::$unknown_location);
            $parsedData['visitors'][]    = intval($unknown->visitors);
            $parsedData['percentages'][] = Helper::calculatePercentage(intval($unknown->visitors), $visitors);
        }

        return $parsedData;
    }

    /**
     * One row for every visitor without a country: empty/NULL locations and the unknown code are merged
     * into one row, placed by its visitor count; the other rows keep their order.
     *
     * @param object[] $data
     * @return object[]
     */
    public static function mergeUnknownCountries($data)
    {
        $rows    = [];
        $unknown = null;

        foreach ($data as $item) {
            $code = isset($item->country) ? (string)$item->country : '';

            if ($code === '' || $code === Country::$unknown_location) {
                if ($unknown === null) {
                    $unknown           = clone $item;
                    $unknown->country  = Country::$unknown_location;
                    $unknown->visitors = 0;
                }
                $unknown->visitors += intval($item->visitors);
                continue;
            }

            $rows[] = $item;
        }

        if ($unknown === null || $unknown->visitors <= 0) {
            return $rows;
        }

        // Keep the query's order; the merged row goes before the first row with fewer visitors.
        $position = count($rows);
        foreach ($rows as $index => $item) {
            if (intval($item->visitors) < $unknown->visitors) {
                $position = $index;
                break;
            }
        }
        array_splice($rows, $position, 0, [$unknown]);

        return $rows;
    }
}
