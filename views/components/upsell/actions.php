<?php
/**
 * Upsell buttons used by every Premium surface: "Upgrade to Premium" and "Learn more".
 *
 * Default: "Upgrade to Premium" goes to the pricing page (new tab) and "Learn more" opens the Premium popup
 * on the add-on (the add-on page stays the href for no-JS).
 * With upgrade_modal: "Upgrade to Premium" opens the Premium popup on the add-on (pricing page stays the
 * href) and "Learn more" goes to the add-on page in a new tab.
 *
 * Args:
 *  - campaign      (string) utm_campaign of both links. Default "premium".
 *  - addon         (string) Add-on slug. Empty: the popup opens on the first step.
 *  - modal         (bool)   "Learn more" opens the Premium popup. Default true. Off inside the popup itself.
 *  - upgrade_modal (bool)   "Upgrade to Premium" opens the popup instead (settings card). Default false.
 *  - learn_url     (string) Overrides the "Learn more" URL.
 *  - class         (string) Extra classes on the wrapper.
 */

use WP_Statistics\Service\Admin\LicenseManagement\Plugin\AddonCatalog;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$campaign = !empty($campaign) ? $campaign : 'premium';
$addon    = !empty($addon) ? $addon : '';
$modal    = isset($modal) ? (bool)$modal : true;
$upgModal = !empty($upgrade_modal);
$target   = $addon ? $addon : 'first-step';
if ($upgModal) {
    $modal = false;
}
$learnUrl = !empty($learn_url) ? $learn_url : ($addon ? AddonCatalog::getProductUrl($addon, $campaign) : WP_STATISTICS_SITE_URL . '/add-ons/?utm_source=wp-statistics&utm_medium=link&utm_campaign=' . rawurlencode($campaign));
?>
<div class="wps-upsell-actions<?php echo !empty($class) ? ' ' . esc_attr($class) : ''; ?>">
    <?php if ($upgModal) : ?>
        <a class="wps-upsell-btn wps-upsell-btn--primary js-wps-openPremiumModal" href="<?php echo esc_url(AddonCatalog::getPricingUrl($campaign)); ?>" data-target="<?php echo esc_attr($target); ?>"><?php esc_html_e('Upgrade to Premium', 'wp-statistics'); ?></a>
    <?php else : ?>
        <a class="wps-upsell-btn wps-upsell-btn--primary" href="<?php echo esc_url(AddonCatalog::getPricingUrl($campaign)); ?>" target="_blank" rel="noopener"><?php esc_html_e('Upgrade to Premium', 'wp-statistics'); ?></a>
    <?php endif; ?>
    <?php if ($modal) : ?>
        <a class="wps-upsell-btn wps-upsell-btn--secondary js-wps-openPremiumModal" href="<?php echo esc_url($learnUrl); ?>" data-target="<?php echo esc_attr($target); ?>"><?php esc_html_e('Learn more', 'wp-statistics'); ?></a>
    <?php else : ?>
        <a class="wps-upsell-btn wps-upsell-btn--secondary" href="<?php echo esc_url($learnUrl); ?>" target="_blank" rel="noopener"><?php esc_html_e('Learn more', 'wp-statistics'); ?></a>
    <?php endif; ?>
</div>
