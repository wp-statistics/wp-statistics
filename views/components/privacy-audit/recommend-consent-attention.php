<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Menus;

/**
 * Consent item: the selected consent plugin is not active or not set up.
 * Same message as the "Consent Integration Needs Attention" notice.
 *
 * @var string $warning IntegrationHelper::getConfigurationWarning().
 */
?>
<div class="wps-mb-16"><?php echo esc_html($warning); ?></div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('How to Fix', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <p><?php echo esc_html__('Activate and set up the consent plugin, or choose another option in Consent Plugin Integration.', 'wp-statistics'); ?></p>
        <p><a href="<?php echo esc_url(Menus::admin_url('settings', ['tab' => 'privacy-settings']) . '#consent_integration'); ?>"><?php echo esc_html__('Review consent settings ›', 'wp-statistics'); ?></a></p>
    </div>
</div>
