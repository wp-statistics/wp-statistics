<?php

use WP_Statistics\Decorators\UserDecorator;

?>

<?php /** @var UserDecorator $user */ ?>
<div class="wps-visitor__visitors-details">
    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Username', 'wp-statistics'); ?></span>
        <div>
            <a href="<?php echo get_edit_user_link($user->getId()); ?>" class="wps-visitor__username">
                <img src="<?php echo WP_STATISTICS_URL . 'assets/images/user-icon.svg' ?>" width="19" height="19">
                <span><?php echo sprintf('%s (#%s)', $user->getDisplayName(), $user->getId()); ?></span>
            </a>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Email address', 'wp-statistics'); ?></span>
        <div>
            <a href="mailto:<?php echo esc_attr($user->getEmail()); ?>" title="<?php echo esc_attr($user->getEmail()); ?>">
                <span><?php echo esc_html($user->getEmail()); ?></span>
            </a>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Last login', 'wp-statistics'); ?></span>
        <div class="wps-ellipsis-parent">
            <span><?php echo $user->getLastLogin() ? esc_html($user->getLastLogin()) : esc_html('-'); ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Registered', 'wp-statistics'); ?></span>
        <div class="wps-ellipsis-parent">
            <span><?php echo esc_html($user->getRegisteredDate()); ?></span>
        </div>
    </div>

    <div class="wps-visitor__visitors-detail--row">
        <span><?php esc_html_e('Role', 'wp-statistics'); ?></span>
        <div class="wps-ellipsis-parent">
            <span class="c-capitalize"><?php echo esc_html($user->getRole()); ?></span>
        </div>
    </div>
</div>