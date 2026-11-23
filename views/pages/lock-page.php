<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\User;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

$pluginHandler  = new PluginHandler();
$hasLicense     = LicenseHelper::isPluginLicenseValid($addon_slug) ? true : false;
$isActive       = $pluginHandler->isPluginActive($addon_slug);
$isInstalled    = $pluginHandler->isPluginInstalled($addon_slug);
$situation      = LicenseHelper::getAddonSituation($addon_slug, $pluginHandler);
$canManage      = User::Access('manage');

// White label (or a hidden Add-ons menu): no upsell, only a neutral line, like the inline teasers.
if ($situation === LicenseHelper::SITUATION_HIDDEN) : ?>
    <div class="wps-wrap__main wps-lock-page__main">
        <div class="wps-lock-page wps-lock-page--container wps-lock-page--unavailable">
            <div class="wps-lock-page__head"><?php esc_html_e('This feature is not available', 'wp-statistics'); ?></div>
            <div class="wps-lock-page__description"><?php esc_html_e('Ask a site admin if you need this report.', 'wp-statistics'); ?></div>
        </div>
    </div>
    <?php
    return;
endif;
?>


<div class="wps-wrap__main wps-lock-page__main">
    <div class="wps-lock-page wps-lock-page--container">
        <?php if ($isInstalled && !$isActive) : ?>
            <div class="wps-notice-settings wps-notice-settings--warning">
                <div>
                    <div class="wps-notice-settings__desc">
                        <?php
                        /* translators: %s: Add-on name. */
                        echo esc_html(sprintf(__('%s is installed but turned off.', 'wp-statistics'), $addon_name));
                        if ($canManage && current_user_can('activate_plugins')) :
                            ?>
                            <a href="<?php echo esc_url(LicenseHelper::getAddonPluginsPageUrl($addon_slug)); ?>"><?php
                                /* translators: %s: Add-on name. */
                                echo esc_html(sprintf(__('Activate %s', 'wp-statistics'), $addon_name)); ?></a>
                        <?php elseif (!$canManage) : ?>
                            <?php /* translators: %s: Add-on name. */ echo esc_html(sprintf(__('Ask a site admin to turn on %s.', 'wp-statistics'), $addon_name)); ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if ($hasLicense && !$isActive && !$isInstalled && $canManage) : ?>
            <div class="wps-premium-feature wps-premium-feature--premium-user">
                <?php
                View::load("components/lock-sections/setting-active-licensed-addon", ['addon_title' => $addon_name]);
                ?>
            </div>
        <?php endif ?>
        <div class="wps-lock-page__head">
            <?php echo esc_html($page_title); ?>
        </div>
        <div class="wps-lock-page__description">
            <?php echo esc_html($description); ?>
        </div>

        <?php if (!empty($page_second_title) || !empty($second_description)): ?>
            <div class="wps-lock-page__head wps-lock-page__head--second">
                <?php if (!empty($page_second_title)) echo esc_html($page_second_title); ?>
            </div>
            <div class="wps-lock-page__description">
                <?php if (!empty($second_description)) echo esc_html($second_description); ?>
            </div>
        <?php endif; ?>


        <?php if (!$hasLicense && !$isActive && !$isInstalled && $canManage) : ?>
            <div class="wps-lock-page__actions">
                <a target="_blank" class="wps-lock-page__action wps-lock-page__action--premium" href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/pricing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=' . esc_html($campaign)) ?>">
                    <?php echo esc_html($premium_btn_title); ?>
                </a>
                <a data-target="<?php echo esc_attr($addon_slug) ?>" class="wps-lock-page__action wps-lock-page__action--learn-more js-wps-openPremiumModal"><?php echo esc_html($more_title) ?></a>
            </div>
        <?php endif; ?>

        <div class="wps-lock-page__slider">
            <div class="wps-slider">
                <?php foreach ($images as $image) : ?>
                    <div class="wps-slider__slide">
                        <img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/locked/' . $image); ?>" alt="<?php echo esc_attr($addon_slug); ?> Image">
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="wps-slider__dots"></div> <!-- Dots container -->
        </div>
    </div>
