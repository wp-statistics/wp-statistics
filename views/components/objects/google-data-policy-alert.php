<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly ?>
<div class="wps-addon-settings-marketing--alert">
    <div>
        <h3><?php esc_html_e('Privacy & Data', 'wp-statistics'); ?></h3>
        <p><?php echo wp_kses_post($content); ?></p>
    </div>
</div>