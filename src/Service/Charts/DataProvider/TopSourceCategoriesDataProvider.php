<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Url;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;

class TopSourceCategoriesDataProvider extends AbstractChartDataProvider
{
    protected $visitorsModel;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->args = array_merge($this->args, [
            'group_by'       => 'visitor.source_channel',
            'source_channel' => false,
            'not_null'       => false,
            'decorate'       => true,
            'per_page'       => 10,
            'page'           => 1
        ]);

        $this->visitorsModel = new VisitorsModel();
    }

    public function getData()
    {
        $data       = $this->visitorsModel->getReferrers($this->args);
        $parsedData = $this->parseData($data);

        return $parsedData;
    }

    protected function parseData($data)
    {
        $parsedData = [];
        $direct     = null;

        $totalReferrers = 0;
        foreach ($data as $item) {
            $totalReferrers += $item->getTotalReferrals(true);
        }

        foreach ($data as $item) {
            $topDomain = $this->visitorsModel->getReferrers(['decorate' => true, 'per_page' => 1, 'source_channel' => $item->getRawSourceChannel()]);
            $referrers = $item->getTotalReferrals(true);

            // Store direct category in a temp variable and add it at the end separately
            if ($item->getRawSourceChannel() === 'direct') {
                $direct = $item;
                continue;
            }

            // Limit to 4 categories
            if (count($parsedData) >= 4) break;

            $parsedData[] = [
                'source_category' => $item->getSourceChannel(),
                'top_domain'      => !empty($topDomain) ? Url::cleanUrl($topDomain[0]->getReferrer()) : '-',
                'visitors'        => number_format_i18n($referrers),
                'percentage'      => Helper::calculatePercentage($referrers, $totalReferrers) . '%'
            ];
        }

        // Add direct category
        if (!empty($parsedData)) {
            $referrers = $direct ? $direct->getTotalReferrals(true) : 0;

            $parsedData[] = [
                'source_category' => esc_html__('Direct', 'wp-statistics'),
                'top_domain'      => '-',
                'visitors'        => number_format_i18n($referrers),
                'percentage'      => Helper::calculatePercentage($referrers, $totalReferrers) . '%'
            ];
        }

        return $parsedData;
    }
}