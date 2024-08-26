<?php
use WP_STATISTICS\Helper;
?>
<div class="wps-wrap__top tabbed_page">
    <h2 class="wps_title">
        <span><?php echo(isset($title) ? esc_html($title) : (function_exists('get_admin_page_title') ? esc_html(get_admin_page_title()) : '')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
        <?php if (!empty($tooltip)) : ?>
            <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
        <?php endif; ?>
    </h2>

    <?php if (!empty($description)) echo '<p class="wps_description">' . esc_html($description) . '</p>'    // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	?>

    <?php do_action('wp_statistics_after_admin_page_title'); ?>

    <?php if (isset($real_time_button)): ?>
        <?php
        $is_realtime_active = Helper::isAddOnActive('realtime-stats');
        $button_class       = $is_realtime_active ? 'wps-realtime-btn' : 'wps-realtime-btn disabled';
        $button_title       = $is_realtime_active ? 'Real-time stats are available! Click here to view.' : 'Real-Time add-on required to enable this feature';
        $button_href        = $is_realtime_active ? admin_url('admin.php?page=wp_statistics_realtime_stats') : WP_STATISTICS_SITE_URL . '/product/wp-statistics-realtime-stats/?utm_source=wp-statistics&utm_medium=link&utm_campaign=realtime';
        ?>
        <a target="_blank" class="<?php echo esc_html($button_class); ?>" href="<?php echo esc_url($button_href) ?>" title="<?php echo esc_html_e($button_title, 'wp-statistics') ?>">
            <?php esc_html_e('Realtime', 'wp-statistics'); ?>
        </a>
    <?php endif; ?>

    <div class="wp-clearfix"></div>

    <?php
    $active_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
    if (isset($datepicker)): ?>
        <form class="wps-search-date wps-today-datepicker" method="get">

            <div>
                <input type="hidden" name="page" value="<?php echo esc_attr($pageName); ?>">
                <input type="hidden" name="tab" id="active-tab-input" value="<?php echo esc_attr($active_tab); ?>">
                <input class="wps-search-date__input wps-js-calendar-field" id="search-date-input" type="text" size="18" name="day" data-wps-date-picker="day" readonly value="<?php echo esc_attr($day); ?>" autocomplete="off" placeholder="YYYY-MM-DD" required>
            </div>
        </form>
    <?php endif ?>

    <?php if (isset($hasDateRang) || isset($filters)) : ?>
        <div class="wps-head-filters">
            <?php
            if (!empty($hasDateRang)) {
                include 'date.range.php';
            }

            if (!empty($filters)) {
                foreach ($filters as $filter) {
                    require_once "filters/$filter-filter.php";
                }
            }
            ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($tabs) && is_array($tabs)) { ?>
        <ul class="wps-tabs">
            <?php foreach ($tabs as $tab) { ?>
                <li class="wps-tab-link <?php echo esc_attr($tab['class']); ?>">
                    <?php if (isset($tab['coming_soon'])): ?>
                        <span class="wps-tooltip wps-tooltip--coming_soon" title="<?php echo esc_html__('Coming soon', 'wp-statistics') ?>"><?php echo esc_html($tab['title']); ?> <i class="wps-tooltip-icon coming-soon"></i></span>
                    <?php elseif (isset($tab['locked'])) : ?>
                        <a href="<?php echo esc_attr($tab['link']); ?>" class="wps-locked">
                            <?php echo esc_html($tab['title']); ?>
                            <?php if (!empty($tab['tooltip'])) : ?>
                                <span class="wps-tooltip" title="<?php echo esc_attr($tab['tooltip']) ?>"><i class="wps-tooltip-icon info"></i></span>
                            <?php endif; ?>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo esc_attr($tab['link']); ?>">
                            <?php echo esc_html($tab['title']); ?>
                            <?php if (!empty($tab['tooltip'])) : ?>
                                <span class="wps-tooltip" title="<?php echo esc_attr($tab['tooltip']); ?>"><i class="wps-tooltip-icon info"></i></span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                </li>
            <?php } ?>
        </ul>
    <?php } ?>
</div>
<div class="wps-wrap__main">
    <div class="wp-header-end"></div>