<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\Leaderboard\LeaderboardPresenter;

// The Category Analytics page selects the taxonomy with 'tab'
$taxonomy    = Request::get('tab', Request::get('tx', 'category'));
$taxObject   = get_taxonomy($taxonomy);
$leaderboard = isset($data['rows']) ? $data : ['rows' => [], 'columns' => [], 'published_total' => 0, 'post_type' => []];
?>

<div class="wps-card wps-leaderboard-card">
    <?php View::load('components/objects/card-header', [
        'title'   => $title,
        'tooltip' => $tooltip ?? '',
        'context' => 'card',
    ]); ?>

    <?php View::load('components/leaderboard', [
        'rows'       => LeaderboardPresenter::termRows($leaderboard['rows']),
        'rankings'   => LeaderboardPresenter::termRankings($leaderboard),
        'columns'    => $leaderboard['columns'],
        'name_label' => $taxObject ? $taxObject->labels->singular_name : __('Category', 'wp-statistics'),
        'caption'    => $title,
        'note'       => LeaderboardPresenter::publishedNote($leaderboard['published_total'], $leaderboard['post_type']),
    ]); ?>

    <?php
    View::load("components/objects/view-more", [
        'href'  => Menus::admin_url('category-analytics', ['type' => 'report', 'tx' => $taxonomy]),
        'title' => __('See all categories', 'wp-statistics'),
    ]);
    ?>
</div>
