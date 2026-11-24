<?php
/**
 * One-line empty state for WP Dashboard widgets (compact set).
 *
 * Args (all optional):
 *  - title (string) The line, e.g. "No one online right now". Defaults to the date-range message.
 *  - hint  (string) Short muted text after it. Defaults to the formatted date range.
 *  - date  (array)  ['from' => 'Y-m-d', 'to' => 'Y-m-d'] for the defaults.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Helper;

$date = isset($date) && is_array($date) ? $date : [];

if (!isset($title)) {
    $title = Helper::getNoDataMessage($date['to'] ?? '', $date['from'] ?? '');
}

if (!isset($hint)) {
    $hint = (!empty($date['from']) && !empty($date['to'])) ? Helper::formatDateRangeLabel($date['from'], $date['to']) : '';
}
?>
<div class="wps-dash-empty" role="status">
    <strong class="wps-dash-empty__title"><?php echo esc_html(wp_strip_all_tags($title)); ?></strong>
    <?php if (!empty($hint)) : ?>
        <span class="wps-dash-empty__hint"><?php echo esc_html($hint); ?></span>
    <?php endif; ?>
</div>
