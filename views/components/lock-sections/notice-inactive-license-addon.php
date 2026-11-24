<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>
<div class="wps-notice-settings wps-notice-settings--warning">
    <div>
        <p class="wps-notice-settings__title"> <?php esc_html_e('Notice:', 'wp-statistics')?></p>
        <div class="wps-notice-settings__desc">
            <?php
            echo wp_kses_post(sprintf(
                /* translators: %s: string value */
                __('This add-on has no active license, so it won\'t get updates or security fixes. <a href="%s">Activate your license</a>.', 'wp-statistics'),
                esc_url(admin_url('admin.php?page=wps_plugins_page'))
            ));
            ?>
        </div>
    </div>
</div>