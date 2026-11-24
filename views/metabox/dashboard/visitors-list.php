<?php
/**
 * Visitor list for the WP Dashboard Visitors card (Latest, Most active and Online now
 * segments, views/metabox/visitors.php). Same shared table as the Overview page
 * (views/components/tables/visitor-table.php); in the narrow dashboard column it keeps the
 * pill, the pages and the sorted number (container query), so it never scrolls sideways.
 *
 * Args:
 *  - data  (VisitorDecorator[]) Rows, already limited by the query.
 *  - mode  (string) 'latest', 'active' or 'online'.
 *  - empty (array)  Args for metabox/dashboard/empty.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

$mode  = isset($mode) && in_array($mode, ['latest', 'active', 'online'], true) ? $mode : 'latest';
$data  = !empty($data) && is_array($data) ? $data : [];
$empty = isset($empty) && is_array($empty) ? $empty : [];
?>
<?php if (!empty($data)) : ?>
    <?php
    View::load('components/tables/visitor-table', [
        'visitors'   => $data,
        'sort'       => $mode === 'active' ? 'views' : 'last',
        'exit_label' => $mode === 'online' ? __('Current page', 'wp-statistics') : '',
        'class'      => 'wps-dash-list wps-dash-list--' . $mode,
    ]);
    ?>
<?php else : ?>
    <?php View::load('metabox/dashboard/empty', $empty); ?>
<?php endif; ?>
