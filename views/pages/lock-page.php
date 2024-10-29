<?php
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

$pluginHandler  = new PluginHandler();
$isLicenseValid         = LicenseHelper::isPluginLicenseValid($addon_slug);
$isAddonActive  = WP_STATISTICS\Helper::isAddOnActive( str_replace("wp-statistics-", "", $addon_slug));
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
        <?php  if (!$isActive && $hasLicense) : ?>
           <div class="wps-premium-feature wps-premium-feature--premium-user">
               <?php
                View::load("components/lock-sections/setting-active-licensed-addon", ['addon_title' => $addon_name]);
               ?>
           </div>
        <?php endif ?>
        <div class="wps-lock-page__head">
             <?php echo $page_title;?>
         </div>
        <div class="wps-lock-page__description">
            <?php echo $description?>
        </div>

         <div class="wps-lock-page__actions">
            <a target="_blank" class="wps-lock-page__action wps-lock-page__action--premium" href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/add-ons/'.$addon_slug.'?utm_source=wp-statistics&utm_medium=link&utm_campaign=dp-'.$campaign); ?>">
                <?php echo sprintf(esc_html__('Go Premium for Complete %s Reports', 'wp-statistics'), $addon_name); ?>
            </a>
             <a data-target="<?php echo $addon_slug?>" class="wps-lock-page__action wps-lock-page__action--learn-more js-wps-openPremiumModal"><?php esc_html_e('Learn More', 'wp-statistics'); ?></a>
        </div>

        <div class="wps-lock-page__slider">
            <div class="wps-slider">
                <?php foreach ($images as $image) : ?>
                    <div class="wps-slider__slide">
                        <img src="<?php echo esc_url(WP_STATISTICS_URL . $image) ; ?>" alt="<?php echo esc_attr($addon_name); ?> Image">
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="wps-slider__dots"></div> <!-- Dots container -->
        </div>
    </div>
