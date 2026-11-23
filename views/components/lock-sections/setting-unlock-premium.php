<?php
/**
 * Settings banner on an add-on settings tab when the add-on is not part of the site's plan: one lavender strip
 * with the add-on icon, one value line, a few feature chips and the upgrade buttons on the right.
 * Copy comes from {@see AddonCatalog} so all 8 tabs look and read the same.
 *
 * Args:
 *  - addon_modal_target        (string) Required. Add-on slug.
 *  - addon_title               (string) Add-on name, used when the catalog has no entry.
 *  - addon_description         (string) Value line, used when the catalog has no entry.
 *  - addon_utm_campaign        (string) utm_campaign. Default "settings".
 *  - addon_documentation_title (string) Optional extra link text (REST API docs).
 *  - addon_documentation_slug  (string) Optional extra link URL.
 */

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\AddonCatalog;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$slug     = $addon_modal_target ?? '';
$copy     = AddonCatalog::get($slug);
$name     = $copy['name'] ?? ($addon_title ?? '');
$value    = $copy['value'] ?? wp_strip_all_tags($addon_description ?? '');
$chips    = $copy['chips'] ?? [];
$icon     = AddonCatalog::getIconUrl($slug);
$campaign = !empty($addon_utm_campaign) ? $addon_utm_campaign : 'settings';
$titleId  = 'wps-upsell-banner-' . sanitize_html_class($slug);
?>
<section class="wps-upsell-banner" aria-labelledby="<?php echo esc_attr($titleId); ?>">
    <?php if ($icon) : ?>
        <span class="wps-upsell-banner__icon"><img src="<?php echo esc_url($icon); ?>" alt="" width="28" height="28"></span>
    <?php endif; ?>
    <div class="wps-upsell-banner__text">
        <h3 class="wps-upsell-banner__title" id="<?php echo esc_attr($titleId); ?>"><?php
            /* translators: %s: Add-on name. */
            echo esc_html(sprintf(__('Unlock %s settings', 'wp-statistics'), $name)); ?></h3>
        <?php if ($value) : ?>
            <p class="wps-upsell-banner__value"><?php echo esc_html($value); ?></p>
        <?php endif; ?>
        <?php View::load('components/upsell/chips', [
            'items' => $chips,
            /* translators: %s: Add-on name. */
            'label' => sprintf(__('What %s adds', 'wp-statistics'), $name),
        ]); ?>
    </div>
    <div class="wps-upsell-banner__actions">
        <?php View::load('components/upsell/actions', ['campaign' => $campaign, 'addon' => $slug]); ?>
        <?php if (!empty($addon_documentation_title) && !empty($addon_documentation_slug)) : ?>
            <a class="wps-upsell-banner__docs" href="<?php echo esc_url($addon_documentation_slug); ?>" target="_blank" rel="noopener"><?php echo esc_html($addon_documentation_title); ?></a>
        <?php endif; ?>
    </div>
</section>
