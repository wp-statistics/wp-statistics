<?php

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;

$isLicenseValid  = LicenseHelper::isPluginLicenseValid('wp-statistics-widgets');
$isWidgetsActive = WP_STATISTICS\Helper::isAddOnActive('widgets');
?>
    <h2 class="wps-settings-box__title"><span><?php esc_html_e('Advanced Widgets', 'wp-statistics'); ?></span></h2>

<?php
if (!$isWidgetsActive) echo Admin_Template::get_template('layout/partials/addon-premium-feature',
    ['addon_slug'         => esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp-statistics-widgets/?utm_source=wp-statistics&utm_medium=link&utm_campaign=widgets'),
     'addon_title'        => __('Advanced Widgets Add-on', 'wp-statistics'),
     'addon_modal_target' => 'wp-statistics-widgets',
     'addon_description'  => __('The settings on this page are part of the Advanced Widgets add-on, allowing you to display a range of data widgets on your website.', 'wp-statistics'),
     'addon_features'     => [
         __('Display data widgets using Gutenberg blocks or theme widgets.', 'wp-statistics'),
         __('Easily present vital website statistics.', 'wp-statistics'),
         __('Enhance your audience\'s user experience.', 'wp-statistics'),
     ],
     'addon_info'         => __('With Advanced Widgets, you can easily display your website\'s important statistics', 'wp-statistics'),
    ], true);

if ($isWidgetsActive && !$isLicenseValid) {
    View::load("components/lock-sections/notice-inactive-license-addon");
}
?>

    <div class="postbox">
        <table class="form-table <?php echo !$isWidgetsActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr class="wps-settings-box_head">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Widget Cache Duration', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr data-id="refresh_every_tr">
                <th scope="row">
                    <label for="wps_addon_settings[widgets][cache_life]"><?php esc_html_e('Refresh Every', 'wp-statistics'); ?></label>
                </th>

                <td>

                    <div class="wps-input-group wps-input-group__small">
                         <select name="wps_addon_settings[widgets][cache_life]" id="wps_addon_settings[widgets][cache_life]" style="padding: 12px 24px 12px 14px !important;" class="wps-input-group__field wps-input-group__field--small code">
                            <?php foreach (array_combine(range(1, 24), range(1, 24)) as $key => $value) { ?>
                                <option value="<?php esc_attr_e($value); ?>" <?php selected(WP_STATISTICS\Option::getByAddon('cache_life', 'widgets'), $value); ?>><?php esc_html_e($value); ?></option>
                            <?php } ?>
                         </select>
                         <span class="wps-input-group__label wps-input-group__label-side"><?php esc_html_e('hour(s)', 'wp-statistics'); ?></span>
                    </div>

                     <p class="description"><?php esc_html_e('Set the time interval for refreshing the statistics displayed in widgets. After the chosen period, fresh data will be fetched and displayed.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            </tbody>
        </table>
    </div>

    <div class="postbox">
        <table class="form-table <?php echo !$isWidgetsActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr class="wps-settings-box_head">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Widget Design Customization', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr data-id="use_default_widget_styling_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Use Default Widget Styling', 'wp-statistics'); ?></span>
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
    submit_button(__('Update', 'wp-statistics'), 'wps-button wps-button--primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='widgets-settings'"));
}
?>