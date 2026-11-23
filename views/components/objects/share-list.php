<?php
/**
 * Share list: the PHP twin of wps_js.horizontal_bar() in javascript/helper.js (Top Countries,
 * Browsers, Operating Systems ...). Each row: icon + name on the start side, count (bold) and
 * share on the end side, a thin track under the row. Same markup and classes as the JS list,
 * so it uses the same styles (component/_percent.scss, "Share list").
 *
 * Numbers are printed the way the JS list prints them, so PHP and JS rows read the same in
 * every locale: the count like wps_js.formatNumber() ("950", "1.2K") with the exact number on
 * hover, the share like wps_js.format_percent() ("16.5%", "40%").
 *
 * Args:
 *  - items (array) Rows, each:
 *      label      (string) Name, one line, cut with an ellipsis, full name on hover.
 *      count      (int)    Value of the row.
 *      url        (string) Optional link on the name.
 *      new_tab    (bool)   Optional, open the name link in a new tab.
 *      count_url  (string) Optional link on the count.
 *      external   (string) Optional live URL, shown as the small ↗ new-tab icon after the name.
 *      icon       (string|array) Optional image URL, 'globe' for the neutral site icon, or
 *                 ['svg' => '<path .../>', 'color' => '#hex'] for a 24x24 stroke icon in a colour.
 *      meta       (string) Optional grey text after the name (" · google.com"), cut first.
 *      meta_dir   (string) Optional direction of the meta text ('ltr' for domains).
 *      class      (string) Optional extra classes for the row.
 *  - total (int)   The whole the share is taken from (the range total, not only these rows).
 *  - class (string) Extra classes for the list.
 *
 * Example:
 *  View::load('components/objects/share-list', ['items' => [['label' => 'google.com', 'count' => 115, 'icon' => 'globe']], 'total' => 160]);
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

$items = isset($items) && is_array($items) ? $items : [];
$total = isset($total) ? intval($total) : 0;

if (empty($items)) {
    return;
}

$total   = max($total, array_sum(array_map('intval', array_column($items, 'count'))));
$classes = trim('wps-horizontal-bar wps-share-list ' . ($class ?? ''));

// wps_js.share_percent() + wps_js.format_percent(): one decimal, '.', no trailing ".0"
$wpsShareText = function ($value) {
    $value = round($value * 10) / 10;
    return (floor($value) == $value ? (string)intval($value) : number_format($value, 1, '.', '')) . '%';
};

// wps_js.formatNumber(): plain under 1,000 (by 2 significant digits), then K / M / B / T
$wpsCountText = function ($num) {
    $num = (float)$num;
    if ($num == 0) {
        return '0';
    }
    $exponent = intval(substr(strrchr(sprintf('%.1e', (int)$num), 'e'), 1));
    $k        = $exponent > 0 ? (int)floor(min($exponent, 14) / 3) : 0;
    if ($k < 1) {
        return number_format($num, 0, '.', '');
    }
    $short = abs((float)number_format($num / pow(10, $k * 3), 1, '.', ''));
    return $short . ['', 'K', 'M', 'B', 'T'][$k];
};

?>
<div class="<?php echo esc_attr($classes); ?>" role="list">
    <?php foreach ($items as $item) :
        $count   = intval($item['count'] ?? 0);
        $label   = (string) ($item['label'] ?? '');
        $value   = $total > 0 ? round($count / $total * 1000) / 10 : 0;
        $text    = $wpsShareText($value);
        $fill    = $value > 0 ? max(min($value, 100), 1.5) : 0;
        $icon    = $item['icon'] ?? '';
        $meta    = isset($item['meta']) ? trim((string)$item['meta']) : '';
        $metaDir = isset($item['meta_dir']) && in_array($item['meta_dir'], ['ltr', 'rtl', 'auto'], true) ? $item['meta_dir'] : 'auto';
        $rowCls  = trim('wps-horizontal-bar__item ' . ($item['class'] ?? ''));
        ?>
        <div class="<?php echo esc_attr($rowCls); ?>" role="listitem">
            <div class="wps-horizontal-bar__label-image-container">
                <?php if ($icon === 'globe') : ?>
                    <svg class="wps-horizontal-bar__image wps-share-list__icon" viewBox="0 0 16 16" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true" focusable="false"><circle cx="8" cy="8" r="6.4"/><path d="M1.6 8h12.8M8 1.6c1.7 1.8 2.5 3.9 2.5 6.4S9.7 12.6 8 14.4C6.3 12.6 5.5 10.5 5.5 8S6.3 3.4 8 1.6Z"/></svg>
                <?php elseif (is_array($icon) && !empty($icon['svg'])) : ?>
                    <?php \WP_Statistics\Components\View::load('components/objects/source-channel-icon', ['svg' => $icon['svg'], 'color' => $icon['color'] ?? '', 'class' => 'wps-horizontal-bar__image wps-share-list__icon wps-share-list__icon--color']); ?>
                <?php elseif (is_string($icon) && !empty($icon)) : ?>
                    <img class="wps-horizontal-bar__image" src="<?php echo esc_url($icon); ?>" alt="" width="16" height="16" loading="lazy">
                <?php endif; ?>
                <div class="wps-horizontal-bar__label" title="<?php echo esc_attr($label); ?>">
                    <?php if (!empty($item['url'])) : ?>
                        <a href="<?php echo esc_url($item['url']); ?>"<?php echo !empty($item['new_tab']) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html($label); ?></a>
                    <?php else : ?>
                        <?php echo esc_html($label); ?>
                    <?php endif; ?>
                </div>
                <?php if ($meta !== '') : ?>
                    <span class="wps-share-list__sep" aria-hidden="true">&middot;</span>
                    <span class="wps-share-list__meta" dir="<?php echo esc_attr($metaDir); ?>" title="<?php echo esc_attr($meta); ?>"><?php echo esc_html($meta); ?></span>
                <?php endif; ?>
                <?php if (!empty($item['external'])) {
                    View::load('components/objects/new-tab-link', ['url' => $item['external'], 'label' => __('Open site in a new tab', 'wp-statistics')]);
                } ?>
            </div>
            <div class="wps-horizontal-bar__data-percent-container">
                <div class="wps-horizontal-bar__data">
                    <span title="<?php echo esc_attr($count); ?>"><?php if (!empty($item['count_url'])) : ?><a href="<?php echo esc_url($item['count_url']); ?>"><?php echo esc_html($wpsCountText($count)); ?></a><?php else : ?><?php echo esc_html($wpsCountText($count)); ?><?php endif; ?></span>
                    <span><?php echo esc_html($text); ?></span>
                </div>
            </div>
            <div class="wps-horizontal-bar__track" aria-hidden="true"><div class="wps-horizontal-bar__background" style="width:<?php echo esc_attr($fill); ?>%"></div></div>
        </div>
    <?php endforeach; ?>
</div>
