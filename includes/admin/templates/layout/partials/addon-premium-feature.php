<?php

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;
$pluginHandler = new PluginHandler();
$isPremium = LicenseHelper::isPremiumLicenseAvailable() ? true : false;

?>

<div class="wps-premium-feature wps-premium-feature--premium-user">
    <?php if ($isPremium) {
        View::load("components/lock-sections/setting-active-premium-addon", ['addon_title' => $addon_title]);
    } else if (!$isPremium && LicenseHelper::isPluginLicenseValid($addon_modal_target)) {
        View::load("components/lock-sections/setting-active-licensed-addon", ['addon_title' => $addon_title]);
    } else if (!$isPremium && !LicenseHelper::isPluginLicenseValid($addon_modal_target)) {
        View::load("components/lock-sections/setting-unlock-premium",
            [
                'addon_title' => $addon_title,
                'addon_description' => $addon_description,
                'addon_features' => $addon_features,
                'addon_info' => $addon_info,
                'addon_documentation_title' => isset($addon_documentation_title) ? $addon_documentation_title : '',
                'addon_documentation_slug' =>isset($addon_documentation_slug) ? $addon_documentation_slug : '',
                'addon_modal_target' => $addon_modal_target
            ]
        );
    }
    ?>
</div>