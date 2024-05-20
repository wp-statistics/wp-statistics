<?php

use WP_STATISTICS\Admin_Template;

$isMiniChartActive         = WP_STATISTICS\Helper::isAddOnActive('mini-chart');
$miniChartDefaultPostTypes = get_post_types(array(
    'public'   => true,
    '_builtin' => false
));

$miniChartPostTypes = array('post', 'page');
foreach ($miniChartDefaultPostTypes as $name) {
    $miniChartPostTypes[] = $name;
}

$miniChartPostTypesOptions = array();
foreach ($miniChartPostTypes as $p) {
    $miniChartPostTypesOptions[$p] = sprintf(__('Enable Mini Chart for %s', 'wp-statistics-mini-chart'), ucwords($p));
}
?>

<?php
if (!$isMiniChartActive) echo Admin_Template::get_template('layout/partials/addon-premium-feature',
    ['addon_slug'           => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-mini-chart/?utm_source=wp-statistics&utm_medium=link&utm_campaign=plugin-settings'),
     'addon_title'          => 'Mini Chart Add-On',
     'addon_description'    => 'The settings on this page are part of the Mini Chart add-on, which provides tiny charts for all your posts and pages, along with an Admin Bar for quick access to traffic data.',
     'addon_features'       => [
         'Tiny charts for posts and pages to measure performance.',
         'Admin Bar for easy access to traffic data.',
         'Customizable chart type and color.',
     ],
     'addon_info'           => 'Get clear insights into your website\'s traffic and content success with the Mini Chart add-on.',
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
                <p class="description"><?php esc_html_e('Customize the appearance of mini charts on your posts and pages for a quick glance at their performance.', 'wp-statistics'); ?></p>
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
                <label for="mini-chart-chart_type"><?php esc_html_e('Type', 'wp-statistics'); ?></label>
            </th>

            <td>
                <select name="wps_addon_settings[mini_chart][chart_type]" id="mini-chart-chart_type">
                    <option value="bar" <?php selected(WP_STATISTICS\Option::getByAddon('chart_type', 'mini_chart'), 'bar'); ?>><?php esc_html_e('Bar', 'wp-statistics'); ?></option>
                    <option value="line" <?php selected(WP_STATISTICS\Option::getByAddon('chart_type', 'mini_chart'), 'line'); ?>><?php esc_html_e('Line', 'wp-statistics'); ?></option>
                </select>
                <p class="description">
                    <?php _e('Choose a chart type that best represents your data.', 'wp-statistics'); ?>
                </p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mini-chart-chart_color"><?php esc_html_e('Primary Color', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input type="text" class="regular-text code js-color-picker" id="mini-chart-chart_color" name="wps_addon_settings[mini_chart][chart_color]" value="<?php echo esc_attr(WP_STATISTICS\Option::getByAddon('chart_color', 'mini_chart')); ?>" style="min-width: 50px"/>
                <p class="description"><?php esc_html_e('Select a color for your chart’s main elements to match your website’s theme.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="mini-chart-chart_border_color"><?php esc_html_e('Border Color', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input type="text" class="regular-text code js-color-picker" id="mini-chart-chart_border_color" name="wps_addon_settings[mini_chart][chart_border_color]" value="<?php echo esc_attr(WP_STATISTICS\Option::getByAddon('chart_border_color', 'mini_chart')); ?>" style="min-width: 50px"/>
                <p class="description"><?php esc_html_e('Pick a border color to enhance the visibility of your chart on the dashboard.', 'wp-statistics'); ?></p>
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
        //Initiate Color Picker
        $('.js-color-picker').wpColorPicker();
    });
</script>