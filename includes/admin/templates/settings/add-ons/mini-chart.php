<?php

use WP_STATISTICS\Admin_Template;

$isMiniChartActive         = WP_STATISTICS\Helper::isAddOnActive('mini-chart');
$miniChartDefaultPostTypes = get_post_types(array(
    'public'   => true,
    '_builtin' => false
), 'objects');

$miniChartPostTypes = [
    'post' => __('Posts', 'wp-statistics-mini-chart'),
    'page' => __('Pages', 'wp-statistics-mini-chart'),
];
foreach ($miniChartDefaultPostTypes as $postType) {
    $miniChartPostTypes[$postType->name] = $postType->label;
}

$miniChartPostTypesOptions = array();
foreach ($miniChartPostTypes as $name => $label) {
    // translators: %s: Post type label.
    $miniChartPostTypesOptions[$name] = sprintf(__('Enable Mini Chart for %s', 'wp-statistics-mini-chart'), $label);
}
?>

<?php
if (!$isMiniChartActive) echo Admin_Template::get_template('layout/partials/addon-premium-feature',
    ['addon_slug'        => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-mini-chart/?utm_source=wp-statistics&utm_medium=link&utm_campaign=plugin-settings'),
     'addon_title'       => __('Mini Chart Add-On', 'wp-statistics'),
     'addon_description' => __('The settings on this page are part of the Mini Chart add-on, which provides tiny charts for all your posts and pages, along with an Admin Bar for quick access to traffic data.', 'wp-statistics'),
     'addon_features'    => [
         __('Tiny charts for posts and pages to measure performance.', 'wp-statistics'),
         __('Admin Bar for easy access to traffic data.', 'wp-statistics'),
         __('Customizable chart type and color.', 'wp-statistics'),
     ],
     'addon_info'        => __('Get clear insights into your website\'s traffic and content success with the Mini Chart add-on.', 'wp-statistics'),
    ], true);
?>

<div class="postbox">
    <table class="form-table <?php echo !$isMiniChartActive ? 'form-table--preview' : '' ?>">
        <tbody>
        <tr>
            <th scope="row" colspan="2"><h3><?php esc_html_e('Chart Preferences', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr>
            <th scope="row">
                <label for="mini-chart-interval-time"><?php esc_html_e('Chart Display', 'wp-statistics'); ?></label>
            </th>

            <td>
                <?php foreach ($miniChartPostTypesOptions as $key => $title) { ?>
                    <p>
                        <input id="wps_addon_settings[mini_chart][active_mini_chart_<?php echo esc_attr($key); ?>]" name="wps_addon_settings[mini_chart][active_mini_chart_<?php echo esc_attr($key); ?>]" type="checkbox" value="1" <?php checked(WP_STATISTICS\Option::getByAddon("active_mini_chart_{$key}", 'mini_chart')) ?>>
                        <label for="wps_addon_settings[mini_chart][active_mini_chart_<?php echo esc_attr($key); ?>]"><?php echo esc_html($title); ?></label>
                    </p>
                <?php } ?>
                <p class="description"><?php esc_html_e('Select which post types to show mini charts for.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mini-chart-metric"><?php esc_html_e('Chart Metric', 'wp-statistics'); ?></label>
            </th>

            <td>
                <select name="wps_addon_settings[mini_chart][metric]" id="mini-chart-metric">
                    <option value="visitors" <?php selected(WP_STATISTICS\Option::getByAddon('metric', 'mini_chart', 'visitors'), 'visitors'); ?>><?php esc_html_e('Visitors', 'wp-statistics'); ?></option>
                    <option value="views" <?php selected(WP_STATISTICS\Option::getByAddon('metric', 'mini_chart', 'visitors'), 'views'); ?>><?php esc_html_e('Views', 'wp-statistics'); ?></option>
                </select>
                <p class="description">
                    <?php _e('Choose the metric to display on the chart.', 'wp-statistics'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mini-chart-date_range"><?php esc_html_e('Chart Date Range', 'wp-statistics'); ?></label>
            </th>

            <td>
                <select name="wps_addon_settings[mini_chart][date_range]" id="mini-chart-date_range">
                    <option value="7" <?php selected(WP_STATISTICS\Option::getByAddon('date_range', 'mini_chart', '14'), '7'); ?>><?php esc_html_e('7 days', 'wp-statistics'); ?></option>
                    <option value="14" <?php selected(WP_STATISTICS\Option::getByAddon('date_range', 'mini_chart', '14'), '14'); ?>><?php esc_html_e('14 days', 'wp-statistics'); ?></option>
                    <option value="30" <?php selected(WP_STATISTICS\Option::getByAddon('date_range', 'mini_chart', '14'), '30'); ?>><?php esc_html_e('30 days', 'wp-statistics'); ?></option>
                    <option value="90" <?php selected(WP_STATISTICS\Option::getByAddon('date_range', 'mini_chart', '14'), '90'); ?>><?php esc_html_e('90 days', 'wp-statistics'); ?></option>
                    <option value="180" <?php selected(WP_STATISTICS\Option::getByAddon('date_range', 'mini_chart', '14'), '180'); ?>><?php esc_html_e('180 days', 'wp-statistics'); ?></option>
                </select>
                <p class="description">
                    <?php _e('Select the date range for displaying the chart data.', 'wp-statistics'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mini-chart-count_display"><?php esc_html_e('Count Display', 'wp-statistics'); ?></label>
            </th>

            <td>
                <select name="wps_addon_settings[mini_chart][count_display]" id="mini-chart-count_display">
                    <option value="disabled" <?php selected(WP_STATISTICS\Option::getByAddon('count_display', 'mini_chart', 'total'), 'disabled'); ?>><?php esc_html_e('Do Not Show Count', 'wp-statistics'); ?></option>
                    <option value="date_range" <?php selected(WP_STATISTICS\Option::getByAddon('count_display', 'mini_chart', 'total'), 'date_range'); ?>><?php esc_html_e('Show Count for Selected Date Range', 'wp-statistics'); ?></option>
                    <option value="total" <?php selected(WP_STATISTICS\Option::getByAddon('count_display', 'mini_chart', 'total'), 'total'); ?>><?php esc_html_e('Show Total Count', 'wp-statistics'); ?></option>
                </select>
                <p class="description">
                    <?php _e('Choose how to display the count under the chart.', 'wp-statistics'); ?>
                </p>
            </td>
        </tr>

        </tbody>
    </table>
</div>

<div class="postbox">
    <table class="form-table <?php echo !$isMiniChartActive ? 'form-table--preview' : '' ?>">
        <tbody>
        <tr>
            <th scope="row" colspan="2"><h3><?php esc_html_e('Chart Appearance', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr>
            <th scope="row">
                <label for="mini-chart-chart_color"><?php esc_html_e('Primary Color', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input type="text" class="regular-text code js-color-picker" id="mini-chart-chart_color" name="wps_addon_settings[mini_chart][chart_color]" value="<?php echo esc_attr(WP_STATISTICS\Option::getByAddon('chart_color', 'mini_chart', '#7362BF')); ?>" style="min-width: 50px"/>
                <p class="description"><?php esc_html_e('Select a color for your chart’s main elements to match your website’s theme.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<?php
if ($isMiniChartActive) {
    submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='mini-chart-settings'"));
}
?>

<script>
    jQuery(document).ready(function ($) {
        // Ensure the color picker is available and initialize it
        if ($.fn.wpColorPicker) {
            $('.js-color-picker').wpColorPicker();
        } else {
            console.log('wpColorPicker function is not available.');
        }
    });
</script>