<?php
/**
 * Premium popup add-on switcher. Kept as its own partial (markup here, styles in the "Switcher" block of
 * _premium-modal.scss) so the design can be swapped without touching the steps.
 *
 * Contract with premium-modal.js: each item has the class js-wps-premiumStepFeature and data-modal="<slug>";
 * the script sets aria-pressed="true" and the is-active class on the shown add-on.
 *
 * Temporary design: plain text tabs that wrap, no truncation.
 *
 * Args:
 *  - items     (array)  slug => ['name' => string, 'state' => upgrade|addons-page|active, 'badge' => string]
 *  - id_prefix (string) Element id prefix.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (empty($items) || !is_array($items)) {
    return;
}
?>
<div class="wps-premium-switcher" role="group" aria-label="<?php esc_attr_e('Premium add-ons', 'wp-statistics'); ?>">
    <?php foreach ($items as $slug => $item) : ?>
        <button type="button" class="wps-premium-switcher__item wps-premium-switcher__item--<?php echo esc_attr($item['state']); ?> js-wps-premiumStepFeature" data-modal="<?php echo esc_attr($slug); ?>" aria-pressed="false" aria-controls="<?php echo esc_attr($id_prefix . '-step-' . sanitize_html_class($slug)); ?>">
            <span class="wps-premium-switcher__name"><?php echo esc_html($item['name']); ?></span>
            <?php if (!empty($item['badge'])) : ?>
                <span class="wps-premium-switcher__badge"><?php echo esc_html($item['badge']); ?></span>
            <?php endif; ?>
        </button>
    <?php endforeach; ?>
</div>
