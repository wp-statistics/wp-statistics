<?php

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginDecorator;

$type = $data['license_notice_type'];
?>

<div class="postbox-container wps-postbox-addon-container">

    <?php
    switch ($type) {
        case 'expired_license':
            View::load('components/notices/expired-license', ['data' => $data]);
            break;
        case 'no_license_for_addons':
            View::load('components/notices/no-license-for-addons', ['data' => $data]);
            break;
        case 'missing_addons':
            View::load('components/notices/missing-addons', ['data' => $data]);
            break;
        case 'inactive_addons':
            View::load('components/notices/inactive-addons', ['data' => $data]);
            break;
        case 'no_license':
            View::load('components/notices/no-license', ['data' => $data]);
            break;
        case 'upgrade_to_premium':
            View::load('components/notices/upgrade-to-premium', ['data' => $data]);
            break;
    }
    ?>

    <div class="wps-postbox-addon">
        <?php if (!empty($data['active_addons']) && is_array($data['active_addons'])) : ?>
            <div>
                <h2 class="wps-postbox-addon__title"><?php esc_html_e('Active Add-Ons', 'wp-statistics'); ?></h2>
                <div class="wps-postbox-addon__items">
                    <?php
                    /** @var PluginDecorator $addOn */
                    foreach ($data['active_addons'] as $addOn) {
                        View::load('components/addon-box', ['addOn' => $addOn]);
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
        <?php if (!empty($data['inactive_addons']) && is_array($data['active_addons'])) : ?>
            <div>
                <h2 class="wps-postbox-addon__title"><?php esc_html_e('Inactive Add-Ons', 'wp-statistics'); ?></h2>
                <div class="wps-postbox-addon__items">
                    <?php
                    /** @var PluginDecorator $addOn */
                    foreach ($data['inactive_addons'] as $addOn) {
                        View::load('components/addon-box', ['addOn' => $addOn]);
                    }
                    ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>