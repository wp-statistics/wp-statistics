<?php

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;

$queryKey  = 'pt';
$postTypes = array_values(Helper::get_list_post_type());
$baseUrl   = remove_query_arg([$queryKey, 'pid']); // remove post type and post id from query
?>


<?php

$args = [
    'title'          => __('Post Type', 'wp-statistics'),
    'selectedOption' => Request::get($queryKey, 'post'),
    'data'           => $postTypes,
    'baseUrl'        => $baseUrl,
    'type'           => 'post-type'
];

View::load("components/objects/header-filter-select", $args);
?>
