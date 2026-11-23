<?php
/**
 * Empty state for any card or table: icon, one sentence, the date range.
 *
 * Args (all optional):
 *  - message (string)       Defaults to Helper::getNoDataMessage() for the active range.
 *  - date    (array)        ['from' => 'Y-m-d', 'to' => 'Y-m-d']. Defaults to the page's DateRange.
 *                           Overview widgets pass their own filter range here.
 *  - range   (string|false) Text under the message. Defaults to the formatted date range when the
 *                           message is a date-range message; false hides it.
 *  - icon    (string)       'chart' (default), 'activity' or 'none'.
 *  - class   (string)       Extra classes.
 *
 * Example:
 *  View::load('components/objects/empty-state');
 *  View::load('components/objects/empty-state', ['date' => $filters['date']]);
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;

$date = isset($date) && is_array($date) ? $date : [];
if (empty($date['from']) || empty($date['to'])) {
    $date = array_merge(DateRange::get(), array_filter($date));
}

$isRangeMessage = !isset($message);
if (!isset($message)) {
    $message = Helper::getNoDataMessage($date['to'] ?? '', $date['from'] ?? '');
}

if (!isset($range)) {
    $range = $isRangeMessage ? Helper::formatDateRangeLabel($date['from'] ?? '', $date['to'] ?? '') : false;
}

$icon    = $icon ?? 'chart';
$classes = trim('wps-empty-state ' . ($class ?? ''));
?>
<div class="<?php echo esc_attr($classes); ?>" role="status">
    <?php if ($icon === 'chart') : ?>
        <svg class="wps-empty-state__icon" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>
    <?php elseif ($icon === 'activity') : ?>
        <svg class="wps-empty-state__icon" viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><circle cx="12" cy="12" r="8"/><path d="M12 8v4l2.5 2.5"/></svg>
    <?php endif; ?>
    <?php // dir="auto": an untranslated (English) message keeps its full stop at its end on RTL screens. ?>
    <p class="wps-empty-state__message" dir="auto"><?php echo esc_html($message); ?></p>
    <?php if (!empty($range)) : ?>
        <p class="wps-empty-state__range" dir="auto"><?php echo esc_html($range); ?></p>
    <?php endif; ?>
</div>
