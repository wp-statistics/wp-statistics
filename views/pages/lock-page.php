<?php

use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

$pluginHandler  = new PluginHandler();
$hasLicense     = LicenseHelper::isPluginLicenseValid($addon_slug) ? true : false;
$isActive       = $pluginHandler->isPluginActive($addon_slug);
$isInstalled    = $pluginHandler->isPluginInstalled($addon_slug);
?>


<div class="wps-wrap__main wps-lock-page__main">
    <div class="wp-header-end"></div>
    <div class="wps-lock-page wps-lock-page--container">
        <?php
        if (!$hasLicense && $isInstalled) :
            View::load("components/lock-sections/notice-inactive-license-addon");
        endif;
        ?>
        <?php if ($hasLicense && !$isActive) : ?>
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


        <?php if (!$hasLicense && !$isActive) : ?>
            <div class="wps-lock-page__actions">
                <a target="_blank" class="wps-lock-page__action wps-lock-page__action--premium" href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/pricing?utm_source=wp-statistics&utm_medium=link&utm_campaign=dp-' . esc_html($campaign)) ?>">
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
