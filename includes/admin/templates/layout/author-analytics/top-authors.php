<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\Leaderboard\LeaderboardPresenter;

$postType    = Request::get('pt', 'post');
$leaderboard = isset($data['leaderboard']) ? $data['leaderboard'] : ['rows' => [], 'columns' => [], 'published_total' => 0, 'post_type' => $postType];
?>

<div class="wps-card wps-leaderboard-card">
    <?php View::load('components/objects/card-header', [
        'title'   => $title,
        'tooltip' => $tooltip ?? '',
        'context' => 'card',
    ]); ?>

    <?php View::load('components/leaderboard', [
        'rows'       => LeaderboardPresenter::authorRows($leaderboard['rows'], $postType),
        'rankings'   => LeaderboardPresenter::authorRankings($leaderboard, $postType),
        'columns'    => $leaderboard['columns'],
        'name_label' => __('Author', 'wp-statistics'),
        'caption'    => $title,
        'media'      => 'avatar',
        'note'       => LeaderboardPresenter::publishedNote($leaderboard['published_total'], $postType),
    ]); ?>

    <?php
    View::load("components/objects/view-more", [
        'href'  => Menus::admin_url('author-analytics', ['type' => 'authors', 'pt' => $postType]),
        'title' => __('See all authors', 'wp-statistics'),
    ]);
    ?>
</div>
