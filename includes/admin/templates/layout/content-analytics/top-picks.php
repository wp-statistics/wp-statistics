<?php

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;

$postType = Request::get('tab', 'post');
?>

<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title) ?>
            <?php if ($tooltip): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>
    <div class="wps-flex-container">
        <div class="wps-flex-half">
            <div class="wps-tabs">
                <input type="radio" name="tabs" id="content-most-popular" checked="checked">
                <label for="content-most-popular"><?php esc_html_e('Most Popular', 'wp-statistics') ?></label>
                <div class="wps-tabs__content">
                    <?php
                    if (!empty($data['top_viewing'])) {
                        $counter = 1;

                        foreach ($data['top_viewing'] as $post) {
                            $item = [
                                'href'        => add_query_arg(['type' => 'single', 'post_id' => $post->ID]),
                                'counter'     => $counter,
                                'count'       => Helper::formatNumberWithUnit($post->views),
                                'count_title' => __('views', 'wp-statistics'),
                                'post'        => $post,
                            ];
                            View::load("components/tabs-item", $item);
                            $counter++;
                        }
                    } else {
                        ?>
                        <div class="o-wrap o-wrap--no-data"><p><?php esc_html_e('No recent data available.', 'wp-statistics') ?></p></div><?php
                    }
                    ?>
                </div>


                <?php if (post_type_supports($postType, 'comments')) : ?>
                    <input type="radio" name="tabs" id="content-most-commented">
                    <label for="content-most-commented"><?php esc_html_e('Most Commented', 'wp-statistics') ?></label>
                    <div class="wps-tabs__content">
                        <?php
                        if (!empty($data['top_commented'])) {
                            $counter = 1;

                            foreach ($data['top_commented'] as $post) {
                                $item = [
                                    'href'        => add_query_arg(['type' => 'single', 'post_id' => $post->ID]),
                                    'counter'     => $counter,
                                    'count'       => Helper::formatNumberWithUnit($post->comments),
                                    'count_title' => __('comments', 'wp-statistics'),
                                    'post'        => $post,
                                ];
                                View::load("components/tabs-item", $item);
                                $counter++;
                            }
                        } else {
                            ?>
                            <div class="o-wrap o-wrap--no-data"><p><?php esc_html_e('No recent data available.', 'wp-statistics') ?></p></div><?php
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="wps-flex-half">
            <div class="wps-tabs">
                <input type="radio" name="side-tabs" id="content-recent" checked="checked">
                <label for="content-recent"><?php esc_html_e('Recent', 'wp-statistics') ?></label>
                <div class="wps-tabs__content">
                    <?php
                    if (!empty($data['recent'])) {
                        $counter = 1;

                        foreach ($data['recent'] as $post) {
                            $item = [
                                'href'        => add_query_arg(['type' => 'single', 'post_id' => $post->ID]),
                                'counter'     => $counter,
                                'count'       => Helper::formatNumberWithUnit($post->views),
                                'count_title' => __('views', 'wp-statistics'),
                                'post'        => $post,
                            ];
                            View::load("components/tabs-item", $item);
                            $counter++;
                        }
                    } else {
                        ?>
                        <div class="o-wrap o-wrap--no-data"><p><?php esc_html_e('No recent data available.', 'wp-statistics') ?></p></div><?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <?php
    View::load("components/objects/view-more", [
        'href'  => add_query_arg(['tab' => 'top', 'pt' => $postType], Menus::admin_url('pages')),
        'title' => sprintf(esc_html__('See all %s', 'wp-statistics'), strtolower(Helper::getPostTypeName($postType))),
    ]);
    ?>

</div>