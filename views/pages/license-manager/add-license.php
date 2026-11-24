<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHelper;

// A site that already has a valid license is not asked to buy Premium again: it can add another key or download its add-ons.
$validKeys  = array_keys(LicenseHelper::getLicenses('valid'));
$hasLicense = !empty($validKeys);
$hasPremium = (bool)LicenseHelper::isPremiumLicenseAvailable();
// No download link when every licensed add-on is already installed.
$canDownload = $hasLicense && PluginHelper::hasAddonsToDownload();
?>
<div class="wps-wrap__main">
    <div class="wp-header-end"></div>

    <div class="wps-postbox-addon__step">
        <div class="wps-addon__step__info">
            <span class="wps-addon__step__image wps-addon__step__image--lock"></span>
            <?php if ($hasLicense) : ?>
                <h2 class="wps-addon__step__title"><?php esc_html_e('Add Another License', 'wp-statistics'); ?></h2>
                <p class="wps-addon__step__desc"><?php esc_html_e('Your license is already active on this site. Enter another license key only if you bought more add-ons.', 'wp-statistics'); ?></p>
            <?php else : ?>
                <h2 class="wps-addon__step__title"><?php esc_html_e('Unlock Premium Features', 'wp-statistics'); ?></h2>
                <p class="wps-addon__step__desc"><?php esc_html_e('Enter your license key to unlock premium add-ons and enhance your experience.', 'wp-statistics'); ?></p>
            <?php endif; ?>
        </div>
        <div class="wps-addon__step__license">
            <div class="wps-addon__step__active-license">
                <!--   Add wps-danger or wps-warning class to input-->
                <input id="wps-addon__step__license-input" aria-label="<?php esc_html_e('License', 'wp-statistics'); ?>" type="text" placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                <button class="wps-postbox-addon-button js-addon-active-license disabled"><?php esc_html_e('Activate License', 'wp-statistics'); ?></button>
            </div>
         </div>
        <div class="wps-addon__step__faq">
            <ul>
                <?php if ($canDownload) : ?>
                    <li>
                        <a href="<?php echo esc_url(Menus::admin_url('plugins', ['tab' => 'downloads', 'license_key' => $validKeys[0]])); ?>"><?php esc_html_e('Download your add-ons', 'wp-statistics'); ?></a>
                    </li>
                <?php endif; ?>
                <?php if (!$hasPremium) : ?>
                    <li>
                        <a href="<?php echo esc_url(\WP_Statistics\Service\Admin\LicenseManagement\UpsellLink::pricing('add-ons-page')); ?>" target="_blank" rel="noopener"><?php esc_html_e('Upgrade to Premium', 'wp-statistics'); ?></a>
                    </li>
                <?php endif; ?>
                <li>
                    <a href="<?php echo esc_url(\WP_Statistics\Service\Admin\LicenseManagement\UpsellLink::tag(WP_STATISTICS_SITE_URL . '/resources/finding-and-entering-your-license-key/', 'add-ons-page', 'premium', 'license-key')); ?>" target="_blank" aria-label="<?php esc_html_e('I bought Premium, where is my license key?', 'wp-statistics'); ?>"><?php esc_html_e('I bought Premium, where is my license key?', 'wp-statistics'); ?></a>
                </li>
                <li>
                    <a href="<?php echo esc_url(\WP_Statistics\Service\Admin\LicenseManagement\UpsellLink::tag(WP_STATISTICS_SITE_URL . '/contact-us/?step=purchasing', 'add-ons-page', 'premium', 'support')); ?>" target="_blank" aria-label="<?php esc_html_e('Have questions or trouble activating your license?', 'wp-statistics'); ?>"><?php esc_html_e('Have questions or trouble activating your license?', 'wp-statistics'); ?></a>
                </li>
            </ul>
        </div>
        <a class="wps-addon__step__back-to-addons" href="<?php echo esc_url(admin_url('admin.php?page=wps_plugins_page')) ?>" aria-label="<?php esc_html_e('Back to Add-ons', 'wp-statistics'); ?>"><?php esc_html_e('Back to Add-ons', 'wp-statistics'); ?></a>

    </div>
