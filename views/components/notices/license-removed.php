<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$accountUrl = 'https://wp-statistics.com/my-account/?utm_source=wp-statistics&utm_medium=link&utm_campaign=remove-license';
$supportUrl = 'https://wp-statistics.com/contact-us/?utm_source=wp-statistics&utm_medium=link&utm_campaign=remove-license';
?>
<div class="wps-licnotice wps-licnotice--success" role="status">
    <span class="wps-licnotice__icon" aria-hidden="true">✓</span>
    <div class="wps-licnotice__body">
        <h2 class="wps-licnotice__title"><?php esc_html_e('License removed from this site', 'wp-statistics'); ?></h2>
        <p class="wps-licnotice__text"><?php esc_html_e('The key is gone from this site. To use it on another site, sign in to your wp-statistics.com account and remove this site from the license there first.', 'wp-statistics'); ?></p>
        <div class="wps-licnotice__actions">
            <a class="wps-licnotice__button" href="<?php echo esc_url($accountUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Open my account', 'wp-statistics'); ?></a>
            <a class="wps-licnotice__link wps-licnotice__support" href="<?php echo esc_url($supportUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Contact support', 'wp-statistics'); ?></a>
        </div>
    </div>
</div>
