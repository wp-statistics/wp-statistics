<?php

use WP_STATISTICS\Admin_Template;

$catSingle = [
    'campaign' => 'category-analytics',
    'src'  => 'assets/images/locked/category.jpg',
];
Admin_Template::get_template(['layout/partials/data-plus-locked-page'], $catSingle);
