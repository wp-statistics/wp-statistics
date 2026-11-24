<?php
/**
 * Premium popup add-on switcher. Kept as its own partial (markup here, styles in the "Switcher" block of
 * _premium-modal.scss) so the design can be swapped without touching the steps.
 *
 * Design: a tile grid, 4 equal tiles per row (2 per row in a narrow popup), each with a line icon and the
 * full add-on name (never cut off). The shown add-on gets the brand border, tint and text.
 *
 * Contract with premium-modal.js: each item has the class js-wps-premiumStepFeature and data-modal="<slug>";
 * the script sets aria-selected="true", tabindex="0" and the is-active class on the shown add-on, and moves
 * between tiles with the arrow keys, Home and End.
 *
 * Args:
 *  - items     (array)  slug => ['name' => string, 'state' => upgrade|addons-page|activate|active, 'badge' => string]
 *  - id_prefix (string) Element id prefix.
 */

use WP_Statistics\Service\Admin\LicenseManagement\Plugin\AddonCatalog;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (empty($items) || !is_array($items)) {
    return;
}

$first = true;
?>
<div class="wps-premium-switcher" role="tablist" aria-label="<?php esc_attr_e('Premium add-ons', 'wp-statistics'); ?>">
    <?php foreach ($items as $slug => $item) : ?>
        <button type="button" role="tab" class="wps-premium-switcher__item wps-premium-switcher__item--<?php echo esc_attr($item['state']); ?> js-wps-premiumStepFeature" id="<?php echo esc_attr($id_prefix . '-tab-' . sanitize_html_class($slug)); ?>" data-modal="<?php echo esc_attr($slug); ?>" aria-selected="false" tabindex="<?php echo $first ? '0' : '-1'; ?>" aria-controls="<?php echo esc_attr($id_prefix . '-step-' . sanitize_html_class($slug)); ?>">
            <?php echo AddonCatalog::getLineIcon($slug, 18, 'wps-premium-switcher__icon'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed markup ?>
            <span class="wps-premium-switcher__text">
                <span class="wps-premium-switcher__name"><?php echo esc_html($item['name']); ?></span>
                <?php if (!empty($item['badge'])) : ?>
                    <span class="wps-premium-switcher__badge"><?php echo esc_html($item['badge']); ?></span>
                <?php endif; ?>
            </span>
        </button>
        <?php $first = false; ?>
    <?php endforeach; ?>
</div>
