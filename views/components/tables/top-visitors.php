<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

// Shared visitor table (views/components/tables/visitor-table.php), sorted by views.
?>

    <div class="inside">
        <?php
        View::load('components/tables/visitor-table', [
            'visitors'   => !empty($data) ? $data : [],
            'sort'       => 'views',
            'views_hint' => __('Views in one day. Visitors get a new ID each day, so each day counts on its own.', 'wp-statistics'),
        ]);
        ?>
    </div>
<?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
