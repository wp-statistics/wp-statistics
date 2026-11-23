<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\Leaderboard\LeaderboardPresenter;

$reportUrl = function ($post) {
    return Menus::admin_url('content-analytics', ['type' => 'single', 'post_id' => $post->ID]);
};

$lists = [
    [
        'key'   => 'views',
        'label' => __('Most viewed', 'wp-statistics'),
        'items' => LeaderboardPresenter::contentItems($data['top_viewing'] ?? [], 'views', $reportUrl),
    ],
    [
        'key'   => 'comments',
        'label' => __('Most commented', 'wp-statistics'),
        'items' => LeaderboardPresenter::contentItems($data['top_commented'] ?? [], 'comments', $reportUrl),
    ],
    [
        'key'   => 'newest',
        'label' => __('Newest', 'wp-statistics'),
        'items' => LeaderboardPresenter::contentItems($data['recent'] ?? [], 'newest', $reportUrl),
    ],
];

$seeAll = [];
if (Menus::in_page('category-analytics') && !Request::compare('type', 'single')) {
    $seeAll = [
        'href'  => add_query_arg(['tab' => 'top'], Menus::admin_url('pages')),
        'title' => __('See all contents', 'wp-statistics'),
    ];
}

View::load('components/content-rank-list', [
    'id'      => 'wps-category-top-contents',
    'title'   => $title,
    'tooltip' => $tooltip ?? '',
    'lists'   => $lists,
    'see_all' => $seeAll,
]);
