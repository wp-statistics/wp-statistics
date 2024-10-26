<?php
 use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
$pluginHandler = new PluginHandler();
?>

<div class="wps-modal__premium-step js-wps-premiumModalStep wps-modal__premium-step--<?php echo esc_attr($step_name) ?>" >
    <?php echo $description; ?>
    <?php if ( $step_name !== 'first-step') : ?>
        <img class="wps-premium-step__image" src="<?php echo WP_STATISTICS_URL . 'assets/images/premium-modal/' . esc_attr($step_name) . '.png'; ?>" alt="<?php echo esc_attr($step_name); ?>">
        <?php if (LicenseHelper::isPremiumLicenseAvailable() &&!$pluginHandler->isPluginInstalled($step_name)) : ?>
            <div class="wps-premium-step__notice">
                <div>
                    <?php echo  sprintf(__('Your WP Statistics Premium includes the %s add-on, but it\'s not installed yet. Visit the <b>Add-Ons page</b> to <b>install</b> and <b>activate it</b>, unlocking its full features.', 'wp-statistics'),
                        esc_attr($step_title)) ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>