<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

// Shared visitor table (views/components/tables/visitor-table.php), sorted by last view.
?>

    <div class="inside">
        <?php
        View::load('components/tables/visitor-table', [
            'visitors'   => !empty($data) ? $data : [],
            'sort'       => 'last',
            'exit_label' => __('Current page', 'wp-statistics'),
            'empty'      => ['message' => __('No one is online right now.', 'wp-statistics'), 'range' => false, 'icon' => 'activity'],
        ]);
        ?>
    </div>
<?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
