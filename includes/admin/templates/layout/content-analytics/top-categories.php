<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\Leaderboard\LeaderboardPresenter;

$postType   = !empty($post_type) && is_string($post_type) ? $post_type : Request::get('tab', 'post');
$authorId   = !empty($author_id) ? (int) $author_id : 0;
$taxonomies = isset($taxonomies) && is_array($taxonomies) ? $taxonomies : [];
$idPrefix   = isset($id_prefix) ? sanitize_html_class($id_prefix) : 'content-tax';

// Only the leaderboard shape (taxonomy => ['rows' => ...]) is drawn
$taxonomies = array_filter($taxonomies, function ($item) {
    return is_array($item) && !empty($item['rows']);
});
?>

<div class="wps-card wps-leaderboard-card">
    <?php View::load('components/objects/card-header', [
        'title'   => $title,
        'tooltip' => $tooltip ?? '',
        'context' => 'card',
    ]); ?>

    <?php if (!empty($taxonomies)) : ?>
        <div class="wps-tabs wps-leaderboard-tabs<?php echo count($taxonomies) === 1 ? ' wps-leaderboard-tabs--single' : ''; ?>">
            <?php
            $counter = 1;
            foreach ($taxonomies as $taxonomy => $leaderboard) :
                $taxObject = get_taxonomy($taxonomy);
                $taxName   = $taxObject ? $taxObject->labels->menu_name : $taxonomy;
                $tabId     = $idPrefix . '-' . $counter;
                ?>
                <input type="radio" name="<?php echo esc_attr($idPrefix); ?>" id="<?php echo esc_attr($tabId) ?>" <?php checked($counter, 1) ?>>
                <label for="<?php echo esc_attr($tabId) ?>"><?php echo esc_html($taxName) ?></label>

                <div class="wps-tabs__content">
                    <?php View::load('components/leaderboard', [
                        'rows'       => LeaderboardPresenter::termRows($leaderboard['rows']),
                        'rankings'   => LeaderboardPresenter::termRankings($leaderboard),
                        'columns'    => $leaderboard['columns'],
                        'name_label' => $taxObject ? $taxObject->labels->singular_name : $taxName,
                        'caption'    => $title . ': ' . $taxName,
                    ]); ?>
                    <?php View::load("components/objects/view-more", [
                        'href'  => Menus::admin_url('category-analytics', array_filter(['type' => 'report', 'tx' => $taxonomy, 'pt' => $postType, 'author_id' => $authorId])),
                        /* translators: %s: plural name in lower case, a post type ("posts") or a taxonomy ("categories"). */
                        'title' => sprintf(__('See all %s', 'wp-statistics'), function_exists('mb_strtolower') ? mb_strtolower($taxName) : strtolower($taxName)),
                    ]); ?>
                </div>
                <?php $counter++; ?>
            <?php endforeach; ?>
        </div>
    <?php else : ?>
        <?php View::load('components/objects/empty-state'); ?>
    <?php endif; ?>
</div>
