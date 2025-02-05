<div class="<?php echo esc_attr($classes) ?>" id="<?php echo esc_attr($filter_type . '-filter'); ?>">
    <span class="dashicons dashicons-filter"></span>
    <span class="wps-visitor-filter__text">
        <span class="filter-text"><?php esc_html_e("Filters", "wp-statistics") ?></span>
        <?php if ($activeFilters > 0) : ?>
            <span class="wps-badge"><?php echo esc_html($activeFilters) ?></span>
        <?php endif; ?>
    </span>
</div>
