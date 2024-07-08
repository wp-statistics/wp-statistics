<?php

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;

$taxonomy  = Request::get('tx', 'category');
$postType  = Helper::getPostTypesByTaxonomy($taxonomy);
$postTypes = get_post_types_by_support('comments');
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
            <div class="wps-category-tabs">
                <input type="radio" name="category-views" id="category-views" checked="checked">
                <label for="category-views"><?php esc_html_e('Views', 'wp-statistics') ?></label>
                <div class="wps-category-tabs__content">
                    <?php
                    if (!empty($data['viewing'][$taxonomy])) {
                        $counter = 1;
                        foreach ($data['viewing'][$taxonomy] as $category) : ?>
                            <a class="wps-category-tabs__item" href="<?php echo esc_url(Menus::admin_url('category-analytics', ['type' => 'single', 'term_id' => $category['term_id']])) ?>">
                                <div class="wps-category-tabs__item--content">
                                    <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text"><?php echo esc_html($category['term_name']); ?></span></h3>
                                    <span><?php echo esc_html(Helper::formatNumberWithUnit($category['views'])); ?><?php esc_html_e(' page views', 'wp-statistics') ?></span>
                                </div>
                            </a>
                            <?php $counter++;
                        endforeach;
                    } else {
                        ?><div class="o-wrap o-wrap--no-data"><p><?php esc_html_e('No recent data available.', 'wp-statistics') ?></p></div><?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="wps-flex-half">
            <div class="wps-category-tabs">
                <input type="radio" name="category-publishing" id="category-publishing" checked="checked">
                <label for="category-publishing"><?php esc_html_e('Publishing', 'wp-statistics') ?></label>
                <div class="wps-category-tabs__content">
                    <?php
                    if (!empty($data['publishing'])) {
                        $counter = 1;
                        foreach ($data['publishing'] as $term) : ?>
                            <a class="wps-category-tabs__item" href="<?php echo esc_url(Menus::admin_url('category-analytics', ['type' => 'single', 'term_id' => $term->term_id])) ?>">
                                <div class="wps-category-tabs__item--content">
                                    <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text"><?php echo esc_html($term->term_name); ?></span></h3>
                                    <span><?php echo esc_html(Helper::formatNumberWithUnit($term->posts_count)); ?><?php esc_html_e(' contents published', 'wp-statistics') ?></span>
                                </div>
                            </a>
                            <?php $counter++;
                        endforeach;
                    } else {
                        ?><div class="o-wrap o-wrap--no-data"><p><?php esc_html_e('No recent data available.', 'wp-statistics') ?></p></div><?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="c-footer">
        <div class="c-footer__more">
            <a href="" class="c-footer__more__link">
                <?php echo esc_html__('See all categories', 'wp-statistics'); ?>
            </a>
        </div>
    </div>
</div>