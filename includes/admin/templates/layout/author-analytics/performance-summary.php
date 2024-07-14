<?php

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Request;

$postTypeLabelSingular  = Helper::getPostTypeName(Request::get('pt', 'post'), true);
$postTypeLabelPlural    = Helper::getPostTypeName(Request::get('pt', 'post'));
?>

<div class="wps-card wps-card__sums <?php echo isset($active) ? 'wps-card__sums--authors' : ''; ?>  <?php echo isset($total_type) ? 'wps-card__sums--two-row' : ''; ?>">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title); ?>
            <?php if (isset($tooltip)) : ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>

    <div class="wps-card__summary">

        <div class="wps-card__summary--title">
            <span><?php echo esc_html($total) ?></span>
            <span><?php echo isset($total_title) ? esc_html($total_title) : esc_html_e('Total', 'wp-statistics'); ?></span>
        </div>

        <?php if (isset($active)) : ?>
            <div class="wps-card__summary--active">
                <span><?php echo esc_html($active) ?></span>
                <span><?php esc_html_e('Active', 'wp-statistics') ?></span>
            </div>
        <?php endif ?>

        <?php if (isset($published)) : ?>
            <div class="wps-card__summary--publish">
                <span><?php echo esc_html($published) ?></span>
                <span title="<?php echo sprintf(esc_attr__('Published %s', 'wp-statistics'), $postTypeLabelPlural) ?>"><?php echo sprintf(esc_html__('Published %s', 'wp-statistics'), $postTypeLabelPlural) ?></span>
            </div>
        <?php endif ?>
            <div class="wps-card__summary--avg">
                <?php if (isset($avg)) : ?>
                    <span><?php echo esc_html($avg) ?></span>
                <?php endif ?>

                <?php if (isset($avg_title)) : ?>
                    <span title="<?php echo esc_attr($avg_title) ?>"><?php echo esc_html($avg_title) ?></span>
                <?php endif ?>
            </div>

        <?php if (isset($total_type)) : ?>
            <div class="wps-card__summary--total">
                <span><?php echo esc_html($total_type) ?></span>
                <span><?php esc_html_e('Total', 'wp-statistics') ?></span>
            </div>
        <?php endif ?>

        <?php if (isset($total_avg)) : ?>
            <div class="wps-card__summary--total-avg">
                <span><?php echo esc_html($total_avg) ?></span>
                <span><?php echo sprintf(esc_html__('Total Avg. per %s', 'wp-statistics'), $postTypeLabelSingular) ?></span>
            </div>
        <?php endif ?>

     </div>
</div>