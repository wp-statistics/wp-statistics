<div class="o-wrap wps-about-widget wps-about-widget__premium">
    <div class="wps-about-widget__close">
        <a href="<?php echo esc_url(add_query_arg([
            'action'    => 'wp_statistics_dismiss_widget',
            'nonce'     => wp_create_nonce('wp_statistics_dismiss_widget'),
            'widget_id' => 'about-premium'
        ])) ?>">
            <span class="wp-close" title="Close"></span>
        </a>
    </div>
    <div class="c-about">
        <div class="c-about__row c-about__row--logo  hndle ui-sortable-handle">
            <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/?utm_source=wp-statistics&utm_medium=link&utm_campaign=logo'); ?>" target="_blank">
                <span class="c-about-logo"></span>
            </a>
            <span class="c-about-badge"><span><?php esc_html_e('Premium', 'wp-statistics'); ?></span></span>
        </div>
        <div class="c-about__row c-about__premium">
             <div class="c-about__premium--content">
                 <div>
                     <h3><?php esc_html_e('See More with Premium', 'wp-statistics'); ?></h3>
                     <p><?php esc_html_e('Enhance your experience with exclusive features and tools designed to maximize your website\'s potential.', 'wp-statistics'); ?></p>
                 </div>
                <div class="c-about__upgrade">
                    <a href="https://wp-statistics.com/product/add-ons-bundle?utm_source=wp-statistics&utm_medium=link&utm_campaign=premium" target="_blank">
                        <?php esc_html_e('Upgrade Now', 'wp-statistics'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="c-about__row c-about__footer">
            <div class="c-about__guarantee">
                <?php esc_html_e('14-day money back guarantee', 'wp-statistics'); ?>
            </div>
        </div>
    </div>
</div>