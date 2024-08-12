<?php

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;

$postType             = Request::get('pt', 'post');
$postTypeNameSingular = Helper::getPostTypeName($postType, true);
$order                = Request::get('order', 'desc');

$headers = [
    ['label' => Request::has('pt') ? esc_html($postTypeNameSingular) : esc_html__('Content', 'wp-statistics'), 'sort_url' => Helper::getTableColumnSortUrl('title'), 'order_class' => Request::compare('order_by', 'title') ? esc_attr($order) : ''],
    ['label' => esc_html__('Visitors', 'wp-statistics'), 'sort_url' => Helper::getTableColumnSortUrl('visitors'), 'order_class' => !Request::has('order_by') || Request::compare('order_by', 'visitors') ? esc_attr($order) : ''],
    ['label' => esc_html__('Views', 'wp-statistics'), 'sort_url' => Helper::getTableColumnSortUrl('views'), 'order_class' => Request::compare('order_by', 'views') ? esc_attr($order) : ''],
    ['label' => esc_html__('Words', 'wp-statistics'), 'sort_url' => Helper::getTableColumnSortUrl('words'), 'order_class' => Request::compare('order_by', 'words') ? esc_attr($order) : ''],
    ['label' => esc_html__('Published Date', 'wp-statistics'), 'sort_url' => Helper::getTableColumnSortUrl('date'), 'order_class' => Request::compare('order_by', 'date') ? esc_attr($order) : '']
];

$default_image_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="140" height="140" viewBox="0 0 140 140" fill="none">
    <g clip-path="url(#clip0_9208_25189)">
        <path d="M0 0H140V140H0V0Z" fill="#E0E0E0"/>
        <path d="M92 88H48C46.9 88 46 87.1 46 86V54C46 52.9 46.9 52 48 52H92C93.1 52 94 52.9 94 54V86C94 87.1 93.1 88 92 88ZM68.28 73.573L64.865 70.052L55.23 80.999H85.35L74.565 64.644L68.281 73.572L68.28 73.573ZM62.919 64.523C62.9189 64.0251 62.8208 63.5321 62.6302 63.0721C62.4396 62.6121 62.1603 62.1942 61.8081 61.8422C61.456 61.4901 61.038 61.2109 60.578 61.0204C60.118 60.8299 59.6249 60.7319 59.127 60.732C58.6291 60.7321 58.1361 60.8302 57.6761 61.0208C57.2161 61.2114 56.7982 61.4907 56.4462 61.8429C56.0941 62.195 55.8149 62.613 55.6244 63.073C55.4339 63.533 55.3359 64.0261 55.336 64.524C55.336 65.5296 55.7355 66.4939 56.4465 67.205C57.1576 67.916 58.1219 68.3155 59.1275 68.3155C60.1331 68.3155 61.0975 67.916 61.8085 67.205C62.5195 66.4939 62.919 65.5286 62.919 64.523Z" fill="#C2C2C2"/>
    </g>
    <defs>
        <clipPath id="clip0_9208_25189">
            <rect width="140" height="140" fill="white"/>
        </clipPath>
    </defs>
</svg>';

$rows = array_map(function ($post) use ($default_image_svg) {
    return [
        'columns'        => [
            sprintf(
                '<a target="_blank" href="%s" class="wps-table-ellipsis--name" title="%s">%s <span>%s</span></a>',
                esc_url(Menus::admin_url('content-analytics', [
                    'type'    => 'single',
                    'post_id' => $post->post_id,
                    'from'    => Request::get('from', date('Y-m-d', strtotime('-30 days'))),
                    'to'      => Request::get('to', date('Y-m-d'))
                ])),
                esc_html($post->title),
                has_post_thumbnail($post->post_id)
                    ? sprintf(
                    '<img src="%s" class="wps-pages-image" alt="%s">',
                    esc_url(get_the_post_thumbnail_url($post->post_id)),
                    esc_attr($post->title)
                )
                    : $default_image_svg,
                esc_html($post->title)
            ),
            esc_html(number_format_i18n($post->visitors)),
            esc_html(number_format_i18n($post->views)),
            esc_html(number_format_i18n($post->words)),
            esc_html(date(Helper::getDefaultDateFormat(), strtotime($post->date)) . ' ' . esc_html__('at', 'wp-statistics') . ' ' . esc_html(date('H:i', strtotime($post->date))))
        ],
        'view_more_link' => get_the_permalink($post->post_id),
        'view_more_text' => esc_html__('View Content', 'wp-statistics')
    ];
}, $data['posts']);

$args = [
    'headers'    => $headers,
    'rows'       => $rows,
    'pagination' => isset($pagination) ? $pagination : ''
];
View::load("components/tables/pages-table", $args);