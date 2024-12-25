<?php

use WP_Statistics\Utils\Request;

$queryKey       = 'role';
$role           = Request::get($queryKey, '');
$roles          = wp_roles()->role_names;
$selectedTitle  = $roles[$role] ?? '';
$defaultUrl     = remove_query_arg([$queryKey]);
?>
<div class="wps-head-filters__item loading">
    <div class="wps-dropdown">
        <label class="selectedItemLabel"><?php esc_html_e('User Role', 'wp-statistics'); ?>: </label>
        <button type="button" class="dropbtn"><span><?php echo !empty($selectedTitle) ? esc_html($selectedTitle) : esc_html__('All', 'wp-statistics'); ?></span></button>

        <div class="dropdown-content">
            <input type="text" class="wps-search-dropdown">
            <a href="<?php echo esc_url($defaultUrl); ?>" data-index="0" class="<?php echo !isset($role) ? 'selected' : '' ?>">
                <?php esc_html_e('All', 'wp-statistics'); ?>
            </a>
            <?php foreach ($roles as $key => $value) : ?>
                <a href="<?php echo esc_url(add_query_arg([$queryKey => $key])) ?>" class="dropdown-item <?php echo $role === $key ? 'selected' : ''; ?>">
                    <?php echo esc_html($value); ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

