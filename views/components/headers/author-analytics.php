<?php

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;

$authorId = Request::get('author_id');
$userData = get_userdata($authorId);

$hasUserData = !empty($userData);

$roles    = $hasUserData ? $userData->roles : [];
$userRole = !empty($roles) ? ucfirst($roles[0]) : esc_html__('No role', 'wp-statistics');

$registeredDate = $hasUserData ? $userData->user_registered : '';
$formattedDate  = $registeredDate ? date_i18n('F j, Y g:i A', strtotime($registeredDate)) : esc_html__('Unknown Date', 'wp-statistics');

$displayName = $hasUserData ? $userData->display_name : esc_html__('Unknown User', 'wp-statistics');
$email       = $hasUserData ? $userData->user_email : '';
?>
<div class="wps-author-analytics--header">
    <div class="wps-author-analytics--header__img">
        <img src="<?php echo esc_url(get_avatar_url($authorId)); ?>" alt="">
    </div>
    <div>
        <div class="wps-author-analytics--header__title">
            <h2 class="wps_title"><?php echo esc_html($displayName); ?></h2>
            <span><?php echo esc_html($userRole); ?></span>
        </div>
        <div class="wps-author-analytics--header__info">
            <span class="wps-author-analytics--header__joined"><?php echo esc_html__('Joined on', 'wp-statistics') ?> <?php echo esc_html($formattedDate); ?></span>
            <a href="<?php echo esc_url(get_edit_user_link($authorId)); ?>"><?php echo esc_html__('Visit Profile', 'wp-statistics') ?></a>
            <a href="<?php echo Menus::admin_url('pages', ['tab' => 'contents', 'author_id' => $authorId, 'pt' => 'post']); ?>"><?php echo esc_html__('View Author Posts', 'wp-statistics') ?></a>
            <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html__('Email to Author', 'wp-statistics') ?></a>
        </div>
    </div>
</div>