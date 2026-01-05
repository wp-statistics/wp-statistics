<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Url;
use WP_Statistics\Service\Analytics\Referrals\SourceChannels;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;

class TopSourceCategoriesDataProvider extends AbstractChartDataProvider
{
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
        // Get visitors grouped by referrer channel
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['referrer_channel'],
            'date_from' => $this->args['date']['from'] ?? null,
            'date_to'   => $this->args['date']['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $parsedData = $this->parseData($result['data']['rows'] ?? []);

        return $parsedData;
    }

    protected function parseData($data)
    {
        $parsedData = [];
        $direct     = null;

        // Calculate total referrers
        $totalReferrers = 0;
        foreach ($data as $row) {
            $totalReferrers += intval($row['visitors'] ?? 0);
        }

        // Sort data by visitors count descending
        usort($data, function ($a, $b) {
            return intval($b['visitors'] ?? 0) - intval($a['visitors'] ?? 0);
        });

        foreach ($data as $row) {
            $channel  = $row['referrer_channel'] ?? '';
            $visitors = intval($row['visitors'] ?? 0);

            // Store direct category in a temp variable and add it at the end separately
            if ($channel === 'direct') {
                $direct = $row;
                continue;
            }

            // Skip empty channels
            if (empty($channel)) {
                continue;
            }

            // Limit to 4 categories
            if (count($parsedData) >= 4) {
                break;
            }

            // Get top domain for this channel
            $topDomain = $this->getTopDomainForChannel($channel);

            $parsedData[] = [
                'source_category' => SourceChannels::getName($channel),
                'top_domain'      => $topDomain,
                'visitors'        => number_format_i18n($visitors),
                'percentage'      => Helper::calculatePercentage($visitors, $totalReferrers) . '%'
            ];
        }

        // Add direct category at the end
        if (!empty($parsedData)) {
            $directVisitors = $direct ? intval($direct['visitors'] ?? 0) : 0;

            $parsedData[] = [
                'source_category' => esc_html__('Direct', 'wp-statistics'),
                'top_domain'      => '-',
                'visitors'        => number_format_i18n($directVisitors),
                'percentage'      => Helper::calculatePercentage($directVisitors, $totalReferrers) . '%'
            ];
        }

        return $parsedData;
    }

    /**
     * Get top domain for a specific channel
     *
     * @param string $channel
     * @return string
     */
    protected function getTopDomainForChannel($channel)
    {
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['referrer'],
            'filters'   => [
                'referrer_channel' => $channel
            ],
            'date_from' => $this->args['date']['from'] ?? null,
            'date_to'   => $this->args['date']['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1,
        ]);

        $rows = $result['data']['rows'] ?? [];

        if (!empty($rows) && !empty($rows[0]['referrer_domain'])) {
            return Url::cleanUrl($rows[0]['referrer_domain']);
        }

        return '-';
    }
}