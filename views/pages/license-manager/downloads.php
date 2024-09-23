<?php

use WP_Statistics\Components\View;

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
                    <?php esc_html_e('Select Your Add-ons', 'wp-statistics'); ?>
                </h3>
                <a class="wps-addon__download_select-all js-wps-addon-select-all"><?php esc_html_e('Select All', 'wp-statistics'); ?></a>
            </div>
            <div class="wps-addon__download__items">
                <?php
                /** @var ProductDecorator $addOn */
                foreach ($data['licensed_addons'] as $addOn) {
                    View::load('components/addon-download-card', ['addOn' => $addOn]);
                }
                /** @var ProductDecorator $addOn */
                foreach ($data['not_included_addons'] as $addOn) {
                    View::load('components/addon-download-card', ['addOn' => $addOn]);
                }
                ?>
            </div>
        </div>
        <div class="wps-addon__step__action">
            <a href="" class="wps-addon__step__back"><?php esc_html_e('Back', 'wp-statistics'); ?></a>
            <a href="" class="wps-postbox-addon-button">
                <?php esc_html_e('Download & Install Selected Add-ons', 'wp-statistics'); ?>
            </a>
        </div>
    </div>
</div>
