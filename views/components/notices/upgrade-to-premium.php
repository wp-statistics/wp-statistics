<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_Statistics\Service\Admin\LicenseManagement\UpsellLink;

// "Upgrade to Premium" = pricing page, "Learn more" = the add-ons list (both new tab).
$upgradeUrl = UpsellLink::pricing('notice');
$learnUrl   = UpsellLink::addons('notice');
$supportUrl = UpsellLink::tag(WP_STATISTICS_SITE_URL . '/contact-us/', 'notice', 'premium', 'support');
?>
<div class="wps-licnotice wps-licnotice--success" role="status">
    <span class="wps-licnotice__icon" aria-hidden="true">✓</span>
    <div class="wps-licnotice__body">
        <h2 class="wps-licnotice__title"><?php esc_html_e('Upgrade to WP Statistics Premium', 'wp-statistics'); ?></h2>
        <p class="wps-licnotice__text"><?php esc_html_e('Want more powerful analytics? Premium unlocks every add-on, enhanced features and priority support.', 'wp-statistics'); ?></p>
        <div class="wps-licnotice__actions">
            <a class="wps-licnotice__button" href="<?php echo esc_url($upgradeUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Upgrade to Premium', 'wp-statistics'); ?></a>
            <a class="wps-licnotice__link" href="<?php echo esc_url($learnUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Learn more', 'wp-statistics'); ?></a>
            <a class="wps-licnotice__link wps-licnotice__support" href="<?php echo esc_url($supportUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Contact support', 'wp-statistics'); ?></a>
        </div>
    </div>
</div>
