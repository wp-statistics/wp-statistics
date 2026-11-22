<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

/**
 * Visitors table (Visitors, Logged-in Users; Data Plus single-post visitors on older add-on versions).
 * Renders the shared visitor table (views/components/tables/visitor-table.php), sorted by last view.
 *
 * Args kept for add-ons: data, pagination, single_post (date column = page view time),
 * hide_entry_page_column + hide_latest_page_column (both: no Pages column), open_links_in_new_tab.
 */
$hidePages = !empty($hide_entry_page_column) && !empty($hide_latest_page_column);
?>

    <div class="inside">
        <?php
        View::load('components/tables/visitor-table', [
            'visitors'    => !empty($data) ? $data : [],
            'sort'        => 'last',
            'show_pages'  => !$hidePages,
            'date_source' => !empty($single_post) ? 'page_view' : 'last_view',
            'date_label'  => !empty($single_post) ? __('Page view', 'wp-statistics') : '',
            'views_hint'  => __('Views in one day. Visitors get a new ID each day, so each day counts on its own.', 'wp-statistics'),
            'target'      => !empty($open_links_in_new_tab) ? '_blank' : '',
        ]);
        ?>
    </div>
<?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
