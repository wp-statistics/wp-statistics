<?php
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

$pluginHandler = new PluginHandler();
$isPremium     = LicenseHelper::isPremiumLicenseAvailable() ? true : false;
$hasLicense    = LicenseHelper::isPluginLicenseValid($addon_modal_target) ? true : false;
$isActive      = $pluginHandler->isPluginActive($addon_modal_target);
?>

<div class="wps-premium-feature wps-premium-feature--premium-user">
    <?php
    if (!$isActive && $hasLicense) :
        View::load("components/lock-sections/setting-active-licensed-addon", ['addon_title' => $addon_title]);
    elseif (!$isPremium && !$hasLicense) :
        View::load("components/lock-sections/setting-unlock-premium", [
            'addon_title'               => $addon_title,
            'addon_description'         => $addon_description,
            'addon_features'            => $addon_features,
            'addon_info'                => $addon_info ?? '',
            'addon_documentation_title' => $addon_documentation_title ?? '',
            'addon_documentation_slug'  => $addon_documentation_slug ?? '',
            'addon_modal_target'        => $addon_modal_target,
            'addon_slug'                => $addon_slug,
            'addon_utm_campaign'        => $addon_campaign ?? ''
        ]);
    endif;
    ?>
</div>