<?php
/**
 * A header button for a feature that needs an add-on the site does not have (Export, Realtime, Query
 * Parameter). It looks muted, shows no hover tooltip, and a click opens the Premium popup on that add-on
 * (premium-modal.js, .js-wps-openPremiumModal + data-target; data-surface "locked-button" goes into the
 * popup links' utm_term). Keyboard: a real <button>, Enter or Space.
 * White label (or a hidden Add-ons menu): nothing is printed.
 *
 * Args:
 *  - label (string) Required. Visible button text.
 *  - addon (string) Required. Add-on slug, the popup step to open (e.g. wp-statistics-advanced-reporting).
 *  - class (string) Extra classes for the page's own button look (e.g. "wps-premium-btn wps-premium-btn__export").
 */

use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\UpsellLink;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$label = isset($label) ? (string) $label : '';
$addon = isset($addon) ? (string) $addon : '';
$class = isset($class) ? (string) $class : '';

if ($label === '' || $addon === '' || LicenseHelper::areAddonPromptsHidden()) {
    return;
}
?>
<button type="button" class="<?php echo esc_attr(trim($class . ' disabled wps-tooltip-premium wps-premium-locked-btn js-wps-openPremiumModal')); ?>"<?php echo UpsellLink::popupAttributes($addon, 'locked-button'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper ?> aria-haspopup="dialog">
    <?php echo esc_html($label); ?>
    <span class="screen-reader-text"><?php esc_html_e("(Premium feature. See what's included)", 'wp-statistics'); ?></span>
</button>
