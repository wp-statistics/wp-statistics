<?php

use WP_STATISTICS\Admin_Template;

$geographic = [
    'campaign' => 'author',
    'src'      => 'assets/images/locked/author.jpg',
];
Admin_Template::get_template(['layout/partials/data-plus-locked-page'], $geographic);
