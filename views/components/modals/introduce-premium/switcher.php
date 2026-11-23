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
 *  - items     (array)  slug => ['name' => string, 'state' => upgrade|addons-page|active, 'badge' => string]
 *  - id_prefix (string) Element id prefix.
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (empty($items) || !is_array($items)) {
    return;
}

// Monochrome line icons (24x24, stroke = currentColor), one per add-on.
$icons = [
    'wp-statistics-data-plus'           => '<path d="M5 4l5.5 15 2.2-6.3L19 10.5z"/><path d="M13 13l5 5"/>',
    'wp-statistics-marketing'           => '<circle cx="12" cy="12" r="8.5"/><circle cx="12" cy="12" r="4.5"/><circle cx="12" cy="12" r=".9" fill="currentColor"/>',
    'wp-statistics-mini-chart'          => '<path d="M4 19h16"/><path d="M5 15l4.5-4.5 3.5 3 6-6.5"/><path d="M15 7h4v4"/>',
    'wp-statistics-advanced-reporting'  => '<rect x="3.5" y="5.5" width="17" height="13" rx="2"/><path d="M4 7.5l8 6 8-6"/>',
    'wp-statistics-realtime-stats'      => '<path d="M3 12h4l2.5-6 5 12 2.5-6h4"/>',
    'wp-statistics-widgets'             => '<rect x="4" y="4" width="7" height="7" rx="1.5"/><rect x="13" y="4" width="7" height="7" rx="1.5"/><rect x="4" y="13" width="7" height="7" rx="1.5"/><rect x="13" y="13" width="7" height="7" rx="1.5"/>',
    'wp-statistics-customization'       => '<path d="M4 7h9M17 7h3M4 17h3M11 17h9"/><circle cx="15" cy="7" r="2"/><circle cx="9" cy="17" r="2"/>',
    'wp-statistics-rest-api'            => '<path d="M8.5 7.5 4 12l4.5 4.5M15.5 7.5 20 12l-4.5 4.5M13.5 5.5l-3 13"/>',
];
$first = true;
?>
<div class="wps-premium-switcher" role="tablist" aria-label="<?php esc_attr_e('Premium add-ons', 'wp-statistics'); ?>">
    <?php foreach ($items as $slug => $item) : ?>
        <button type="button" role="tab" class="wps-premium-switcher__item wps-premium-switcher__item--<?php echo esc_attr($item['state']); ?> js-wps-premiumStepFeature" id="<?php echo esc_attr($id_prefix . '-tab-' . sanitize_html_class($slug)); ?>" data-modal="<?php echo esc_attr($slug); ?>" aria-selected="false" tabindex="<?php echo $first ? '0' : '-1'; ?>" aria-controls="<?php echo esc_attr($id_prefix . '-step-' . sanitize_html_class($slug)); ?>">
            <?php if (isset($icons[$slug])) : ?>
                <svg class="wps-premium-switcher__icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><?php echo $icons[$slug]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed markup above ?></svg>
            <?php endif; ?>
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
