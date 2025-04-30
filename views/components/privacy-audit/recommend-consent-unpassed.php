<?php
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Integrations\IntegrationHelper;

$integrations = IntegrationHelper::getAllIntegrations();
?>

<div class="wps-mb-16"><?php echo esc_html__('WP Statistics does not require consent by default, as it doesn’t record any Personally Identifiable Information (PII). However, one of your enabled settings collects PII, making visitor consent mandatory to stay compliant with GDPR, CCPA, and other privacy regulations.', 'wp-statistics') ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Why is This Important?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php echo esc_html__('With PII collection enabled, integrating a consent plugin is essential to protect user data, meet regulatory requirements, and uphold privacy standards.', 'wp-statistics') ?>
    </div>
</div>

<?php
foreach ($integrations as $integration) {
    if (!$integration->isActive()) continue;

    $notice = $integration->detectionNotice();

    if (empty($notice)) continue;

    ?>
        <div class="wps-audit-card__suggestion wps-mb-16">
            <div class="wps-audit-card__suggestion-head"><?php echo esc_html($notice['title']) ?></div>
            <div class="wps-audit-card__suggestion-text">
                <p><?php echo esc_html($notice['description']) ?></p>
                <a href="<?php echo esc_url(Menus::admin_url('settings', ['tab' => 'privacy-settings']). '#consent_integration') ?>"><?php esc_html_e('Activate integration ›') ?></a>
            </div>
        </div>
    <?php
}
?>

<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Need Help?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php
        printf(
            __('<p>Check out our %s for instructions on configuring a compatible plugin. You can also explore various third-party solutions to ensure compliance and a smooth integration. Browse our list of %s to find the best fit for your site.</p>', 'wp-statistics'),
            '<a target="_blank" href="' . esc_url(WP_STATISTICS_SITE_URL . '/resources/integrating-wp-statistics-with-consent-management-plugins/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy') . '">' . __('consent integration guide', 'wp-statistics') . '</a>',
            '<a target="_blank" href="' . esc_url(WP_STATISTICS_SITE_URL . '/resources/compatible-consent-plugins-with-wp-statistics/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy') . '">' . __('supported consent plugins', 'wp-statistics') . '</a>'
        );
        ?>
    </div>
</div>
