<?php

use WP_STATISTICS\Admin_Template;

$postType = [
    'campaign' => 'content',
    'src'      => 'assets/images/locked/content.jpg',
];
Admin_Template::get_template(['layout/partials/data-plus-locked-page'], $postType);