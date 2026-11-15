<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginDecorator;

$type = $data['license_notice_type'];

// Free site with no license and no add-on: show the Premium tour first, and keep the add-ons under a fold.
$isFreeTour = $type === 'no_license' && empty($data['active_addons']);
?>

<div class="postbox-container wps-postbox-addon-container<?php echo $isFreeTour ? ' wps-postbox-addon-container--tour' : ''; ?>">

    <?php
    if ($isFreeTour) {
        View::load('components/premium-showcase');
        echo '<details class="wps-fold" id="wps-addons-fold"><summary class="wps-fold__summary"><span>🔑 ' . esc_html__('Already have a license? Install or manage add-ons', 'wp-statistics') . '</span><span class="wps-fold__chev" aria-hidden="true">▾</span></summary><div class="wps-fold__body">';
        $type = '';
    }

    // Shown once after a license was removed from this site.
    if (!empty($_GET['license_removed'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        View::load('components/notices/license-removed', ['data' => $data]);
    }

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

    <?php
    if (!$isFreeTour) {
        View::load('components/licenses-card', ['data' => $data]);
    }
    ?>

    <div class="wps-postbox-addon">
        <?php if (!empty($data['active_addons']) && is_array($data['active_addons'])) : ?>
            <div>
                <h2 class="wps-postbox-addon__title"><?php esc_html_e('Active Add-ons', 'wp-statistics'); ?></h2>
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
                <h2 class="wps-postbox-addon__title"><?php esc_html_e('Inactive Add-ons', 'wp-statistics'); ?></h2>
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
    <?php if ($isFreeTour) : ?>
        </div></details>
    <?php endif; ?>
</div>
