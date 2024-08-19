<?php 
use WP_STATISTICS\Helper; 
use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Utils\Request;

?>
<div class="wps-wrap__top <?php echo isset($real_time_button) ? 'wps-wrap__top--has__realtime' : ''; ?>">
    <?php if (isset($backUrl, $backTitle)): ?>
        <a href="<?php echo esc_url($backUrl) ?>" title="<?php echo esc_html($backTitle) ?>" class="wps-previous-url"><?php echo esc_html($backTitle) ?></a>
    <?php endif ?>
    
    <?php if (isset($title)): ?>
        <h2 class="wps_title"><?php echo(isset($title) ? esc_attr($title) : (function_exists('get_admin_page_title') ? get_admin_page_title() : '')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?>
            <?php if (!empty($tooltip)) : ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif; ?>
        </h2>
    <?php endif ?>

    <?php 
        if (Menus::in_page('content-analytics') && Request::compare('type', 'single')) {
            Admin_Template::get_template(['layout/content-analytics/post-type-header']);
        }
    ?>

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
    <?php if (isset($Datepicker)): ?>
        <form class="wps-search-date wps-today-datepicker" method="get">
            <div>
                <input type="hidden" name="page" value="<?php echo esc_attr($pageName); ?>">
                <input class="wps-search-date__input wps-js-calendar-field" id="search-date-input" type="text" size="18" name="day" data-wps-date-picker="day" readonly value="<?php echo esc_attr($day); ?>" autocomplete="off" placeholder="YYYY-MM-DD" required>
            </div>
        </form>
    <?php endif ?>

    <?php if (isset($hasDateRang) || isset($filters) || isset($searchBoxTitle) || isset($filter)): ?>
        <div class="wps-head-filters">
            <?php
            if (!empty($hasDateRang)) {
                include 'date.range.php';
            }

            if (isset($filter) and isset($filter['code'])) {
                echo $filter['code']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
                <div class="wp-clearfix"></div>
                <?php
            }

            if (!empty($filters)) {
                foreach ($filters as $filter) {
                    require_once "filters/$filter-filter.php";
                }
            }

            if (isset($searchBoxTitle)): ?>
                <div class="wps-filter-visitor wps-head-filters__item loading">
                    <div class="wps-dropdown">
                        <label for="wps-visitor-filter" class="selectedItemLabel"><?php echo esc_attr($searchBoxTitle); ?></label>
                        <select id="wps-visitor-filter" class="wps-select2" data-type-show="select2"></select>
                    </div>
                </div>
            <?php endif ?>
        </div>
    <?php endif ?>
</div>
<div class="wps-wrap__main">
    <div class="wp-header-end"></div>