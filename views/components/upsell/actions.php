<?php
/**
 * The two upsell buttons of the big surfaces (lock pages, settings cards, the Premium popup). One behaviour
 * everywhere, the words say where you go:
 *  - "Upgrade to Premium" = pricing page, new tab.
 *  - "Learn more"         = the add-on's page on wp-statistics.com (the add-ons list when there is no
 *                           add-on), new tab.
 * Neither opens the Premium popup. Links: {@see UpsellLink}.
 *
 * Args:
 *  - surface (string) utm_medium, e.g. "lock-page", "settings-card", "popup". Default "lock-page".
 *  - addon   (string) Add-on slug. Empty: Premium as a whole (utm_campaign "premium").
 *  - class   (string) Extra classes on the wrapper.
 */

use WP_Statistics\Service\Admin\LicenseManagement\UpsellLink;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$surface = !empty($surface) ? $surface : 'lock-page';
$addon   = !empty($addon) && $addon !== 'first-step' ? $addon : '';
// Popup links get utm_term (the surface that opened it) from premium-modal.js.
$popup   = $surface === 'popup' ? ' js-wps-premiumModalLink' : '';
?>
<div class="wps-upsell-actions<?php echo !empty($class) ? ' ' . esc_attr($class) : ''; ?>">
    <a class="wps-upsell-btn wps-upsell-btn--primary<?php echo esc_attr($popup); ?>" href="<?php echo esc_url(UpsellLink::pricing($surface, $addon)); ?>" target="_blank" rel="noopener"><?php esc_html_e('Upgrade to Premium', 'wp-statistics'); ?></a>
    <a class="wps-upsell-btn wps-upsell-btn--secondary<?php echo esc_attr($popup); ?>" href="<?php echo esc_url(UpsellLink::addon($addon, $surface)); ?>" target="_blank" rel="noopener"><?php esc_html_e('Learn more', 'wp-statistics'); ?></a>
</div>
