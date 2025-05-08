<?php

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginDecorator;

?>

<div class="postbox-container wps-postbox-addon-container">

    <?php if (!empty($data['invalid_licenses']) && is_array($data['invalid_licenses'])): ?>
        <div class="wps-notice wps-notice--danger">
            <div>
                <p class="wps-notice__title"><?php esc_html_e('Expired or Invalid License', 'wp-statistics'); ?></p>
                <div class="wps-notice__description">
                    <?php
                    echo wp_kses_post(sprintf(
                        __('Your WP Statistics license %s has expired or isn’t valid. Without a valid license, we can’t ensure security or compatibility updates. <br> <a href="%s">Renew</a> or update your license to keep everything running smoothly. <br> Need help? <a href="%s">Contact Support</a>', 'wp-statistics'),
                        implode(", ", $data['invalid_licenses']),
                        esc_url($data['install_addon_link']),
                        esc_url("https://wp-statistics.com/contact-us/?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon")
                    ));
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$data['has_valid_premium_license'] && !empty($data['unlicensed_installed_add_ons']) && is_array($data['unlicensed_installed_add_ons'])): ?>
        <div class="wps-notice wps-notice--danger">
            <div>
                <p class="wps-notice__title"><?php esc_html_e('No License for Installed Add-ons', 'wp-statistics'); ?></p>
                <div class="wps-notice__description">
                    <?php esc_html_e('You’ve installed the following add-ons, but we couldn’t find valid licenses for them:', 'wp-statistics') ?>
                    <ul>
                        <?php foreach ($data['unlicensed_installed_add_ons'] as $addOn): ?>
                            <li>
                                <a href="<?php echo esc_url($addOn->getProductUrl()); ?>?utm_source=wp-statistics&utm_medium=link&utm_campaign=dp" target="_blank">
                                    <?php echo esc_html($addOn->getName()) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php
                    echo wp_kses_post(sprintf(
                        __('Without valid licenses, these add-ons won’t receive critical updates or new features. Please add a valid license to ensure ongoing compatibility and support. Have questions? <a href="%s" target="_blank">Contact Support</a>', 'wp-statistics'),
                        esc_url("https://wp-statistics.com/contact-us/?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon"),
                    ));
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($data['has_valid_premium_license'] && !empty($data['missing_add_ons']) && is_array($data['missing_add_ons'])): ?>
        <div class="wps-notice wps-notice--warning">
            <div>
                <p class="wps-notice__title"><?php esc_html_e('Some Add-ons Are Missing', 'wp-statistics'); ?></p>
                <div class="wps-notice__description">
                    <?php esc_html_e('You have a valid WP Statistics license, but you haven’t installed the following add-ons yet:', 'wp-statistics') ?>
                    <ul>
                        <?php foreach ($data['missing_add_ons'] as $addOn): ?>
                            <li>
                                <a href="<?php echo esc_url($addOn->getProductUrl()); ?>?utm_source=wp-statistics&utm_medium=link&utm_campaign=dp" target="_blank">
                                    <?php echo esc_html($addOn->getName()) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php esc_html_e('Install them now to take full advantage of your WP Statistics.', 'wp-statistics') ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($data['inactive_installed_add_ons']) && is_array($data['inactive_installed_add_ons'])): ?>
        <div class="wps-notice wps-notice--warning">
            <div>
                <p class="wps-notice__title"><?php esc_html_e('Inactive Add-ons', 'wp-statistics'); ?></p>
                <div class="wps-notice__description">
                    <?php esc_html_e('You’ve installed the following WP Statistics add-ons, but they’re currently inactive:', 'wp-statistics'); ?>
                    <ul>
                        <?php foreach ($data['inactive_installed_add_ons'] as $addOn): ?>
                            <li>
                                <a href="<?php echo esc_url($addOn->getProductUrl()); ?>?utm_source=wp-statistics&utm_medium=link&utm_campaign=dp" target="_blank">
                                    <?php echo esc_html($addOn->getName()) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php esc_html_e('Activate them now to unlock their features and get the most out of WP Statistics.', 'wp-statistics'); ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!$data['has_any_license']): ?>
        <div class="wps-notice wps-notice--success">
            <div>
                <p class="wps-notice__title"><?php esc_html_e('No WP Statistics License Detected', 'wp-statistics') ?></p>
                <div class="wps-notice__description">
                    <?php
                    echo wp_kses_post(sprintf(
                        __('You haven’t registered a WP Statistics license yet. Having a valid license unlocks premium add-ons and features. <a href="%s" target="_blank">Purchase</a> or <a href="%s">add a license</a> now to get started!.', 'wp-statistics'),
                        esc_url('https://wp-statistics.com/pricing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon'),
                        esc_url($data['install_addon_link'])
                    ));
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($data['has_any_license'] && !$data['has_valid_premium_license']): ?>
        <div class="wps-notice wps-notice--success">
            <div>
                <p class="wps-notice__title"><?php esc_html_e('Upgrade to WP Statistics Premium', 'wp-statistics') ?></p>
                <div class="wps-notice__description">
                    <?php
                    echo wp_kses_post(sprintf(
                        __('Want more powerful analytics? Upgrade to our Premium license to unlock advanced add-ons, enhanced features, and priority support. <br> <a href="%s" target="_blank">Upgrade Now</a> or <a href="%s" target="_blank">Learn More</a>. <br> Have questions? <a href="%s" target="_blank">Contact Support</a>', 'wp-statistics'),
                        esc_url("https://wp-statistics.com/pricing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon"),
                        esc_url("https://wp-statistics.com/support/?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon"),
                        esc_url("https://wp-statistics.com/contact-us/?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon"),
                    ));
                    ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

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