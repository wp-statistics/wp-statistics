<?php
use Wp_Statistics\Menus;
?>

<div class="wp-quickstats-widget__enable-email">
    <div class="wp-quickstats-widget__enable-email__desc"><span class="wp-quickstats-widget__enable-email__icon"></span>
        <div>
            <p><?php esc_html_e('Receive Weekly Email Reports', 'wp-statistics'); ?></p>
            <a href="<?php echo esc_url(Menus::admin_url('settings', ['tab' => 'notifications-settings'])) ?>" title="<?php esc_attr_e('Enable Now', 'wp-statistics'); ?>"><?php esc_html_e('Enable Now', 'wp-statistics'); ?></a>
        </div>
    </div>
    <div class="wp-quickstats-widget__enable-email__close"><span id="js-close-notice" class="wp-close" title="Close"></span></div>
</div>