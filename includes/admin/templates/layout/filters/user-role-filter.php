<?php

use WP_Statistics\Utils\Request;

$queryKey       = 'pid';
$postId         = Request::get($queryKey, false, 'number');
$defaultTitle   = $postId ? get_the_title($postId) : esc_html__('All', 'wp-statistics');
$defaultUrl     = ''; // remove post id from query
?>

<div class="wps-filter-user-role wps-head-filters__item loading">
    <div class="wps-dropdown">
        <label for="wps-user-role" class="selectedItemLabel"><?php esc_html_e('User Role', 'wp-statistics'); ?>:</label>
        <select id="wps-user-role" class="wps-select2" data-type-show="select2">
            <option value="<?php echo esc_url($defaultUrl) ?>" selected>
                <?php echo esc_html($defaultTitle); ?>
            </option>
        </select>
    </div>
</div>
