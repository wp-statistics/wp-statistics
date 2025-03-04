<?php

use WP_STATISTICS\Helper;

$id = 'wps-filter-' . $type;
?>

<div id="<?php echo esc_attr($id); ?>" class="wps-filter-post-type wps-head-filters__item">
    <div class="wps-dropdown">
        <label class="selectedItemLabel"><?php echo esc_html($title) ?>: </label>
        <button type="button" class="dropbtn"><span><?php echo ! empty($selectedOption) ? esc_html(Helper::getPostTypeName($selectedOption)) : esc_html__('All', 'wp-statistics'); ?></span></button>
    </div>
</div>