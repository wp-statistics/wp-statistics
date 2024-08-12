<?php

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;

$order   = Request::get('order', 'desc');
$headers = [
    ['label' => esc_html__('Author', 'wp-statistics'), 'sort_url' => Helper::getTableColumnSortUrl('name'), 'order_class' => Request::compare('order_by', 'name') ? esc_attr($order) : ''],
    ['label' => esc_html__('Author\'s Page Views', 'wp-statistics'), 'sort_url' => Helper::getTableColumnSortUrl('page_views'), 'order_class' => !Request::has('order_by') || Request::compare('order_by', 'page_views') ? esc_attr($order) : ''],
    ['label' => esc_html__('Published Posts', 'wp-statistics'), 'sort_url' => Helper::getTableColumnSortUrl('total_posts'), 'order_class' => Request::compare('order_by', 'total_posts') ? esc_attr($order) : '']
];

$rows = array_map(function ($author) {
    return [
        'columns'        => [
            sprintf('<a href="%s" target="_blank" class="wps-table-ellipsis--name"><img src="%s" alt="%s" class="wps-avatar"/>  <span title="%s">%s</span></a>', esc_url(Menus::admin_url('author-analytics', ['type' => 'single-author', 'author_id' => $author->id])) , esc_url(get_avatar_url($author->id)), esc_attr($author->name), esc_attr($author->name), esc_html($author->name)),
            esc_html($author->page_views),
            esc_html($author->total_posts)
        ],
        'view_more_link' => get_author_posts_url($author->id),
        'view_more_text' => esc_html__('View Author Page', 'wp-statistics')
    ];
}, $data['authors']);

$args = [
    'headers'    => $headers,
    'rows'       => $rows,
    'pagination' => isset($pagination) ? $pagination : ''
];
View::load("components/tables/pages-table", $args);