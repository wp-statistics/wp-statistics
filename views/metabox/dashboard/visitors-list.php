<?php
/**
 * Visitor list for the WP Dashboard widgets: Latest Visitors, Most Active Visitors
 * and Currently Online. Same shared table as the Overview page
 * (views/components/tables/visitor-table.php); in the narrow dashboard column it keeps the
 * pill, the pages and the sorted number (container query), so it never scrolls sideways.
 *
 * Args:
 *  - data  (VisitorDecorator[]) Rows, already limited by the query.
 *  - mode  (string) 'latest', 'active' or 'online'.
 *  - total (int)    Online only: everyone online now (feeds the header counter).
 *  - empty (array)  Args for metabox/dashboard/empty.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

$mode  = isset($mode) && in_array($mode, ['latest', 'active', 'online'], true) ? $mode : 'latest';
$data  = !empty($data) && is_array($data) ? $data : [];
$empty = isset($empty) && is_array($empty) ? $empty : [];
?>
<?php if ($mode === 'online' && !empty($total)) : ?>
    <span class="wps-currently-online"><?php echo esc_html($total) ?></span>
<?php endif; ?>
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
