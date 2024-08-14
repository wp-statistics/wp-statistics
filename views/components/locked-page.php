<div class="wps-premium-box-feature">
    <div class="wps-premium-box-feature__head">
        <h1>
            <?php echo esc_html__('This Feature is Part of the', 'wp-statistics') ?>
            <span><?php echo esc_html__('DataPlus Add-On', 'wp-statistics') ?></span>
        </h1>
        <p>
            <?php echo esc_html__('The settings on this page are part of the DataPlus add-on, which enhances WP Statistics by expanding tracking capabilities and providing detailed visitor insights.', 'wp-statistics') ?>
        </p>
    </div>
    <div class="wps-premium-box__items">
        <div class="wps-premium-box__item">
            <?php echo esc_html__('Track custom post types and taxonomies.', 'wp-statistics') ?>
        </div>
        <div class="wps-premium-box__item">
            <?php echo esc_html__('Use advanced filtering for specific query parameters and UTM tags.', 'wp-statistics') ?>
        </div>
        <div class="wps-premium-box__item">
            <?php echo esc_html__('Monitor outbound link clicks and downloads.', 'wp-statistics') ?>
        </div>
        <div class="wps-premium-box__item">
            <?php echo esc_html__('Compare weekly traffic and view hourly visitor patterns.', 'wp-statistics') ?>
        </div>
        <div class="wps-premium-box__item">
            <?php echo esc_html__('Analyze individual content pieces with detailed widgets.', 'wp-statistics') ?>
        </div>
    </div>
    <div class="wps-premium-box__info">
        <?php echo esc_html__('Unlock deeper insights into your website\'s performance with DataPlus.', 'wp-statistics') ?>
    </div>
    <a target="_blank" class="button button-primary" href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-data-plus?utm_source=wp-statistics&utm_medium=link&utm_campaign=dp-'.$campaign); ?>">
        <?php echo esc_html__('Upgrade Now.', 'wp-statistics') ?>
    </a>
</div>
<div class="wps-premium-box-image">
    <img  src="<?php echo esc_url(WP_STATISTICS_URL . $src); ?>"/>
</div>