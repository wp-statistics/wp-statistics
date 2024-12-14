<?php

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;

$taxonomy  = Request::get('tx', 'category');
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
                <input type="radio" name="category-views" id="category-views" checked="checked">
                <label for="category-views"><?php esc_html_e('Views', 'wp-statistics') ?></label>
                <div class="wps-tabs__content">
                    <?php
                    if (!empty($data['viewing'])) {
                        $counter = 1;
                        foreach ($data['viewing'] as $term) : ?>
                            <a class="wps-tabs-item" href="<?php echo esc_url(Menus::admin_url('category-analytics', ['type' => 'single', 'term_id' => $term->term_id])) ?>">
                                <div class="wps-content-tabs__item--content">
                                    <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text"><?php echo esc_html($term->term_name); ?></span></h3>
                                    <span>
                                        <?php echo esc_html(Helper::formatNumberWithUnit($term->views));?> <?php esc_html_e('content views', 'wp-statistics') ?>
                                    </span>
                                </div>
                            </a>
                            <?php $counter++;
                        endforeach;
                    } else {
                        ?>
                        <div class="o-wrap o-wrap--no-data"><p><?php esc_html_e('No recent data available.', 'wp-statistics') ?></p></div><?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="wps-flex-half">
            <div class="wps-tabs">
                <input type="radio" name="category-publishing" id="category-publishing" checked="checked">
                <label for="category-publishing"><?php esc_html_e('Publishing', 'wp-statistics') ?></label>
                <div class="wps-tabs__content">
                    <?php
                    if (!empty($data['publishing'])) {
                        $counter = 1;
                        foreach ($data['publishing'] as $term) : ?>
                            <a class="wps-tabs-item" href="<?php echo esc_url(Menus::admin_url('category-analytics', ['type' => 'single', 'term_id' => $term->term_id])) ?>">
                                <div class="wps-content-tabs__item--content">
                                    <h3 class="wps-ellipsis-parent"><span class="wps-ellipsis-text"><?php echo esc_html($term->term_name); ?></span></h3>
                                    <span>
                                        <?php echo esc_html(number_format_i18n($term->posts)); ?> <?php esc_html_e('contents published', 'wp-statistics') ?>
                                    </span>
                                </div>
                            </a>
                            <?php $counter++;
                        endforeach;
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
        'href'  => Menus::admin_url('category-analytics', ['type' => 'report', 'tx' => $taxonomy]),
        'title' => __('See all categories', 'wp-statistics'),
    ]);
    ?>
</div>