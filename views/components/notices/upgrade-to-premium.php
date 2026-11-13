<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$upgradeUrl = 'https://wp-statistics.com/pricing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon';
$learnUrl   = 'https://wp-statistics.com/support/?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon';
$supportUrl = 'https://wp-statistics.com/contact-us/?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon';
?>
<div class="wps-licnotice wps-licnotice--success" role="status">
    <span class="wps-licnotice__icon" aria-hidden="true">✓</span>
    <div class="wps-licnotice__body">
        <h2 class="wps-licnotice__title"><?php esc_html_e('Upgrade to WP Statistics Premium', 'wp-statistics'); ?></h2>
        <p class="wps-licnotice__text"><?php esc_html_e('Want more powerful analytics? Premium unlocks every add-on, enhanced features and priority support.', 'wp-statistics'); ?></p>
        <div class="wps-licnotice__actions">
            <a class="wps-licnotice__button" href="<?php echo esc_url($upgradeUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Upgrade now', 'wp-statistics'); ?></a>
            <a class="wps-licnotice__link" href="<?php echo esc_url($learnUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Learn more', 'wp-statistics'); ?></a>
            <a class="wps-licnotice__link" href="<?php echo esc_url($supportUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Contact support', 'wp-statistics'); ?></a>
        </div>
    </div>
</div>
