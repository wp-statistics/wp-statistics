<?php

use WP_Statistics\Components\View;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginDecorator;

?>
<div class="wps-wrap__main">
    <div class="wp-header-end"></div>

    <div class="wps-postbox-addon__step">
        <div class="wps-addon__step__info">
            <span class="wps-addon__step__image wps-addon__step__image--checked"></span>
            <h2 class="wps-addon__step__title"><?php esc_html_e('You\'re All Set! Your License is Successfully Activated!', 'wp-statistics'); ?></h2>
            <p class="wps-addon__step__desc"><?php esc_html_e('Choose the add-ons you want to install. You can modify your selection later.', 'wp-statistics'); ?></p>
        </div>
        <div class="wps-addon__step__download">
            <div class="wps-addon__download__title">
                <h3>
                    <?php esc_html_e('Select Your Add-Ons', 'wp-statistics'); ?>
                </h3>
                <a class="wps-addon__download_select-all js-wps-addon-select-all <?php echo empty($data['display_select_all']) ? 'wps-hide' : ''; ?>"><?php esc_html_e('Select All', 'wp-statistics'); ?></a>
            </div>
            <div class="wps-addon__download__items">
                <?php
                if (!empty($data['licensed_addons'])) {
                    /** @var PluginDecorator $addOn */
                    foreach ($data['licensed_addons'] as $addOn) {
                        View::load('components/addon-download-card', ['addOn' => $addOn, 'included' => true]);
                    }
                }

                if (!empty($data['not_included_addons'])) {
                    /** @var PluginDecorator $addOn */
                    foreach ($data['not_included_addons'] as $addOn) {
                        View::load('components/addon-download-card', ['addOn' => $addOn, 'included' => false]);
                    }
                }
                ?>
            </div>
        </div>
        <div class="wps-addon__step__action">
            <a href="<?php echo esc_url(Menus::admin_url('plugins', ['tab' => 'add-license'])); ?>" class="wps-addon__step__back"><?php esc_html_e('Back', 'wp-statistics'); ?></a>
            <a class="wps-postbox-addon-button js-addon-download-button disabled">
                <?php esc_html_e('Download & Install Selected Add-Ons', 'wp-statistics'); ?>
            </a>
        </div>
    </div>
</div>