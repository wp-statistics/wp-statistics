<?php
$aboutWidgetContent = apply_filters('wp_statistics_about_widget_content', false);
if ($aboutWidgetContent) {
    echo '<div class="o-wrap o-wrap--no-data">' . apply_filters('the_content', $aboutWidgetContent) . '</div>';

    return;
} ?>

<div class="o-wrap wps-about-widget js-wps-widget-customization-empty">
    <div class="c-about">
        <div class="c-about__row c-about__row--logo  hndle ui-sortable-handle">
            <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/?utm_source=wp-statistics&utm_medium=link&utm_campaign=logo'); ?>" target="_blank">
                <span class="c-about-logo"></span>
            </a>
            <span class="c-about-badge"><span><?php esc_html_e('Version', 'wp-statistics'); ?></span> <span><?php echo WP_STATISTICS_VERSION // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></span> </span>
        </div>
        <div class="c-about__row c-about__links">
            <div class="c-about__links--title"><?php esc_html_e('Quick Actions', 'wp-statistics'); ?></div>
            <div class="c-about__links--content">
                <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/documentation/?utm_source=wp-statistics&utm_medium=link&utm_campaign=help'); ?>" target="_blank">
                    <span class="c-about__links--icon c-about__links--icon__help"></span>
                    <?php esc_html_e('Help Center', 'wp-statistics'); ?>
                </a>

                <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/add-ons/?utm_source=wp-statistics&utm_medium=link&utm_campaign=add-ons'); ?>" target="_blank">
                    <span class="c-about__links--icon c-about__links--icon__add-ons"></span>
                    <?php esc_html_e('Add-Ons', 'wp-statistics'); ?>
                </a>

                <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/changelog/?utm_source=wp-statistics&utm_medium=link&utm_campaign=changelog'); ?>" target="_blank">
                    <span class="c-about__links--icon c-about__links--icon__release"></span>
                    <?php esc_html_e('Release Notes', 'wp-statistics'); ?>
                </a>

                <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/blog/?utm_source=wp-statistics&utm_medium=link&utm_campaign=blog'); ?>" target="_blank">
                    <span class="c-about__links--icon c-about__links--icon__blog"></span>
                    <?php esc_html_e('Blog Updates', 'wp-statistics'); ?>
                </a>
                <div class="c-about__rate">
                    <a href="https://wordpress.org/support/plugin/wp-statistics/reviews/?filter=5#new-post" target="_blank">
                        <?php esc_html_e('Enjoying WP Statistics? Give Us 5 Stars', 'wp-statistics'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="c-about__row c-about__footer">
            <?php if (!is_plugin_active('wp-statistics-customization/wp-statistics-customization.php')) { ?>
                <div class="c-about__customization">
                    <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp-statistics-customization?utm_source=wp-statistics&utm_medium=link&utm_campaign=customization'); ?>" target="_blank">
                        <?php esc_html_e('How to Customize This Widget', 'wp-statistics'); ?>
                    </a>
                </div>
            <?php } ?>
            <div class="c-about__veronalabs">
                <a href="https://veronalabs.com/?utm_source=wp_statistics&utm_medium=display&utm_campaign=wordpress" target="_blank" title="<?php esc_html_e('Power by VeronaLabs', 'wp-statistics'); ?>">
                    <span class="c-about__veronalabs--icon"></span>
                </a>
            </div>
        </div>
    </div>
</div>