<?php 
use WP_STATISTICS\Helper;
use WP_STATISTICS\User;
?>

<div class="wps-visitor__visitors-details">
    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Username', 'wp-statistics'); ?></span>
        <div>
            <a href="<?php echo get_edit_user_link($user->ID); ?>" class="wps-visitor__username">
                <img src="<?php echo WP_STATISTICS_URL . 'assets/images/user-icon.svg' ?>" width="19" height="19">
                <span><?php echo sprintf('%s (#%s)', $user->display_name, $user->ID); ?></span>
            </a>
        </div>
    </div>
    
    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Email address', 'wp-statistics'); ?></span>
        <div>
            <a href="mailto:<?php echo esc_attr($user->user_email); ?>" title="<?php echo esc_attr($user->user_email); ?>">
                <span><?php echo esc_html($user->user_email); ?></span>
            </a>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Last login', 'wp-statistics'); ?></span>
        <div class="wps-ellipsis-parent">
            <?php $lastLogin = User::getLastLogin($user->ID); ?>
            <span><?php echo $lastLogin ? esc_html(date_i18n(Helper::getDefaultDateFormat(true), $lastLogin)) : esc_html('-'); ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Registered', 'wp-statistics'); ?></span>
        <div class="wps-ellipsis-parent">
            <span><?php echo esc_html(date_i18n(Helper::getDefaultDateFormat(true), strtotime($user->user_registered))); ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Role', 'wp-statistics'); ?></span>
        <div class="wps-ellipsis-parent">
            <span class="c-capitalize"><?php echo esc_html($user->roles[0]); ?></span>
        </div>
    </div>
</div>