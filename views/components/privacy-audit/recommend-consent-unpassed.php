<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Service\Integrations\IntegrationHelper;

$notices = IntegrationHelper::getDetectionNotice();
?>

<div class="wps-mb-16"><?php echo esc_html__('WP Statistics can operate without recording Personally Identifiable Information (PII). One of your enabled settings collects PII, so review whether the laws that apply to your site require visitor consent before tracking.', 'wp-statistics') ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Why is This Important?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php echo esc_html__('When consent is required, a compatible consent plugin can help WP Statistics respect each visitor’s choice before collecting PII.', 'wp-statistics') ?>
    </div>
</div>

<?php foreach ($notices as $notice) : ?>
    <div class="wps-audit-card__suggestion wps-mb-16">
        <div class="wps-audit-card__suggestion-head"><?php esc_html_e('Consent Integration Available', 'wp-statistics'); ?></div>
        <div class="wps-audit-card__suggestion-text">
            <?php echo wp_kses_post($notice['content']) ?>
        </div>
    </div>
<?php endforeach; ?>

<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Need Help?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php
        printf(
            /* translators: %1$s: string value, %2$s: string value */
            __('<p>Check out our %1$s for instructions on configuring a compatible plugin. You can also explore various third-party solutions to ensure compliance and a smooth integration. Browse our list of %2$s to find the best fit for your site.</p>', 'wp-statistics'),
            '<a target="_blank" href="' . esc_url(WP_STATISTICS_SITE_URL . '/resources/integrating-wp-statistics-with-consent-management-plugins/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy') . '">' . __('consent integration guide', 'wp-statistics') . '</a>',
            '<a target="_blank" href="' . esc_url(WP_STATISTICS_SITE_URL . '/resources/compatible-consent-plugins-with-wp-statistics/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy') . '">' . __('supported consent plugins', 'wp-statistics') . '</a>'
        );
        ?>
    </div>
</div>
