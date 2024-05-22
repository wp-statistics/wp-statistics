<?php

use WP_STATISTICS\Admin_Template;

$isWidgetsActive = WP_STATISTICS\Helper::isAddOnActive('widgets');
?>

<?php
if (!$isWidgetsActive) echo Admin_Template::get_template('layout/partials/addon-premium-feature',
    ['addon_slug'           => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-widgets/?utm_source=wp-statistics&utm_medium=link&utm_campaign=plugin-settings'),
     'addon_title'          => 'Advanced Widgets Add-On',
     'addon_description'    => 'The settings on this page are part of the Advanced Widgets add-on, allowing you to display a range of data widgets on your website.',
     'addon_features'       => [
         'Display data widgets using Gutenberg blocks or theme widgets.',
         'Easily present vital website statistics.',
         'Enhance your audience\'s user experience.',
     ],
     'addon_info'           => 'With Advanced Widgets, you can easily display your website\'s important statistics',
    ], true);
?>

    <div class="postbox">
        <table class="form-table <?php echo !$isWidgetsActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Widget Cache Duration', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[widgets][cache_life]"><?php esc_html_e('Refresh Every', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <select name="wps_addon_settings[widgets][cache_life]" id="wps_addon_settings[widgets][cache_life]" style="padding: 12px 24px 12px 14px !important;">
                        <?php foreach (array_combine(range(1, 24), range(1, 24)) as $key => $value) { ?>
                            <option value="<?php esc_attr_e($value); ?>" <?php selected(WP_STATISTICS\Option::getByAddon('cache_life', 'widgets'), $value); ?>><?php esc_html_e($value); ?></option>
                        <?php } ?>
                    </select>
                    <?php esc_html_e('hour(s)', 'wp-statistics'); ?>
                    <p class="description"><?php esc_html_e('Set the time interval for refreshing the statistics displayed in widgets. After the chosen period, fresh data will be fetched and displayed.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            </tbody>
        </table>
    </div>

    <div class="postbox">
        <table class="form-table <?php echo !$isWidgetsActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Widget Design Customization', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[widgets][disable_styles]"><?php esc_html_e('Use Default Widget Styling', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="wps_addon_settings[widgets][disable_styles]" name="wps_addon_settings[widgets][disable_styles]" type="checkbox" value="1" <?php checked(WP_STATISTICS\Option::getByAddon('disable_styles', 'widgets')) ?>>
                    <label for="wps_addon_settings[widgets][disable_styles]"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Uncheck to allow theme or custom styles to determine widget appearance.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            </tbody>
        </table>
    </div>

<?php
if ($isWidgetsActive) {
    submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='widgets-settings'"));
}
?>