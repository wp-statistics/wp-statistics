<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>
<div class="wps-notice wps-notice--success">
    <div>
        <p class="wps-notice__title"><?php esc_html_e('No WP Statistics License Detected', 'wp-statistics') ?></p>
        <div class="wps-notice__description">
            <?php
            echo wp_kses_post(sprintf(
                /* translators: %1$s: string value, %2$s: string value */
                __('You haven’t registered a WP Statistics license yet. Having a valid license unlocks premium add-ons and features. <a href="%1$s" target="_blank" rel="noopener">Upgrade to Premium</a> or <a href="%2$s">add a license</a> now to get started!', 'wp-statistics'),
                esc_url(\WP_Statistics\Service\Admin\LicenseManagement\UpsellLink::pricing('notice')),
                esc_url($data['install_addon_link'])
            ));
            ?>
        </div>
    </div>
</div>
