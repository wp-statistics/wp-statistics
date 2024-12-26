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
          if ($is_realtime_active): ?>
            <a class="wps-realtime-btn" href="<?php echo esc_url(admin_url('admin.php?page=wp_statistics_realtime_stats')) ?>" title="<?php echo esc_html_e('Real-time stats are available! Click here to view', 'wp-statistics') ?>">
                <?php esc_html_e('Realtime', 'wp-statistics'); ?>
            </a>
        <?php else: ?>
            <button class="wps-realtime-btn disabled wps-tooltip-premium" >
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
                <?php if (!empty($tab['hidden'])) continue; ?>

                <li class="wps-tab-link <?php echo esc_attr($tab['class']); ?>">
                    <?php if (isset($tab['coming_soon'])): ?>
                        <span class="wps-tooltip wps-tooltip--coming_soon" title="<?php echo esc_html__('Coming soon', 'wp-statistics') ?>"><?php echo esc_html($tab['title']); ?> <i class="wps-tooltip-icon coming-soon"></i></span>
                    <?php elseif (isset($tab['locked'])) : ?>
                        <a  data-target="wp-statistics-data-plus"  class="js-wps-openPremiumModal wps-locked">
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