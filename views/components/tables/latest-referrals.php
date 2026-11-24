<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;

// Shared visitor table (views/components/tables/visitor-table.php), sorted by last view.
?>

<div class="inside">
    <?php
    View::load('components/tables/visitor-table', [
        'visitors' => !empty($visitors) ? $visitors : [],
        'sort'     => 'last',
    ]);
    ?>
</div>
