<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

?>
<?php if (!empty($data['total'])) : ?>
    <span class="wps-currently-online"><?php echo esc_html($data['total']) ?></span>
<?php endif; ?>
<?php if (!empty($data['visitors'])) : ?>
    <?php
    // Shared visitor table (views/components/tables/visitor-table.php), sorted by last view.
    View::load('components/tables/visitor-table', [
        'visitors'   => $data['visitors'],
        'sort'       => 'last',
        'exit_label' => __('Current page', 'wp-statistics'),
    ]);
    ?>
<?php else : ?>
    <?php
    View::load("components/objects/no-data", [
        'url'   => WP_STATISTICS_URL . 'assets/images/no-data/vector-4.svg',
        'title' => __('No activity right now.', 'wp-statistics')
    ]);
    ?>
<?php endif; ?>
