<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>
<div class="wps-mb-16"><?php echo esc_html__('WP Statistics counts unique visitors without cookies or storing identifiers on visitors’ devices. Cookieless tracking reduces data stored in the browser, but it does not by itself determine whether consent is required.', 'wp-statistics') ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('When a Cookie Banner May Be Required', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php echo esc_html__('WP Statistics does not require cookies to provide analytics. Your site may still need a cookie banner for other technologies, and analytics consent requirements depend on your settings and applicable laws.', 'wp-statistics') ?>
    </div>
</div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('More Information', 'wp-statistics'); ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php
        printf(
            /* translators: %s: string value */
            wp_kses_post(__('For a comprehensive understanding of how WP Statistics counts unique visitors without cookies, and the advantages of this approach, please refer to our detailed documentation: <a href="%s" target="_blank">Counting Unique Visitors Without Cookies</a>.', 'wp-statistics')),
            esc_url(WP_STATISTICS_SITE_URL . '/resources/counting-unique-visitors-without-cookies/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy')
        );
        ?>
    </div>
</div>
