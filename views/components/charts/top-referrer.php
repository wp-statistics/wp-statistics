<?php
use WP_Statistics\Components\View;
?>
<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html($title); ?>
            <?php if (!empty($tooltip)): ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif ?>
        </h2>
    </div>
    <div class="o-wrap wps-p-0">
        <div class="wps-postbox-chart wps-p-0">
            <div class="wps-postbox-chart--data c-chart__wps-skeleton--legend">
                <div class="wps-postbox-chart--items"></div>
                <div class="wps-postbox-chart--info">
                    <div class="wps-postbox-chart--previousPeriod">
                        <?php esc_html_e('Previous period', 'wp-statistics') ?>
                    </div>
                    <?php View::load("components/objects/chart-time-range"); ?>
                </div>
            </div>
            <div class="wps-postbox-chart--container c-chart__wps-skeleton">
                <canvas id="<?php echo esc_html($unique_id); ?>">
            </div>
        </div>
    </div>
    <?php if(isset($footer_title)):?>
        <div class="wps-card__footer">
            <div class="wps-card__footer__more">
                <a class="wps-card__footer__more__link" href="<?php echo esc_url($footer_link) ?>">
                    <?php echo esc_html($footer_title); ?>
                </a>
            </div>
        </div>
    <?php endif;?>
</div>
