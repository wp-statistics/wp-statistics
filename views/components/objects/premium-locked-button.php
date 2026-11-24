<?php
/**
 * A header button for a feature that needs an add-on the site does not have (Export, Realtime, Query
 * Parameter). It looks muted, shows no hover tooltip, and a click opens the Premium popup on that add-on
 * (premium-modal.js, .js-wps-openPremiumModal + data-target). Keyboard: a real <button>, Enter or Space.
 *
 * Args:
 *  - label (string) Required. Visible button text.
 *  - addon (string) Required. Add-on slug, the popup step to open (e.g. wp-statistics-advanced-reporting).
 *  - class (string) Extra classes for the page's own button look (e.g. "wps-premium-btn wps-premium-btn__export").
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$label = isset($label) ? (string) $label : '';
$addon = isset($addon) ? (string) $addon : '';
$class = isset($class) ? (string) $class : '';

if ($label === '' || $addon === '') {
    return;
}
?>
<button type="button" class="<?php echo esc_attr(trim($class . ' disabled wps-tooltip-premium wps-premium-locked-btn js-wps-openPremiumModal')); ?>" data-target="<?php echo esc_attr($addon); ?>" aria-haspopup="dialog">
    <?php echo esc_html($label); ?>
    <span class="screen-reader-text"><?php esc_html_e('(Premium feature, opens details)', 'wp-statistics'); ?></span>
</button>
