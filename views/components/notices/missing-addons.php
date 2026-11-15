<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

$supportUrl = 'https://wp-statistics.com/contact-us/?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon';
?>
<div class="wps-licnotice wps-licnotice--warning" role="status">
    <span class="wps-licnotice__icon" aria-hidden="true">!</span>
    <div class="wps-licnotice__body">
        <h2 class="wps-licnotice__title"><?php esc_html_e('Some add-ons are not installed yet', 'wp-statistics'); ?></h2>
        <p class="wps-licnotice__text"><?php esc_html_e('Your license includes these add-ons. Install them to get everything you paid for.', 'wp-statistics'); ?></p>
        <ul class="wps-licnotice__list">
            <?php foreach ($data['missing_add_ons'] as $addOn) : ?>
                <li>
                    <a href="<?php echo esc_url($addOn->getProductUrl() . '?utm_source=wp-statistics&utm_medium=link&utm_campaign=' . rawurlencode($addOn->getUtmCampaign())); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($addOn->getName()); ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="wps-licnotice__actions">
            <a class="wps-licnotice__button" href="<?php echo esc_url($data['download_addons_link']); ?>"><?php esc_html_e('Install add-ons', 'wp-statistics'); ?></a>
            <a class="wps-licnotice__link wps-licnotice__support" href="<?php echo esc_url($supportUrl); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Contact support', 'wp-statistics'); ?></a>
        </div>
    </div>
</div>
