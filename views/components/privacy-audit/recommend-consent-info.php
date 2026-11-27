<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Service\Integrations\IntegrationHelper;

$notices = IntegrationHelper::getDetectionNotice();
?>

<div class="wps-mb-16"><?php echo esc_html__('With your current settings, WP Statistics does not record personal data, so it counts every visitor without a consent plugin.', 'wp-statistics') ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Do I Need One?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php echo esc_html__('Only if the laws that apply to your site call for consent anyway. If you connect one with an opt-in banner, only visitors who accept are counted.', 'wp-statistics') ?>
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
            __('<p>Learn %1$s with your preferred consent management solution. Explore the %2$s to find the right fit for your site.</p>', 'wp-statistics'),
            '<a target="_blank" href="' . esc_url(WP_STATISTICS_SITE_URL . '/resources/integrating-wp-statistics-with-consent-management-plugins/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy') . '">' . __('how to integrate a consent plugin', 'wp-statistics') . '</a>',
            '<a target="_blank" href="' . esc_url(WP_STATISTICS_SITE_URL . '/resources/compatible-consent-plugins-with-wp-statistics/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy') . '">' . __('list of compatible consent plugins', 'wp-statistics') . '</a>'
        );
        ?>
    </div>
</div>
