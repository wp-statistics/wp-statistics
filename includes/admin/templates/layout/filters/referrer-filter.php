<?php
$activeFilters = 0;

foreach ($_GET as $params_key => $params_item) {
    if (in_array($params_key, ['referrer'])) {
        $activeFilters++;
    }
}

$classes[] = $activeFilters > 0 ? 'wp-referral-filter--active' : '';
$classes[] = is_rtl() ? 'wps-pull-left' : 'wps-pull-right';
?>

<div class="<?php echo esc_attr(implode(' ', $classes)) ?>" id="referral-filter">
    <span class="dashicons dashicons-filter"></span>
    <span class="wps-referral-filter__text">
        <span class="filter-text"><?php esc_html_e("Filters", "wp-statistics") ?></span>
        <?php if ($activeFilters > 0) : ?>
            <span class="wps-badge"><?php echo esc_html($activeFilters) ?></span>
        <?php endif; ?>
    </span>
</div>