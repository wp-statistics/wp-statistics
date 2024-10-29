<?php

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

$pluginHandler  = new PluginHandler();
$isLicenseValid = LicenseHelper::isPluginLicenseValid($addon_slug);
$isAddonActive  = WP_STATISTICS\Helper::isAddOnActive(str_replace("wp-statistics-", "", $addon_slug));
$hasLicense     = LicenseHelper::isPluginLicenseValid($addon_slug) ? true : false;
$isActive       = $pluginHandler->isPluginActive($addon_slug);
?>

<div class="wps-wrap__main">
    <div class="wp-header-end"></div>
    <div class="wps-lock-page wps-lock-page--container">
        <?php
        if ($isAddonActive && !$isLicenseValid) {
            View::load("components/lock-sections/notice-inactive-license-addon");
        }
        ?>
        <?php if (!$isActive && $hasLicense) : ?>
            <div class="wps-premium-feature wps-premium-feature--premium-user">
                <?php
                View::load("components/lock-sections/setting-active-licensed-addon", ['addon_title' => $feature_name]);
                ?>
            </div>
        <?php endif ?>
        <div class="wps-lock-page__head">
            <?php echo $page_title; ?>
        </div>
        <div class="wps-lock-page__description">
            <?php echo $description ?>
        </div>

        <?php if (!empty($page_second_title) || !empty($second_description)): ?>
            <div class="wps-lock-page__head wps-lock-page__head--second">
                <?php if (!empty($page_second_title)) echo $page_second_title; ?>
            </div>
            <div class="wps-lock-page__description">
                <?php if (!empty($second_description)) echo $second_description; ?>
            </div>
        <?php endif; ?>

        <div class="wps-lock-page__actions">
            <a target="_blank" class="wps-lock-page__action wps-lock-page__action--premium" href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/pricing?utm_source=wp-statistics&utm_medium=link&utm_campaign=dp-' . esc_html($campaign)) ?>">
                <?php echo esc_html($premium_btn_title); ?>
            </a>
            <a data-target="<?php echo esc_attr($addon_slug) ?>" class="wps-lock-page__action wps-lock-page__action--learn-more js-wps-openPremiumModal"><?php echo esc_html($more_title) ?></a>
        </div>

        <div class="wps-lock-page__slider">
            <div class="wps-slider">
                <?php foreach ($images as $image) : ?>
                    <div class="wps-slider__slide">
                        <img src="<?php echo esc_url(WP_STATISTICS_URL . 'assets/images/locked/' . $image); ?>" alt="<?php echo esc_attr($feature_name); ?> Image">
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="wps-slider__dots"></div> <!-- Dots container -->
        </div>
    </div>
