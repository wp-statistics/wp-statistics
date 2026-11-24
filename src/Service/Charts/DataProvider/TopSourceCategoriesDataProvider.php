<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Url;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Analytics\Referrals\SourceChannels;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;

/**
 * Rows of the "Top Source Categories" widget (Overview, WP Dashboard and Referrals Overview).
 *
 * Every category is sorted by visitors, Direct Traffic included, at its real place. The top
 * {@see self::LIMIT} are listed and the rest are summed into one "Other N categories" row, so
 * the shares add up to 100%. Each row carries its channel key with a fixed colour and icon.
 *
 * Row keys:
 *  - key, label, domain ('' when none), count (int), share (float), color, icon, url, is_other
 *  - source_category, top_domain ('-' when none), visitors (formatted), percentage ('12.5%'):
 *    the old keys, kept for add-on templates that still read them.
 */
class TopSourceCategoriesDataProvider extends AbstractChartDataProvider
{
    /**
     * Number of categories listed before the "Other" row.
     */
    const LIMIT = 5;

    protected $visitorsModel;

    public function __construct($args = [])
    {
        parent::__construct($args);

        $this->args = array_merge($this->args, [
            'group_by'       => 'visitor.source_channel',
            'source_channel' => false,
            'not_null'       => false,
            'decorate'       => true,
            'per_page'       => false, // every category: the rest is summed into "Other"
            'page'           => 1
        ]);

        $this->visitorsModel = new VisitorsModel();
    }

    public function getData()
    {
        $data = $this->visitorsModel->getReferrers($this->args);

        return $this->parseData($data);
    }

    protected function parseData($data)
    {
        $categories = [];

        foreach ((array)$data as $item) {
            $key = $item->getRawSourceChannel();
            $key = is_string($key) && $key !== '' ? $key : 'unassigned';

            // NULL and '' both mean unassigned: merge them into one row
            if (isset($categories[$key])) {
                $categories[$key]['count'] += $item->getTotalReferrals(true);
                continue;
            }

            $categories[$key] = [
                'key'   => $key,
                'label' => SourceChannels::getName($key),
                'count' => $item->getTotalReferrals(true),
            ];
        }

        $categories = array_values(array_filter($categories, function ($row) {
            return $row['count'] > 0;
        }));

        // By visitors, Direct Traffic included; same count keeps the query order
        $order = array_flip(array_column($categories, 'key'));
        usort($categories, function ($a, $b) use ($order) {
            if ($a['count'] === $b['count']) {
                return $order[$a['key']] - $order[$b['key']];
            }
            return $b['count'] - $a['count'];
        });

        $total = array_sum(array_column($categories, 'count'));
        $top   = array_slice($categories, 0, self::LIMIT);
        $rest  = array_slice($categories, self::LIMIT);
        $date  = $this->getDateRange();

        $rows = [];
        foreach ($top as $category) {
            $style  = SourceChannels::getStyle($category['key']);
            $domain = $category['key'] === 'direct' ? '' : $this->getTopDomain($category['key']);

            $rows[] = $this->buildRow([
                'key'    => $category['key'],
                'label'  => $category['label'],
                'domain' => $domain,
                'count'  => $category['count'],
                'color'  => $style['color'],
                'icon'   => $style['icon'],
                // Direct visits are not referrals: they open the visitors list filtered to Direct.
                'url'    => $category['key'] === 'direct'
                    ? Menus::admin_url('visitors', array_merge(['tab' => 'visitors', 'source_channel' => 'direct'], $date))
                    : Menus::admin_url('referrals', array_merge(['tab' => 'referred-visitors', 'source_channel' => $category['key']], $date)),
            ], $total);
        }

        if (!empty($rest)) {
            $rows[] = $this->buildRow([
                'key'      => 'other',
                /* translators: %s: number of categories summed into the row */
                'label'    => sprintf(_n('Other %s category', 'Other %s categories', count($rest), 'wp-statistics'), number_format_i18n(count($rest))),
                'domain'   => '',
                'count'    => array_sum(array_column($rest, 'count')),
                'color'    => '#A9AAAE',
                'icon'     => '',
                'url'      => Menus::admin_url('referrals', array_merge(['tab' => 'source-categories'], $date)),
                'is_other' => true,
            ], $total);
        }

        return $rows;
    }

    /**
     * Adds the share and the old keys add-on templates read.
     *
     * @param array $row
     * @param int $total
     * @return array
     */
    protected function buildRow($row, $total)
    {
        $row = array_merge(['is_other' => false], $row);

        $row['share']           = Helper::calculatePercentage($row['count'], $total);
        $row['source_category'] = $row['label'];
        $row['top_domain']      = $row['domain'] !== '' ? $row['domain'] : '-';
        $row['visitors']        = number_format_i18n($row['count']);
        $row['percentage']      = $row['share'] . '%';

        return $row;
    }

    /**
     * Most common referrer domain of a category, in the same date range as the widget.
     *
     * @param string $channel
     * @return string Domain, '' when there is none.
     */
    protected function getTopDomain($channel)
    {
        $args = [
            'decorate'       => true,
            'per_page'       => 1,
            'source_channel' => $channel,
        ];

        if (!empty($this->args['date'])) {
            $args['date'] = $this->args['date'];
        }

        $topDomain = $this->visitorsModel->getReferrers($args);

        if (empty($topDomain)) {
            return '';
        }

        $domain = $topDomain[0]->getReferrer();

        return !empty($domain) ? Url::cleanUrl($domain) : '';
    }

    /**
     * The widget range as from/to query args for the links ([] when the default range is used).
     *
     * @return array
     */
    protected function getDateRange()
    {
        $date = $this->args['date'] ?? [];

        if (!is_array($date) || empty($date['from']) || empty($date['to'])) {
            return [];
        }

        return ['from' => $date['from'], 'to' => $date['to']];
    }
}
