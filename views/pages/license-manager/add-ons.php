<?php

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\ProductDecorator;

$activeAddOns   = [];
$inactiveAddOns = [];
if (!empty($addons_list)) {
    foreach ($addons_list as $addOn) {
        if ($addOn->isActivated()) {
            $activeAddOns[] = $addOn;
        } else {
            $inactiveAddOns[] = $addOn;
        }
    }
}

?>
<div class="wps-wrap__main">
    <div class="wp-header-end"></div>
    <div class="postbox-container wps-postbox-addon-container">
        <div class="wps-postbox-addon">
            <div>
                <h2 class="wps-postbox-addon__title"><?php esc_html_e('Active Add-Ons', 'wp-statistics'); ?></h2>
                <div class="wps-postbox-addon__items">
                    <?php
                    /** @var ProductDecorator $addOn */
                    foreach ($activeAddOns as $addOn) {
                        $args = [
                            'addOn'              => $addOn,
                            'has_license_btn'    => true,
                            'setting_link'       => '#',
                            'detail_link'        => '#',
                            'change_log_link'    => '#',
                            'documentation_link' => '#',
                            'alert_class'        => 'danger',
                            'alert_text'         => esc_html__('Almost There! Your license is valid. To proceed, please whitelist this domain in customer portal.', 'wp-statistics'),
                            'alert_link'         => esc_url($addOn->getIcon()),
                            'alert_link_text'    => esc_html__('Learn how to whitelist your domain', 'wp-statistics'),
                        ];
                        View::load('components/addon-box', $args);
                    }
                    ?>
                </div>
            </div>
            <div>
                <h2 class="wps-postbox-addon__title"><?php esc_html_e('Inactive Add-Ons', 'wp-statistics'); ?></h2>
                <div class="wps-postbox-addon__items">
                    <?php
                    /** @var ProductDecorator $addOn */
                    foreach ($inactiveAddOns as $addOn) {
                        $args = [
                            'addOn'              => $addOn,
                            'has_license_btn'    => true,
                            'setting_link'       => '#',
                            'detail_link'        => '#',
                            'change_log_link'    => '#',
                            'documentation_link' => '#',
                        ];
                        View::load('components/addon-box', $args);
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>