<?php

use WP_STATISTICS\Admin_Template;

$contentSingle = [
    'campaign' => 'content',
    'src'      => 'assets/images/locked/content-single.jpg',
];
Admin_Template::get_template(['layout/partials/data-plus-locked-page'], $contentSingle);