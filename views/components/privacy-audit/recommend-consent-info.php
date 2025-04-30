<?php
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Integrations\IntegrationHelper;

$integrations = IntegrationHelper::getAllIntegrations();
?>

<div class="wps-mb-16"><?php echo esc_html__('WP Statistics does not record any Personally Identifiable Information (PII) by default, so consent is not required. However, to further respect your visitors’ privacy and align with the highest standards, we recommend integrating a consent plugin.', 'wp-statistics') ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Why is This Important?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php echo esc_html__('While WP Statistics operates without requiring consent, enabling a consent plugin builds user trust and demonstrates your commitment to privacy.', 'wp-statistics') ?>
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
            __('<p>Learn %s with your preferred consent management solution. Explore the %s to find the right fit for your site.</p>', 'wp-statistics'),
            '<a target="_blank" href="' . esc_url(WP_STATISTICS_SITE_URL . '/resources/integrating-wp-statistics-with-consent-management-plugins/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy') . '">' . __('how to integrate a consent plugin', 'wp-statistics') . '</a>',
            '<a target="_blank" href="' . esc_url(WP_STATISTICS_SITE_URL . '/resources/compatible-consent-plugins-with-wp-statistics/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy') . '">' . __('list of compatible consent plugins', 'wp-statistics') . '</a>'
        );
        ?>
    </div>
</div>
