<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

// Shared visitor table (views/components/tables/visitor-table.php), sorted by views.
?>
<?php if (!empty($data)) : ?>
    <?php View::load('components/tables/visitor-table', ['visitors' => $data, 'sort' => 'views']); ?>
<?php else : ?>
    <?php
    $title = esc_html__('No data found for this date range.', 'wp-statistics');
    if (!empty($isTodayOrFutureDate)) {
        $title = esc_html__('Data coming soon!', 'wp-statistics');
    }
    View::load("components/objects/no-data", [
        'title' => $title
    ]);
    ?>
<?php endif; ?>
