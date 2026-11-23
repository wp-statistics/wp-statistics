<?php
/**
 * Body of the Visitors card (Overview page and WP Dashboard): the list of one segment.
 * The header switch (Latest | Most active | Online now) is drawn by overview.js from the
 * metabox response options; this view is the tab panel it controls.
 *
 * Args:
 *  - segment   (string) 'latest', 'active' or 'online'.
 *  - data      (VisitorDecorator[]) Rows, already limited by the query.
 *  - filters   (array)  Most active: ['date' => ['from', 'to', ...]].
 *  - dashboard (bool)   WP Dashboard (compact list and one-line empty state).
 *  - label     (string) Name of the segment (accessible name of the panel).
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

$segment   = isset($segment) && in_array($segment, ['latest', 'active', 'online'], true) ? $segment : 'latest';
$data      = !empty($data) && is_array($data) ? $data : [];
$filters   = isset($filters) && is_array($filters) ? $filters : [];
$dashboard = !empty($dashboard);
$timeframe = intval(apply_filters('wp_statistics_online_visitors_timeframe', 5));
?>
<div class="wps-visitors-panel wps-visitors-panel--<?php echo esc_attr($segment); ?>" id="wps-visitors-panel" role="tabpanel" aria-label="<?php echo esc_attr($label ?? ''); ?>" data-segment="<?php echo esc_attr($segment); ?>">
    <?php
    if ($dashboard) {
        if ($segment === 'active') {
            $empty = ['date' => $filters['date'] ?? []];
        } elseif ($segment === 'online') {
            $empty = [
                'title' => __('No one online right now', 'wp-statistics'),
                /* translators: %s: Number of minutes. */
                'hint'  => sprintf(_n('Visitors from the last %s minute show here.', 'Visitors from the last %s minutes show here.', $timeframe, 'wp-statistics'), number_format_i18n($timeframe)),
            ];
        } else {
            $empty = [
                'title' => __('No visitors yet', 'wp-statistics'),
                'hint'  => __('New visits show here as they happen.', 'wp-statistics'),
            ];
        }

        View::load('metabox/dashboard/visitors-list', [
            'data'  => $data,
            'mode'  => $segment,
            'empty' => $empty,
        ]);
    } else {
        if ($segment === 'active') {
            $empty = ['date' => $filters['date'] ?? []];
        } elseif ($segment === 'online') {
            $empty = [
                'message' => __('No one online right now', 'wp-statistics'),
                /* translators: %s: Number of minutes. */
                'range'   => sprintf(_n('Visitors from the last %s minute show here.', 'Visitors from the last %s minutes show here.', $timeframe, 'wp-statistics'), number_format_i18n($timeframe)),
                'icon'    => 'activity',
            ];
        } else {
            $empty = ['message' => __('No visitors recorded yet.', 'wp-statistics'), 'range' => false, 'icon' => 'activity'];
        }

        // Shared visitor table (views/components/tables/visitor-table.php): Most active by views, the others by last view.
        View::load('components/tables/visitor-table', [
            'visitors'   => $data,
            'sort'       => $segment === 'active' ? 'views' : 'last',
            'exit_label' => $segment === 'online' ? __('Current page', 'wp-statistics') : '',
            'empty'      => $empty,
        ]);
    }
    ?>
</div>
