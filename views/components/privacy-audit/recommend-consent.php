<P><?php echo esc_html__('To meet GDPR, CCPA, and other privacy regulations, WP Statistics requires user consent for specific features.', 'wp-statistics') ?></P>
<div class="wps-mb-16"><?php echo esc_html__('Follow these two simple steps to ensure compliance and protect user data.', 'wp-statistics') ?></div>
<p><b><?php echo esc_html__('Step 1: Install and Configure WP Consent API', 'wp-statistics') ?></b></p>
<p><?php echo __('Start by installing and configuring the <a target="_blank" href="' . esc_url('https://wordpress.org/plugins/wp-consent-api/') . '">WP Consent API</a> . This essential tool is the backbone of your consent.', 'wp-statistics') ?></p>
<div class="wps-mb-16"><?php echo esc_html__('management system, providing a seamless way to integrate consent management providers.', 'wp-statistics') ?></div>
<p><b><?php echo esc_html__('Step 2: Choose a Consent Management Provider', 'wp-statistics') ?></b></p>
<div class="wps-mb-16"><?php echo esc_html__('Once WP Consent API is set up, integrate it with one of these trusted plugins to manage user consent effectively:', 'wp-statistics') ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Why is This Important?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php echo esc_html__('By setting up WP Consent API and integrating a Consent Management Provider, you ensure your site meets privacy standards, safeguards user trust, and stays fully compliant with global regulations.', 'wp-statistics') ?>
    </div>
</div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Need Help?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php
        printf(
            __('Check out the %s for easy, step-by-step instructions to get started.', 'wp-statistics'),
            '<a target="_blank" href="' . esc_url(WP_STATISTICS_SITE_URL . '/resources/wp-consent-level-integration/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy') . '">' . __('WP Consent Level Integration Guide', 'wp-statistics') . '</a>'
        );
        ?>
    </div>
</div>
