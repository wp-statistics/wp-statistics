<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly
?>
<div class="wps-licnotice wps-licnotice--warning" role="status">
    <span class="wps-licnotice__icon" aria-hidden="true">!</span>
    <div class="wps-licnotice__body">
        <h2 class="wps-licnotice__title"><?php esc_html_e('Some add-ons are switched off', 'wp-statistics'); ?></h2>
        <p class="wps-licnotice__text"><?php esc_html_e('These add-ons are installed but not active, so their features are not running.', 'wp-statistics'); ?></p>
        <ul class="wps-licnotice__list">
            <?php foreach ($data['inactive_installed_add_ons'] as $addOn) : ?>
                <li>
                    <a href="<?php echo esc_url($addOn->getProductUrl() . '?utm_source=wp-statistics&utm_medium=link&utm_campaign=' . rawurlencode($addOn->getUtmCampaign())); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html($addOn->getName()); ?></a>
                </li>
            <?php endforeach; ?>
        </ul>
        <div class="wps-licnotice__actions">
            <a class="wps-licnotice__button" href="<?php echo esc_url(admin_url('plugins.php')); ?>"><?php esc_html_e('Activate add-ons', 'wp-statistics'); ?></a>
        </div>
    </div>
</div>
