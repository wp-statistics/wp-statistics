<?php
/**
 * Legacy no-data partial. Kept for add-ons (Real-Time, Data Plus) that load it with
 * ['title' => ..., 'url' => ...]. It now renders the shared empty state; the old
 * illustration `url` is ignored and replaced by the empty-state icon.
 *
 * New code should load components/objects/empty-state directly.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

$rangeMessages = [
    __('No data found for this date range.', 'wp-statistics'),
    __('Data coming soon!', 'wp-statistics'),
];

$args = [
    'class' => 'wps--no-data__container',
    'icon'  => 'chart',
];

if (isset($title) && $title !== '') {
    $args['message'] = $title;

    // Only date-range messages get the range line under them.
    if (!in_array($title, $rangeMessages, true)) {
        $args['range'] = false;
    }
}

if (isset($date)) {
    $args['date'] = $date;
}

// Date-range messages are recomputed from the range, so "Data coming soon!" only shows when the
// whole range starts today or later, and the dates are printed under the message.
if (isset($args['message']) && in_array($args['message'], $rangeMessages, true)) {
    $range           = $args['date'] ?? \WP_Statistics\Components\DateRange::get();
    $args['message'] = \WP_STATISTICS\Helper::getNoDataMessage($range['to'] ?? '', $range['from'] ?? '');
    $args['range']   = \WP_STATISTICS\Helper::formatDateRangeLabel($range['from'] ?? '', $range['to'] ?? '');
}

View::load('components/objects/empty-state', $args);
