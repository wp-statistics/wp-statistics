<?php
/**
 * Compact ranked content list with a "Rank by" menu in the card header (Top Contents).
 * One list per ranking; the menu shows one at a time (assets/dev/javascript/components/leaderboard.js).
 * Each item: rank, featured image (WordPress's 'thumbnail' size, shown at 40px, lazy-loaded) or a
 * colour tile with initials when the post has none, the title (cut with an
 * ellipsis, full title on hover), a ↗ link to the live page and the number.
 *
 * Args:
 *  - id       (string)  Unique id prefix for this card.
 *  - title    (string)  Card title.
 *  - tooltip  (string)  Optional info tooltip.
 *  - lists    (array[]) Each: 'key' (string), 'label' (menu text, e.g. "Most viewed"),
 *                       'items' (Leaderboard::contentItems() rows).
 *  - see_all  (array)   Optional ['href' => '', 'title' => ''] for the "See all ..." footer.
 *  - empty    (array)   Args for components/objects/empty-state.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

$id      = !empty($id) ? sanitize_html_class($id) : 'wps-rank-' . wp_unique_id();
$lists   = isset($lists) && is_array($lists) ? array_values($lists) : [];
$empty   = isset($empty) && is_array($empty) ? $empty : [];
$tooltip = isset($tooltip) ? $tooltip : '';

$tools = '';
if (count($lists) > 1) {
    $options = '';
    foreach ($lists as $index => $list) {
        $options .= '<option value="' . esc_attr($list['key']) . '"' . selected($index, 0, false) . '>' . esc_html($list['label']) . '</option>';
    }

    $tools = '<label class="wps-rank-by" for="' . esc_attr($id . '-rank-by') . '"><span class="wps-rank-by__label">' . esc_html__('Rank by', 'wp-statistics') . '</span>'
        . '<select id="' . esc_attr($id . '-rank-by') . '" class="wps-rank-by__select" data-wps-rank-by="' . esc_attr($id) . '">' . $options . '</select></label>';
}
?>
<div class="wps-card wps-rank-card" id="<?php echo esc_attr($id); ?>">
    <?php View::load('components/objects/card-header', [
        'title'   => $title ?? '',
        'tooltip' => $tooltip,
        'tools'   => $tools,
        'context' => 'card',
    ]); ?>

    <?php foreach ($lists as $index => $list) : ?>
        <div class="wps-rank-list" data-rank-list="<?php echo esc_attr($list['key']); ?>"<?php echo $index > 0 ? ' hidden' : ''; ?>>
            <?php if (!empty($list['items'])) : ?>
                <ol class="wps-rank-list__items" aria-label="<?php echo esc_attr(($title ?? '') . ': ' . $list['label']); ?>">
                    <?php foreach ($list['items'] as $rank => $item) : ?>
                        <li class="wps-rank-list__item">
                            <span class="wps-rank-list__rank" aria-hidden="true"><?php echo esc_html(number_format_i18n($rank + 1)); ?></span>
                            <?php View::load('components/objects/post-thumb', [
                                'thumb'    => $item['thumb'],
                                'initials' => $item['initials'],
                                'color'    => $item['color'],
                                'size'     => 40,
                                'class'    => 'wps-rank-list__thumb',
                            ]); ?>
                            <span class="wps-title-cell wps-rank-list__title">
                                <a href="<?php echo esc_url($item['url']); ?>" title="<?php echo esc_attr($item['title']); ?>"><span class="wps-ellipsis-text"><?php echo esc_html($item['title']); ?></span></a>
                                <?php View::load('components/objects/new-tab-link', ['url' => $item['link']]); ?>
                            </span>
                            <span class="wps-rank-list__metric"><?php echo esc_html($item['metric']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php else : ?>
                <?php View::load('components/objects/empty-state', $empty); ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>

    <?php if (!empty($see_all['href'])) {
        View::load('components/objects/view-more', [
            'href'  => $see_all['href'],
            'title' => $see_all['title'],
        ]);
    } ?>
</div>
