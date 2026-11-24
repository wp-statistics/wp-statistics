<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\Leaderboard\LeaderboardPresenter;

// Older callers passed ['viewing' => ..., 'publishing' => ...]; only the leaderboard shape is drawn now.
$leaderboard = isset($data['rows']) ? $data : ['rows' => [], 'columns' => [], 'published_total' => 0, 'post_type' => []];
?>

<div class="wps-card wps-leaderboard-card">
    <?php View::load('components/objects/card-header', [
        'title'   => $title,
        'tooltip' => $tooltip ?? '',
        'context' => 'card',
    ]); ?>

    <?php View::load('components/leaderboard', [
        'rows'       => LeaderboardPresenter::authorRows($leaderboard['rows']),
        'rankings'   => LeaderboardPresenter::authorRankings($leaderboard),
        'columns'    => $leaderboard['columns'],
        'name_label' => __('Author', 'wp-statistics'),
        'caption'    => $title,
        'media'      => 'avatar',
        'note'       => LeaderboardPresenter::publishedNote($leaderboard['published_total'], $leaderboard['post_type']),
    ]); ?>

    <?php
    View::load("components/objects/view-more", [
        'href'  => Menus::admin_url('author-analytics', ['type' => 'authors']),
        'title' => __('See all authors', 'wp-statistics'),
    ]);
    ?>
</div>
