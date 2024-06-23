<?php

use WP_STATISTICS\Admin_Template;

$geographic = [
    'campaign' => 'geographic',
    'src'  => 'assets/images/locked/geographic-single.jpg',
];
Admin_Template::get_template(['layout/partials/data-plus-locked-page'], $geographic);
