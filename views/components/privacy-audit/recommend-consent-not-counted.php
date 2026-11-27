<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Menus;
use WP_Statistics\Service\Integrations\ConsentOnlyTracking;

/**
 * Consent item: only visitors who accept the banner are counted.
 * Same message and fix as the "Some Visitors Are Not Counted" notice (ConsentOnlyTracking::getInfo()).
 *
 * @var array    $info     ConsentOnlyTracking::getInfo().
 * @var string[] $settings Settings that record personal data (RequireConsent::getPersonalDataSettings()).
 */

$settingsUrl = Menus::admin_url('settings', ['tab' => 'privacy-settings']) . '#consent_integration';

// Setting the integration to None is only safe when WP Statistics records no personal data once the
// integration is gone, so check the settings without the integration's anonymity exemption.
$afterNone  = \WP_Statistics\Service\Admin\PrivacyAudit\Faqs\RequireConsent::getPersonalDataSettings(false);
$showButton = ConsentOnlyTracking::isNoneSafe();
if (!$showButton) {
    $settings = $afterNone;
}
?>
<div class="wps-mb-16"><?php echo esc_html($info['message']); ?></div>

<?php if (!$showButton) : ?>
    <div class="wps-audit-card__suggestion wps-mb-16">
        <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Keep Asking for Consent for Now', 'wp-statistics') ?></div>
        <div class="wps-audit-card__suggestion-text">
            <p><?php echo esc_html__('These settings make WP Statistics record personal data:', 'wp-statistics'); ?></p>
            <ul>
                <?php foreach ($settings as $setting) : ?>
                    <li><?php echo esc_html($setting); ?></li>
                <?php endforeach; ?>
            </ul>
            <p><?php echo esc_html__('To count every visitor, change these settings first, then set Consent Plugin Integration to None.', 'wp-statistics'); ?></p>
        </div>
    </div>
<?php endif; ?>

<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('How to Fix', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <p>
            <?php if ($showButton) : ?>
                <?php echo ConsentOnlyTracking::getFixButton($info); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped inside ?>
            <?php endif; ?>
            <a href="<?php echo esc_url($settingsUrl); ?>"><?php echo esc_html__('Review consent settings ›', 'wp-statistics'); ?></a>
        </p>
    </div>
</div>
