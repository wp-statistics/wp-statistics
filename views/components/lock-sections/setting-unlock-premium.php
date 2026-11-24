<?php
/**
 * Settings card on an add-on settings tab when the add-on is not part of the site's plan: the lock page
 * text block without a picture, in a white card. One headline, three benefits, "Upgrade to Premium"
 * (pricing page, new tab) and "Learn more" (add-on page, new tab), then one line: guarantee first, then
 * "Included in Premium with all 8 add-ons". No popup: the card already shows what the popup would.
 * Copy comes from {@see AddonCatalog}, the same source as the popup and the add-on lock pages.
 *
 * Args:
 *  - addon_modal_target (string) Required. Add-on slug.
 *  - addon_title        (string) Add-on name (kept for callers; the card shows no add-on label).
 *  - addon_utm_campaign (string) Kept for callers; utm_campaign now comes from the add-on slug.
 */

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\AddonCatalog;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

$slug     = $addon_modal_target ?? '';
$copy     = AddonCatalog::get($slug);
$headline = $copy['headline'] ?? '';
$benefits = $copy['benefits'] ?? [];
$titleId  = 'wps-upsell-card-' . sanitize_html_class($slug);

if ($headline === '') {
    return;
}
?>
<section class="wps-upsell-card" aria-labelledby="<?php echo esc_attr($titleId); ?>">
    <h3 class="wps-upsell-card__title" id="<?php echo esc_attr($titleId); ?>"><?php echo esc_html($headline); ?></h3>
    <?php View::load('components/upsell/benefits', ['items' => $benefits, 'class' => 'wps-upsell-card__benefits']); ?>
    <?php View::load('components/upsell/actions', ['surface' => 'settings-card', 'addon' => $slug, 'class' => 'wps-upsell-card__actions']); ?>
    <?php View::load('components/upsell/guarantee', ['extra' => __('Included in Premium with all 8 add-ons', 'wp-statistics'), 'class' => 'wps-upsell-card__footer']); ?>
</section>
