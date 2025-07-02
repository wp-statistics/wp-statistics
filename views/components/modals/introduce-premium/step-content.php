<?php
 use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
$pluginHandler = new PluginHandler();
if ( $step_name !== 'first-step'){
    $isActive       = $pluginHandler->isPluginActive($step_name);
    $isInstalled    = $pluginHandler->isPluginInstalled($step_name);
    $hasLicense     = LicenseHelper::isPluginLicenseValid($step_name);
}

?>

<div class="wps-modal__premium-step js-wps-premiumModalStep wps-modal__premium-step--<?php echo esc_attr($step_name) ?>" >
    <?php echo $description; ?>
    <?php if ( $step_name !== 'first-step') : ?>
        <img class="wps-premium-step__image v-image-lazy" width="509" height="291" data-src="<?php echo WP_STATISTICS_URL . 'assets/images/premium-modal/' . esc_attr($step_name) . '.png'; ?>" alt="<?php echo esc_attr($step_name); ?>">
        <?php if ($hasLicense && !$isActive) : ?>
            <div class="wps-premium-step__notice">
                <div>
                    <?php echo  sprintf(__('Your license includes the %s, but it’s not installed yet. Go to the Add-Ons page to install and activate it, so you can start using all its features.', 'wp-statistics'),
                        esc_attr($step_title)) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!$hasLicense && $isInstalled) : ?>
            <div class="wps-premium-step__notice wps-premium-step__notice--warning">
                <div>
                    <?php echo  sprintf(__('This add-on does <b>not have an active license</b>, which means it cannot receive updates, including important security updates. For uninterrupted access to updates and to keep your site secure, we strongly recommend activating a license. Activate your license <a href="%s">here</a>.', 'wp-statistics'),
                        esc_url(admin_url('admin.php?page=wps_plugins_page'))) ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>