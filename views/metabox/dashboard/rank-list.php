<?php
/**
 * Ranked list for the WP Dashboard widgets Top Pages and Top Referring
 * (the Overview page keeps its Top Pages table with Visitors and Bounce Rate).
 *
 * Same rows as Top Countries: components/objects/share-list (name, count, share of the
 * range total, thin track under the row). Referrers get the neutral site icon and the
 * small ↗ link to the site; pages get the ↗ link to the live page.
 *
 * Args:
 *  - data (array)  ['rows' => [...], 'total' => int] from MetaboxDataProvider::getTopPagesShare()
 *                  or ::getReferrersShare(), already limited by the query.
 *  - type (string) 'pages' or 'referrers'.
 *  - date (array)  ['from' => 'Y-m-d', 'to' => 'Y-m-d'] of the widget filter.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;
use WP_Statistics\Decorators\ReferralDecorator;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Pages;

$type  = isset($type) && $type === 'referrers' ? 'referrers' : 'pages';
$date  = isset($date) && is_array($date) ? $date : [];
$rows  = !empty($data['rows']) ? $data['rows'] : [];
$total = !empty($data['total']) ? intval($data['total']) : 0;

$range = array_filter(['from' => $date['from'] ?? '', 'to' => $date['to'] ?? '']);
$items = [];

foreach ($rows as $row) {
    if ($type === 'pages') {
        $page    = Pages::get_page_info($row->id, $row->type, $row->uri);
        $items[] = [
            'label'    => $page['title'],
            'url'      => add_query_arg($range, $page['report']),
            'count'    => intval($row->views),
            'external' => is_string($page['link']) && preg_match('#^https?://#i', $page['link']) ? $page['link'] : '',
        ];
    } else {
        /** @var ReferralDecorator $row */
        $items[] = [
            'label'    => $row->getListLabel($rows),
            'url'      => Menus::admin_url('referrals', array_merge(['tab' => 'referred-visitors', 'referrer' => $row->getRawReferrer()], $range)),
            'count'    => intval($row->getTotalReferrals(true)),
            'external' => $row->getReferrer(),
            'icon'     => 'globe',
        ];
    }
}
?>
<?php if (!empty($items)) : ?>
    <?php View::load('components/objects/share-list', [
        'items' => $items,
        'total' => $total,
        'class' => 'wps-dash-rank wps-dash-rank--' . $type,
    ]); ?>
<?php else : ?>
    <?php View::load('metabox/dashboard/empty', ['date' => $date]); ?>
<?php endif; ?>
