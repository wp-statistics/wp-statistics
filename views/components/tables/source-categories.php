<?php
/**
 * Top Source Categories: the Top Countries share rows (components/objects/share-list).
 * Category name (no icon: the rows read as a plain ranking, like Top Countries without flags),
 * the top domain as grey text, count + share on the end side, thin bar under the row. The last
 * row sums the categories past the top five. The full Referrals > Source Categories report keeps
 * the coloured icons, because there they match the lines of the chart above the table.
 *
 * Args:
 *  - data    (array) Rows from TopSourceCategoriesDataProvider::getData(). Rows with only the
 *                    old keys (source_category, top_domain, visitors, percentage) still work.
 *  - filters (array) Optional, ['date' => ['from' => ..., 'to' => ...]] for the empty state.
 *  - wrap    (bool)  Optional, print the `.inside` wrapper (default true, as before).
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

$rows  = isset($data) && is_array($data) ? $data : [];
$wrap  = !isset($wrap) || $wrap;
$items = [];

foreach ($rows as $row) {
    if (!is_array($row)) {
        continue;
    }

    $count = isset($row['count']) ? intval($row['count']) : intval(preg_replace('/[^\d]/', '', (string)($row['visitors'] ?? '0')));
    $label = (string)($row['label'] ?? ($row['source_category'] ?? ''));

    if (array_key_exists('domain', $row)) {
        $domain = (string)$row['domain'];
    } else {
        $domain = (string)($row['top_domain'] ?? '');
        $domain = $domain === '-' ? '' : $domain;
    }

    $items[] = [
        'label'    => $label,
        'count'    => $count,
        'url'      => $row['url'] ?? '',
        'meta'     => $domain,
        'meta_dir' => 'ltr',
        'class'    => !empty($row['is_other']) ? 'wps-share-list__item--other' : '',
    ];
}
?>
<?php if ($wrap) : ?><div class="inside"><?php endif; ?>
    <?php if (!empty($items)) : ?>
        <div class="o-wrap wps-source-categories">
            <?php View::load('components/objects/share-list', ['items' => $items, 'class' => 'wps-share-list--categories']); ?>
        </div>
    <?php else : ?>
        <?php View::load('components/objects/empty-state', ['date' => $filters['date'] ?? ($args['date'] ?? null)]); ?>
    <?php endif; ?>
<?php if ($wrap) : ?></div><?php endif; ?>
