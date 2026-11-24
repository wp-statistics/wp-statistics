<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$licenseUrl = admin_url('admin.php?page=wps_plugins_page&tab=add-license');
$supportUrl = \WP_Statistics\Service\Admin\LicenseManagement\UpsellLink::tag(WP_STATISTICS_SITE_URL . '/contact-us/', 'notice', 'premium', 'support');
?>
<div class="wps-licnotice" role="alert">
    <span class="wps-licnotice__icon" aria-hidden="true">!</span>
    <div class="wps-licnotice__body">
        <h2 class="wps-licnotice__title"><?php esc_html_e('Add a license for your installed add-ons', 'wp-statistics'); ?></h2>
        <p class="wps-licnotice__text"><?php esc_html_e('These add-ons have no valid license, so they will not get updates or new features.', 'wp-statistics'); ?></p>
        <ul class="wps-licnotice__list">
            <?php foreach ($data['unlicensed_installed_add_ons'] as $addOn) : ?>
                <li>
                    <a href="<?php echo esc_url(\WP_Statistics\Service\Admin\LicenseManagement\UpsellLink::tag($addOn->getProductUrl(), 'notice', $addOn->getSlug(), 'learn-more')); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($addOn->getName()); ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="wps-licnotice__actions">
            <a class="wps-licnotice__button" href="<?php echo esc_url($licenseUrl); ?>"><?php esc_html_e('Add license', 'wp-statistics'); ?></a>
            <a class="wps-licnotice__link wps-licnotice__support" href="<?php echo esc_url($supportUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Contact support', 'wp-statistics'); ?></a>
        </div>
    </div>
</div>
