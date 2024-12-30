<div class="wps-mb-16"><?php echo esc_html__('WP Statistics does not require consent by default, as it doesn’t record any Personally Identifiable Information (PII). However, one of your enabled settings collects PII, making visitor consent mandatory to stay compliant with GDPR, CCPA, and other privacy regulations.', 'wp-statistics') ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Why is This Important?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php echo esc_html__('With PII collection enabled, integrating a consent plugin is essential to protect user data, meet regulatory requirements, and uphold privacy standards.', 'wp-statistics') ?>
    </div>
</div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Need Help?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php
        printf(
            __('<p>%s to configure the WP Consent API and connect with a compatible consent plugin.</p>Browse the %s to ensure compliance and smooth integration.', 'wp-statistics'),
            '<a target="_blank" href="' . esc_url(WP_STATISTICS_SITE_URL . '/resources/wp-consent-level-integration/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy') . '">' . __('Follow this guide', 'wp-statistics') . '</a>',
            '<a target="_blank" href="' . esc_url(WP_STATISTICS_SITE_URL . '/resources/compatible-consent-plugins-with-wp-statistics/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy') . '">' . __('list of compatible consent plugins', 'wp-statistics') . '</a>'
        );
        ?>
    </div>
</div>
