<?php

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

if (!WP_STATISTICS\Option::get('usage_data_tracking') && !in_array('usage_data_tracking', get_option('wp_statistics_dismissed_notices', []))) {
    $notice = [
        'title'   => __('Help Us Improve WP Statistics!', 'wp-statistics'),
        'content' => __('We’ve added a new Usage Tracking option to help us understand how WP Statistics is used and identify areas for improvement. By <a href="#" class="wps-option-data" data-option="usage_data_tracking" data-value="true">enabling</a> this feature, you’ll help us make the plugin better for everyone. No personal or sensitive data is collected.', 'wp-statistics'),
        'links'   => [
            'learn_more'      => [
                'text' => __('Learn More', 'wp-statistics'),
                'url'  => '#',
            ],
            'enable_tracking' => [
                'text'  => __('Enable Usage Tracking', 'wp-statistics'),
                'url'   => '#',
                'class' => 'wps-option__updater notice--enable-usage',
            ]
        ]
    ];
    Notice::renderNotice($notice, 'usage_data_tracking', 'setting', true, 'action');
}
?>
<div class="wps-wrap__top <?php echo isset($real_time_button) ? 'wps-wrap__top--has__realtime' : ''; ?>">
    <?php if (isset($backUrl, $backTitle)): ?>
        <a href="<?php echo esc_url($backUrl) ?>" title="<?php echo esc_html($backTitle) ?>" class="wps-previous-url"><?php echo esc_html($backTitle) ?></a>
    <?php endif ?>

    <?php if (isset($title)): ?>
        <h2 class="wps_title <?php echo isset($install_addon_btn_txt) ? 'wps_plugins_page-title' : '' ?>">
            <?php if (isset($flagImage)): ?>
                <img class="wps-flag" src="<?php echo esc_url($flagImage) ?>" alt="<?php echo esc_attr($title) ?>">
            <?php endif ?>
            <?php echo(isset($title) ? esc_attr($title) : (function_exists('get_admin_page_title') ? get_admin_page_title() : '')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?>
            <?php if (!empty($tooltip)) : ?>
                <span class="wps-tooltip" title="<?php echo esc_attr($tooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
            <?php endif; ?>

            <?php if (isset($install_addon_btn_txt)) : ?>
                <a href="<?php echo esc_attr($install_addon_btn_link); ?>" class="wps-install-addon-btn">
                    <span><?php echo esc_attr($install_addon_btn_txt); ?></span>
                </a>
            <?php endif; ?>
        </h2>
    <?php endif ?>
    <?php
    if (Menus::in_page('content-analytics') && Request::compare('type', 'single')) {
        View::load("components/headers/post-type");
    }
    if (Menus::in_page('category-analytics') && Request::compare('type', 'single')) {
        View::load("components/headers/category-analytics");
    }
    if (Menus::in_page('author-analytics') && Request::compare('type', 'single-author')) {
        View::load("components/headers/author-analytics");
    }
    if ((Menus::in_page('download_tracker') || Menus::in_page('link_tracker')) && Request::compare('type', 'single')) {
        View::load("components/headers/tracker");
    }
    ?>

    <?php do_action('wp_statistics_after_admin_page_title'); ?>

    <?php if (isset($real_time_button)): ?>
        <?php
        $is_realtime_active = Helper::isAddOnActive('realtime-stats');
        ?>

        <?php if ($is_realtime_active): ?>
            <a class="wps-realtime-btn" href="<?php echo esc_url(admin_url('admin.php?page=wp_statistics_realtime_stats')) ?>" title="<?php echo esc_html_e('Real-time stats are available! Click here to view', 'wp-statistics') ?>">
                <?php esc_html_e('Realtime', 'wp-statistics'); ?>
            </a>
        <?php else: ?>
            <button class="wps-realtime-btn disabled wps-tooltip-premium">
                <?php esc_html_e('Realtime', 'wp-statistics'); ?>
                <span class="wps-tooltip_templates tooltip-premium tooltip-premium--bottom tooltip-premium--right">
                    <span id="tooltip_realtime">
                        <a data-target="wp-statistics-realtime-stats" class="js-wps-openPremiumModal"><?php esc_html_e('Learn More', 'wp-statistics'); ?></a>
                        <span>
                            <?php esc_html_e('Premium Feature', 'wp-statistics'); ?>
                        </span>
                    </span>
                </span>
            </button>
        <?php endif ?>
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
        <div class="<?php echo (Menus::in_page('content-analytics') || Menus::in_page('category-analytics') || Menus::in_page('author-analytics') || Menus::in_page('download_tracker') || Menus::in_page('link_tracker')) && (Request::compare('type', 'single') || Request::compare('type', 'single-author')) ? 'wps-head-filters wps-head-filters--custom' : 'wps-head-filters' ?>">
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