<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Decorators\ReferralDecorator;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;

?>

<?php
// Rows + range total from MetaboxDataProvider::getReferrersShare(); a plain row list still works.
$rows  = isset($data['rows']) ? $data['rows'] : (is_array($data ?? null) ? $data : []);
$total = isset($data['total']) ? intval($data['total']) : 0;
$items = [];

foreach ($rows as $item) {
    /** @var ReferralDecorator $item */
    $items[] = [
        'label'     => $item->getListLabel($rows),
        'url'       => $item->getReferrer(),
        'new_tab'   => true,
        'count'     => intval($item->getTotalReferrals(true)),
        'count_url' => Menus::admin_url('referrals', ['tab' => 'referred-visitors', 'referrer' => $item->getRawReferrer(), 'from' => $filters['date']['from'] ?? '', 'to' => $filters['date']['to'] ?? '']),
        'icon'      => 'globe',
    ];
}
?>
<?php if (!empty($items)) : ?>
    <div class="o-wrap">
        <?php View::load('components/objects/share-list', ['items' => $items, 'total' => $total, 'class' => 'wps-share-list--referrers']); ?>
    </div>
<?php else : ?>
    <?php
    $title = __('No data found for this date range.', 'wp-statistics');
    if ($isTodayOrFutureDate) {
        $title = __('Data coming soon!', 'wp-statistics');
    }
    View::load("components/objects/no-data", [
        'url'   => WP_STATISTICS_URL . 'assets/images/no-data/vector-2.svg',
        'title' => $title,
        'date'  => $filters['date'] ?? null
    ]);
    ?>
<?php endif; ?>