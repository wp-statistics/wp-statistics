<?php
/**
 * Top of an add-on settings tab when the add-on is not running.
 *
 *  - White label (or a hidden Add-ons menu): nothing.
 *  - Installed but turned off: a short notice with an "Activate" link, no upsell.
 *  - In the license but not installed: "You have access to this add-on" box.
 *  - Not in the plan: the upsell card (components/lock-sections/setting-unlock-premium).
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\User;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\AddonCatalog;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

$pluginHandler = new PluginHandler();
$isPremium     = LicenseHelper::isPremiumLicenseAvailable() ? true : false;
$hasLicense    = LicenseHelper::isPluginLicenseValid($addon_modal_target) ? true : false;
$isActive      = $pluginHandler->isPluginActive($addon_modal_target);
$situation     = LicenseHelper::getAddonSituation($addon_modal_target, $pluginHandler);
$copy          = AddonCatalog::get($addon_modal_target);
$addonName     = $copy['name'] ?? ($addon_title ?? '');

if ($isActive || $situation === LicenseHelper::SITUATION_HIDDEN) {
    return;
}

if ($situation === LicenseHelper::SITUATION_INACTIVE) : ?>
    <div class="wps-notice-settings wps-notice-settings--warning">
        <div>
            <div class="wps-notice-settings__desc">
                <?php
                /* translators: %s: Add-on name. */
                echo esc_html(sprintf(__('The %s add-on isn\'t activated.', 'wp-statistics'), $addonName));
                if (User::Access('manage') && current_user_can('activate_plugins')) : ?>
                    <a href="<?php echo esc_url(LicenseHelper::getAddonPluginsPageUrl($addon_modal_target)); ?>"><?php
                        /* translators: %s: Add-on name. */
                        echo esc_html(sprintf(__('Activate %s', 'wp-statistics'), $addonName)); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php elseif ($hasLicense) : ?>
    <div class="wps-premium-feature wps-premium-feature--premium-user">
        <?php View::load("components/lock-sections/setting-active-licensed-addon", ['addon_title' => $addon_title]); ?>
    </div>
<?php elseif (!$isPremium) :
    View::load("components/lock-sections/setting-unlock-premium", [
        'addon_title'        => $addon_title ?? '',
        'addon_modal_target' => $addon_modal_target,
        'addon_utm_campaign' => $addon_campaign ?? '',
    ]);
endif;
