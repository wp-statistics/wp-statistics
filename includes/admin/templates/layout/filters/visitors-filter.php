<?php 
$activeFilters = 0;

foreach ($_GET as $params_key => $params_item) {
    if (in_array($params_key, ['agent', 'location', 'platform', 'referrer' , 'user_id' ,'ip'])) {
        $activeFilters++;
    }
}

$classes[] = $activeFilters > 0 ? 'wp-visitors-filter--active' : '';
$classes[] = is_rtl() ? 'wps-pull-left' : 'wps-pull-right';
?>

<div class="<?php echo esc_attr(implode(' ', $classes)) ?>" id="visitors-filter">
    <span class="dashicons dashicons-filter"></span>
    <span class="wps-visitor-filter__text">
        <span class="filter-text"><?php esc_html_e("Filters", "wp-statistics") ?></span>
        <?php if ($activeFilters > 0) : ?>
            <span class="wps-badge"><?php echo esc_html($activeFilters) ?></span>
        <?php endif; ?>
    </span>
</div>