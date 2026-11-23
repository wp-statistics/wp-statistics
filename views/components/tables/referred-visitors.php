<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

// Shared visitor table (views/components/tables/visitor-table.php), sorted by last view.
?>

    <div class="inside">
        <?php
        View::load('components/tables/visitor-table', [
            'visitors'   => !empty($visitors) ? $visitors : [],
            'sort'       => 'last',
            'views_hint' => __('Views in one day. Visitors get a new ID each day, so each day counts on its own.', 'wp-statistics'),
        ]);
        ?>
    </div>

<?php
echo $pagination ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
