<?php
/**
 * Settings card on an add-on settings tab when the add-on is not part of the site's plan: the lock page
 * text block without a picture, in a white card. One headline, three benefits, "Upgrade to Premium" (opens the Premium popup on the add-on, pricing page as href) and
 * "Learn more" (add-on page), then one "Included in Premium" + guarantee line.
 * Copy comes from {@see AddonCatalog}, the same source as the popup and the add-on lock pages.
 *
 * Args:
 *  - addon_modal_target (string) Required. Add-on slug.
 *  - addon_title        (string) Add-on name (kept for callers; the card shows no add-on label).
 *  - addon_utm_campaign (string) utm_campaign. Default "settings".
 */

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\AddonCatalog;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$slug     = $addon_modal_target ?? '';
$copy     = AddonCatalog::get($slug);
$headline = $copy['headline'] ?? '';
$benefits = $copy['benefits'] ?? [];
$campaign = !empty($addon_utm_campaign) ? $addon_utm_campaign : 'settings';
$titleId  = 'wps-upsell-card-' . sanitize_html_class($slug);

if ($headline === '') {
    return;
}
?>
<section class="wps-upsell-card" aria-labelledby="<?php echo esc_attr($titleId); ?>">
    <h3 class="wps-upsell-card__title" id="<?php echo esc_attr($titleId); ?>"><?php echo esc_html($headline); ?></h3>
    <?php View::load('components/upsell/benefits', ['items' => $benefits, 'class' => 'wps-upsell-card__benefits']); ?>
    <?php View::load('components/upsell/actions', ['campaign' => $campaign, 'addon' => $slug, 'upgrade_modal' => true, 'class' => 'wps-upsell-card__actions']); ?>
    <p class="wps-upsell-card__footer">
        <span><?php esc_html_e('Included in Premium with all 8 add-ons', 'wp-statistics'); ?></span>
        <span class="wps-upsell-card__sep" aria-hidden="true">&middot;</span>
        <span class="wps-upsell-card__guarantee">
            <svg width="14" height="14" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 2.8 4.5 5.6v5.6c0 4.6 3.1 8.3 7.5 10 4.4-1.7 7.5-5.4 7.5-10V5.6L12 2.8Z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/><path d="m8.6 12.2 2.4 2.4 4.5-4.8" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            <?php esc_html_e('14-day money-back guarantee', 'wp-statistics'); ?>
        </span>
    </p>
</section>
