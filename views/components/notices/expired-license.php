<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$renewUrl   = \WP_Statistics\Service\Admin\LicenseManagement\UpsellLink::tag(WP_STATISTICS_SITE_URL . '/my-account/subscriptions/', 'notice', 'premium', 'renew');
$licenseUrl = admin_url('admin.php?page=wps_plugins_page&tab=add-license');
$supportUrl = \WP_Statistics\Service\Admin\LicenseManagement\UpsellLink::tag(WP_STATISTICS_SITE_URL . '/contact-us/', 'notice', 'premium', 'support');
?>
<div class="wps-licnotice wps-licnotice--danger" role="alert">
    <span class="wps-licnotice__icon" aria-hidden="true">!</span>
    <div class="wps-licnotice__body">
        <h2 class="wps-licnotice__title"><?php esc_html_e('Your license has expired', 'wp-statistics'); ?></h2>
        <p class="wps-licnotice__text"><?php esc_html_e('Without a valid license, your add-ons stop getting security and compatibility updates.', 'wp-statistics'); ?></p>
        <ul class="wps-licnotice__list wps-licnotice__list--keys">
            <?php foreach ($data['invalid_licenses'] as $license) : ?>
                <li><code><?php echo esc_html($license); ?></code></li>
            <?php endforeach; ?>
        </ul>
        <div class="wps-licnotice__actions">
            <a class="wps-licnotice__button" href="<?php echo esc_url($renewUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Renew license', 'wp-statistics'); ?></a>
            <a class="wps-licnotice__link" href="<?php echo esc_url($licenseUrl); ?>"><?php esc_html_e('Enter a new key', 'wp-statistics'); ?></a>
            <a class="wps-licnotice__link wps-licnotice__support" href="<?php echo esc_url($supportUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Contact support', 'wp-statistics'); ?></a>
        </div>
    </div>
</div>
