<?php

use WP_Statistics\Utils\Request;

$queryKey       = 'role';
$role           = Request::get($queryKey, '');
$roles          = wp_roles()->role_names;
$defaultUrl     = remove_query_arg([$queryKey]);
?>

<div class="wps-filter-user-role wps-head-filters__item loading">
    <div class="wps-dropdown">
        <label for="wps-user-role" class="selectedItemLabel"><?php esc_html_e('User Role', 'wp-statistics'); ?>:</label>
        <select id="wps-user-role" class="wps-select2" data-type-show="select2">
            <option value="<?php echo esc_url($defaultUrl) ?>" <?php selected($role, '') ?>>
              <?php esc_html_e('All', 'wp-statistics'); ?>
            </option>

            <?php foreach ($roles as $key => $value) : ?>
                <option <?php selected($role, $key) ?> value="<?php echo esc_url(add_query_arg([$queryKey => $key])) ?>">
                    <?php echo esc_html($value); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
