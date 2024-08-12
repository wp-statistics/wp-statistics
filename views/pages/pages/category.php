<?php

use WP_STATISTICS\Menus;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;

$taxonomy = Request::get('tx', 'category');
$order    = Request::get('order', 'desc');
$taxName  = Helper::getTaxonomyName(Request::get('tx', 'category'), true);
$headers  = [
    ['label' => esc_html($taxName), 'sort_url' => Helper::getTableColumnSortUrl('name'), 'order_class' => Request::compare('order_by', 'name') ? esc_attr($order) : ''],
    ['label' => sprintf(esc_html__('%s Page Views', 'wp-statistics'), $taxName), 'sort_url' => Helper::getTableColumnSortUrl('views'), 'order_class' => !Request::has('order_by') || Request::compare('order_by', 'views') ? esc_attr($order) : ''],
    ['label' => esc_html__('Total Published Posts', 'wp-statistics'), 'sort_url' => Helper::getTableColumnSortUrl('post_count'), 'order_class' => Request::compare('order_by', 'post_count') ? esc_attr($order) : '']
];

$rows = array_map(function ($category) use ($taxName) {
    return [
        'columns'        => [
            sprintf('<a href="%s" target="_blank" class="wps-table-ellipsis--name"><span title="%s">%s</span></a>', esc_url(Menus::admin_url('category-analytics', ['type' => 'single', 'term_id' => $category['term_id']])), esc_attr($category['term_name']), esc_html($category['term_name'])),
            esc_html(number_format_i18n($category['views'])),
            esc_html(number_format_i18n($category['posts_count']))
        ],
        'view_more_link' => get_term_link(intval($category['term_id'])),
        'view_more_text' => sprintf(esc_html__('View %s Page', 'wp-statistics'), $taxName)
    ];
}, $data['categories'][$taxonomy]);

$args = [
    'headers'    => $headers,
    'rows'       => $rows,
    'pagination' => isset($pagination) ? $pagination : ''
];

if ($showLockedPage) {
    $locked_args = [
        'campaign' => 'pages',
        'src'      => 'assets/images/locked/category-pages.jpg',
    ];
    View::load("components/locked-page", $locked_args);
} else {
    View::load("components/tables/pages-table", $args);
}
