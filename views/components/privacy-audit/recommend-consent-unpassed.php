<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Service\Integrations\IntegrationHelper;

/**
 * Consent item: no consent plugin, and some settings record personal data.
 *
 * @var string[] $settings Settings that record personal data (RequireConsent::getPersonalDataSettings()).
 */

$notices  = IntegrationHelper::getDetectionNotice();
$settings = isset($settings) ? (array)$settings : [];
?>

<div class="wps-mb-16"><?php echo esc_html__('These settings make WP Statistics record personal data, and no consent plugin is connected:', 'wp-statistics') ?></div>
<ul class="wps-mb-16">
    <?php foreach ($settings as $setting) : ?>
        <li><?php echo esc_html($setting); ?></li>
    <?php endforeach; ?>
</ul>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('What to Do', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php echo esc_html__('Change these settings so WP Statistics stops recording personal data, or connect a consent plugin if the laws that apply to your site require consent.', 'wp-statistics') ?>
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
            __('<p>Read the %1$s, or see the list of %2$s.</p>', 'wp-statistics'),
            '<a target="_blank" href="' . esc_url(WP_STATISTICS_SITE_URL . '/resources/integrating-wp-statistics-with-consent-management-plugins/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy') . '">' . __('consent integration guide', 'wp-statistics') . '</a>',
            '<a target="_blank" href="' . esc_url(WP_STATISTICS_SITE_URL . '/resources/compatible-consent-plugins-with-wp-statistics/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy') . '">' . __('supported consent plugins', 'wp-statistics') . '</a>'
        );
        ?>
    </div>
</div>
