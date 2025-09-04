<?php

use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHelper;
use WP_Statistics\Service\Admin\LicenseManagement\Plugin\PluginHandler;

$isLicenseValid        = LicenseHelper::isPluginLicenseValid('wp-statistics-customization');
$isCustomizationActive = WP_STATISTICS\Helper::isAddOnActive('customization');
global $wp_version;

$disableMenuArray = [
    'visitor_insights'   => __('Visitor Insight', 'wp-statistics'),
    'pages_insight'      => __('Page Insight', 'wp-statistics'),
    'referrals'          => __('Referrals', 'wp-statistics'),
    'content_analytics'  => __('Content Analytics', 'wp-statistics'),
    'author_analytics'   => __('Author Analytics', 'wp-statistics'),
    'category_analytics' => __('Category Analytics', 'wp-statistics'),
    'geographic'         => __('Geographic', 'wp-statistics'),
    'devices'            => __('Devices', 'wp-statistics'),
    'link_tracker'       => __('Link Tracker', 'wp-statistics'),
    'download_tracker'   => __('Download Tracker', 'wp-statistics'),
    'plugins'            => __('Add-ons', 'wp-statistics'),
    'privacy_audit'      => __('Privacy Audit', 'wp-statistics'),
    'optimize'           => __('Optimization', 'wp-statistics'),
    'exclusions'         => __('Exclusions', 'wp-statistics'),
];
if (empty(Option::get('useronline'))) {
    unset($disableMenuArray['online']);
}
if (!Helper::isAddOnActive('data-plus') || Option::getByAddon('link_tracker', 'data_plus', '1') !== '1') {
    unset($disableMenuArray['link_tracker']);
}
if (!Helper::isAddOnActive('data-plus') || Option::getByAddon('download_tracker', 'data_plus', '1') !== '1') {
    unset($disableMenuArray['download_tracker']);
}
if (empty(Option::get('privacy_audit'))) {
    unset($disableMenuArray['privacy_audit']);
}
if (empty(Option::get('record_exclusions'))) {
    unset($disableMenuArray['exclusions']);
}

$disabledMenuItems = WP_STATISTICS\Option::getByAddon('disable_menus', 'customization', []);
$pluginHandler     = new PluginHandler();
?>

    <h2 class="wps-settings-box__title"><span><?php esc_html_e('Customization', 'wp-statistics'); ?></span></h2>

<?php
if (!$isCustomizationActive) echo Admin_Template::get_template('layout/partials/addon-premium-feature',
    ['addon_slug'         => esc_url(WP_STATISTICS_SITE_URL . '/add-ons/wp-statistics-customization/?utm_source=wp-statistics&utm_medium=link&utm_campaign=customization'),
     'addon_title'        => __('Customization Add-on', 'wp-statistics'),
     'addon_modal_target' => 'wp-statistics-customization',
     'addon_description'  => __('The settings on this page are part of the Customization add-on, which allows you to customize menus and make WP Statistics white-label.', 'wp-statistics'),
     'addon_features'     => [
         __('Customize menus according to your preferences.', 'wp-statistics'),
         __('Make WP Statistics white-label.', 'wp-statistics'),
     ],
     'addon_info'         => __('Enjoy a simplified, customized experience with the Customization add-on.', 'wp-statistics'),
    ], true);

// @todo, render the notice with \WP_Statistics\Service\Admin\NoticeHandler\Notice::renderNotice(); in future.
if ($isCustomizationActive && !$isLicenseValid) {
    View::load("components/lock-sections/notice-inactive-license-addon");
}
?>
    <div class="postbox">
        <table class="form-table <?php echo !$isCustomizationActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr class="wps-settings-box_head">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Manage Admin Menus', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr data-id="disable_menus_tr">
                <th scope="row">
                    <label for="wps_addon_settings[customization][disable_menus]"><?php esc_html_e('Disable Menus', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <select name="wps_addon_settings[customization][disable_menus][]" id="wps_addon_settings[customization][disable_menus]" multiple>
                        <?php foreach ($disableMenuArray as $key => $title) { ?>
                            <option value="<?php echo esc_attr($key) ?>" <?php echo in_array($key, $disabledMenuItems ? $disabledMenuItems : []) ? 'selected' : '' ?>><?php echo esc_html($title) ?></option>
                        <?php } ?>
                    </select>
                    <p class="description"><?php esc_html_e('Select the menus you want to hide from the WordPress sidebar. To deselect a menu, hold Ctrl and click on it.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            </tbody>
        </table>
    </div>

    <div class="postbox">
        <table class="form-table <?php echo !$isCustomizationActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr class="wps-settings-box_head">
                <th scope="row" colspan="2"><h3><?php esc_html_e('White label and Header Customization', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr data-id="white_label_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('White label', 'wp-statistics'); ?></span>
                </th>

                <td>
                    <input id="wps_addon_settings[customization][wps_white_label]" type="checkbox" value="1" name="wps_addon_settings[customization][wps_white_label]" <?php checked(WP_STATISTICS\Option::getByAddon('wps_white_label', 'customization')) ?>>
                    <label for="wps_addon_settings[customization][wps_white_label]"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php _e('White label WP Statistics report pages. Remove branding and promotional elements. For a detailed list of changes, refer to the <a href="https://wp-statistics.com/resources/whitelabeling-wp-statistics/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">White label Documentation</a>.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr data-id="change_the_header_logo_tr">
                <th scope="row">
                    <label for="wps_addon_settings[customization][wps_modify_banner]"><?php esc_html_e('Change the Header Logo', 'wp-statistics'); ?></label>
                </th>

                <?php
                $custom_header_logo = esc_attr(stripslashes(WP_STATISTICS\Option::getByAddon('wps_modify_banner', 'customization')));
                $default_logo_url   = WP_STATISTICS_URL . 'assets/images/logo-statistics-header-blue.png';
                $header_logo_url    = !empty($custom_header_logo) ? $custom_header_logo : $default_logo_url;
                $display_clear      = !empty($custom_header_logo) ? "" : "display: none;";

                wp_enqueue_media();
                ?>
                <script>
                    var wps_ar_vars = {
                        'default_avatar_url': "<?php echo esc_url($default_logo_url); ?>"
                    }
                </script>
                <td>
                    <div class='wps-img-preview-wrapper'>
                        <img style="max-width: 300px; max-height: 200px;" id='wps-upload-image-preview' src='<?php echo esc_attr($header_logo_url) ?>' alt="Header Logo">
                        <input type="button" class="wps_img_settings_clear_upload_button button" style="<?php echo esc_attr($display_clear); ?> margin: 0 5px;" value="<?php esc_html_e('X', 'wp-statistics-advanced-reporting') ?>"/>
                    </div>
                    <div class="wps-input-group wps-input-group__action">
                        <input id="wps_addon_settings[customization][wps_modify_banner]" name="wps_addon_settings[customization][wps_modify_banner]" type="text" class="regular-text wps-input-group__field wps-input-group__field--small" value="<?php echo $custom_header_logo; ?>"/>
                        <input type="button" class="wps_img_settings_upload_button button wps-input-group__label" value="<?php esc_html_e('Upload File', 'wp-statistics-advanced-reporting') ?>" style="margin: 0; "/>
                    </div>
                    <p class="description"><?php esc_html_e('Customize the header logo to match your branding by uploading your own logo.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="postbox">
        <table id="wps-export-form" class="wps-export form-table wps-export__table <?php echo !$isCustomizationActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr class="wps-settings-box_head">
                <th scope="row" colspan="2">
                    <h3><?php esc_html_e('Import & Export Settings', 'wp-statistics'); ?></h3>
                </th>
            </tr>

            <tr>
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Export Settings', 'wp-statistics'); ?></span>
                </th>

                <td>
                    <p class="wps-export__item">
                        <input
                            id="wps-addon-wp-statistics"
                            name="addons[]"
                            class="wps-export__checkbox"
                            type="checkbox"
                            value="wp-statistics"
                            checked
                        >
                        <label for="wps-addon-wp-statistics" class="wps-export__label">
                            <?php echo esc_html__('WP Statistics (core settings)'); ?>
                        </label>
                    </p>
                    <?php foreach (PluginHelper::$plugins as $plugin => $title):
                        $isPluginActive = $pluginHandler->isPluginActive($plugin); ?>
                        <p class="wps-export__item <?php echo !$isPluginActive ? esc_attr('wps-export__item--disabled') : ''; ?>">
                            <input
                                id="wps-addon-<?php echo esc_attr($plugin); ?>"
                                name="addons[]"
                                class="wps-export__checkbox"
                                type="checkbox"
                                value="<?php echo esc_attr($plugin); ?>"
                                <?php echo $isPluginActive ? 'checked' : 'disabled'; ?>
                            >
                            <label for="wps-addon-<?php echo esc_attr($plugin); ?>" class="wps-export__label">
                                <?php echo esc_html($title); ?>
                            </label>
                        </p>
                    <?php endforeach; ?>

                    <p class="description"><?php esc_html_e('Choose any WP Statistics add‑ons whose settings you want in the file (e.g. Data Plus, Advanced Reporting, Real‑Time Stats). Core plugin settings are always included.', 'wp-statistics'); ?></p>
                    <br>
                    <button type="button" class="wps-button wps-button--default" id="wps-btn-export-settings">
                        <?php esc_html_e('Download export file', 'wp-statistics'); ?>
                    </button>
                    <p class="description">
                        <?php _e('The file is saved in JSON format and contains both core settings and the add‑ons you tick above. <a href="https://wp-statistics.com/resources/import-export-settings/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">Learn more</a>.', 'wp-statistics'); ?>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>

        <table id="wps-import-form" class="wps-import form-table wps-import__table <?php echo !$isCustomizationActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr id="wps-import-form-row">
                <th scope="row">
                    <label for="wps-input-import-file"><?php esc_html_e('Import Settings', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input
                        type="file"
                        accept=".json,.txt"
                        id="wps-input-import-file"
                        name="import_file"
                        class="wps-import__file"
                    >
                    <p class="description"><?php esc_html_e('Select a JSON file exported from WP Statistics.', 'wp-statistics'); ?></p>
                    <br>

                    <input
                        id="wps-input-import-images"
                        type="checkbox"
                        name="import_images"
                        value="1"
                        class="wps-import__checkbox"
                    >
                    <label for="wps-input-import-images"><?php esc_html_e('Download and import image files', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('If the exported settings reference custom images, fetch them and add them to this site’s Media Library.', 'wp-statistics'); ?></p>
                    <br>

                    <button type="button" class="wps-button wps-button--default" id="wps-btn-import-settings">
                        <?php esc_html_e('Start import', 'wp-statistics'); ?>
                    </button>

                    <p class="description">
                        <?php _e('Need a safety net? Use <b>Download export file</b> above to back up your current settings first. You can always restore defaults later under <b>Settings › Advanced Options › Reset Options</b>. <a href="https://wp-statistics.com/resources/import-export-settings/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">Learn more</a>.', 'wp-statistics'); ?>
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="postbox">
        <table class="form-table <?php echo !$isCustomizationActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr class="wps-settings-box_head">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Overview Widget Customization', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr data-id="enable_overview_widget_tr">
                <th scope="row">
                    <label for="wps_settings[customization_show_wps_about_widget_overview]"><?php esc_html_e('Enable Overview Widget', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <select name="wps_addon_settings[customization][show_wps_about_widget_overview]" id="wps_settings[customization_show_wps_about_widget_overview]">
                        <option value="yes" <?php selected(WP_STATISTICS\Option::getByAddon('show_wps_about_widget_overview', 'customization'), 'yes'); ?>><?php esc_html_e('Yes', 'wp-statistics'); ?></option>
                        <option value="no" <?php selected(WP_STATISTICS\Option::getByAddon('show_wps_about_widget_overview', 'customization'), 'no'); ?>><?php esc_html_e('No', 'wp-statistics'); ?></option>
                    </select>
                    <p class="description"><?php esc_html_e('Activate a custom widget on the Overview page.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr class="js-wps-show_if_customization_show_wps_about_widget_overview_equal_yes" data-id="widget_title_tr">
                <th scope="row">
                    <label for="wps_addon_settings[customization][wps_about_widget_title]"><?php esc_html_e('Widget Title', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input dir="ltr" type="text" name="wps_addon_settings[customization][wps_about_widget_title]" id="wps_addon_settings[customization][wps_about_widget_title]" size="30" value="<?php echo esc_attr(WP_STATISTICS\Option::getByAddon('wps_about_widget_title', 'customization')) ?>"/>
                    <p class="description"><?php esc_html_e('Enter a title for your custom widget.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr class="js-wps-show_if_customization_show_wps_about_widget_overview_equal_yes" data-id="widget_content_tr">
                <th scope="row">
                    <?php if ($wp_version >= 3.3 && function_exists('wp_editor')): ?>
                        <label for="wps_about_widget_content"><?php esc_html_e('Widget Content', 'wp-statistics'); ?></label>
                    <?php else : ?>
                        <label for="wps_addon_settings[customization][wps_about_widget_content]"><?php esc_html_e('Widget Content', 'wp-statistics'); ?></label>
                    <?php endif ?>
                </th>

                <td>
                    <?php if ($wp_version >= 3.3 && function_exists('wp_editor')) { ?>
                        <?php wp_editor(stripslashes(WP_STATISTICS\Option::getByAddon('wps_about_widget_content', 'customization')), 'wps_about_widget_content', array('textarea_name' => 'wps_addon_settings[customization][wps_about_widget_content]', 'editor_height' => 400)); ?>
                    <?php } else { ?>
                        <textarea class="large-text" rows="10" id="wps_addon_settings[customization][wps_about_widget_content]" name="wps_addon_settings[customization][wps_about_widget_content]"><?php echo esc_textarea(stripslashes(WP_STATISTICS\Option::getByAddon('wps_about_widget_content', 'customization'))) ?></textarea>
                    <?php } ?>
                    <p class="description"><?php esc_html_e('Craft the content for your widget; text, images, and HTML are supported.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            </tbody>
        </table>
    </div>

<?php
if ($isCustomizationActive) {
    submit_button(__('Update', 'wp-statistics'), 'wps-button wps-button--primary', 'submit', '', array('id' => 'customization_submit', 'OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='customization-settings'"));
}
?>