<div class="o-wrap wps-about-widget wps-about-widget__premium">
    <div class="wps-about-widget__close">
        <a href="<?php echo esc_url(add_query_arg([
            'action'    => 'wp_statistics_dismiss_widget',
            'nonce'     => wp_create_nonce('wp_statistics_dismiss_widget'),
            'widget_id' => $widget_id
        ])) ?>">
            <span class="wp-close" title="Close"></span>
        </a>
    </div>
    <div class="c-about">
        <div class="c-about__row c-about__row--title  hndle ui-sortable-handle">
            <?php esc_html_e('Get More with Premium Analytics', 'wp-statistics'); ?>
        </div>
        <div class="c-about__premium__content">
            <div class="c-about__row c-about__premium">
                <p><?php esc_html_e('Upgrade to unlock advanced analytics, including detailed traffic trends and weekly performance summaries.', 'wp-statistics'); ?></p>
                <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/pricing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=premium'); ?>" target=" _blank">
                <?php esc_html_e('Discover More, Go Premium', 'wp-statistics'); ?>
                </a>
                <div class="c-about__guarantee">
                    <?php esc_html_e('Start now with a 14-day money-back guarantee.', 'wp-statistics'); ?>
                </div>
            </div>
            <div class="c-about__row c-about__footer">
                <img class="c-about__footer__img c-about__footer__img--side" src="<?php echo WP_STATISTICS_URL . 'assets/images/premium-widget.svg' ?>"
                     alt=" <?php esc_html_e('Get More with Premium Analytics', 'wp-statistics'); ?>" />
                <img class="c-about__footer__img c-about__footer__img--wide" src="<?php echo WP_STATISTICS_URL . 'assets/images/premium-widget-wide.svg' ?>"
                     alt=" <?php esc_html_e('Get More with Premium Analytics', 'wp-statistics'); ?>" />
            </div>
        </div>

    </div>
</div>