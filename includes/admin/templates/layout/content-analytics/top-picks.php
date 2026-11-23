<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\Leaderboard\LeaderboardPresenter;

$postType  = Request::get('tab', 'post');
$reportUrl = function ($post) {
    return add_query_arg(['type' => 'single', 'post_id' => $post->ID]);
};

$lists = [
    [
        'key'   => 'views',
        'label' => __('Most viewed', 'wp-statistics'),
        'items' => LeaderboardPresenter::contentItems($data['top_viewing'] ?? [], 'views', $reportUrl),
    ],
];

if (post_type_supports($postType, 'comments')) {
    $lists[] = [
        'key'   => 'comments',
        'label' => __('Most commented', 'wp-statistics'),
        'items' => LeaderboardPresenter::contentItems($data['top_commented'] ?? [], 'comments', $reportUrl),
    ];
}

$lists[] = [
    'key'   => 'newest',
    'label' => __('Newest', 'wp-statistics'),
    'items' => LeaderboardPresenter::contentItems($data['recent'] ?? [], 'newest', $reportUrl),
];

$postTypeName = Helper::getPostTypeName($postType);

View::load('components/content-rank-list', [
    'id'      => 'wps-content-top-contents',
    'title'   => $title,
    'tooltip' => $tooltip ?? '',
    'lists'   => $lists,
    'see_all' => [
        'href'  => add_query_arg(['tab' => 'top', 'pt' => $postType], Menus::admin_url('pages')),
        /* translators: %s: plural name in lower case, a post type ("posts") or a taxonomy ("categories"). */
        'title' => sprintf(__('See all %s', 'wp-statistics'), function_exists('mb_strtolower') ? mb_strtolower($postTypeName) : strtolower($postTypeName)),
    ],
]);
