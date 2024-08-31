<?php
?>

<div class="wps-filter-query-params wps-head-filters__item loading">
    <div class="wps-dropdown">
        <label class="selectedItemLabel"><?php esc_html_e('Source Category:', 'wp-statistics'); ?> </label>
        <button type="button" class="dropbtn"><span><?php echo !empty($selectedTitle) ? esc_html($selectedTitle) : esc_html__('All', 'wp-statistics'); ?></span></button>

        <div class="dropdown-content">
            <input type="text" class="wps-search-dropdown">
            <a href="" data-index="0" class="<?php echo !$selected ? 'selected' : '' ?>"><?php esc_html_e('All', 'wp-statistics'); ?></a>
            <a href="" data-index="2" title="" class="dropdown-item">
                test
            </a>
        </div>
    </div>
</div>