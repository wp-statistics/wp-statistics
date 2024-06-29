<?php

use WP_Statistics\Utils\Request;

$queryKey       = 'pid';
$postId         = Request::get($queryKey, false, 'number');
$defaultTitle   = $postId ? get_the_title($postId) : esc_html__('All', 'wp-statistics');
$defaultUrl     = $postId ? add_query_arg([$queryKey => $postId]) : remove_query_arg($queryKey); // remove post id from query
?>

<div class="wps-filter-page wps-head-filters__item loading">
    <div class="wps-dropdown">
        <label for="wps-page-filter" class="selectedItemLabel"><?php esc_html_e('Page:', 'wp-statistics'); ?></label>
        <select id="wps-page-filter" class="wps-select2" data-type-show="select2">
            <option value="<?php echo esc_url($defaultUrl) ?>" selected>
                <?php echo esc_html($defaultTitle); ?>
            </option>
        </select>
    </div>
</div>

