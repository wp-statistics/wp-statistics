<?php
$isDataPlusActive = WP_STATISTICS\Helper::isAddOnActive('data-plus');
?>

    <div class="postbox">
        <table class="form-table <?php echo !$isDataPlusActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Event Tracking', 'wp-statistics'); ?> <a href="#" class="wps-tooltip" title="<?php esc_html_e('Enable or disable tracking features for clicks and downloads', 'wp-statistics') ?>"><i class="wps-tooltip-icon"></i></a></h3></th>
            </tr>

            <?php if (!$isDataPlusActive) : ?>
                <tr class="upgrade-notice" valign="top">
                    <th scope="row" colspan="2">
                        <p style="font-size: 1em"><?php esc_html_e('Event Tacking feature is currently restricted in your current version. Unlock premium features to gain a deeper insight into your website.', 'wp-statistics') ?></p>
                        <a target="_blank" class="button button-primary" href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=link&utm_campaign=plugin-settings'); ?>"><?php esc_html_e('Upgrade', 'wp-statistics') ?></a>
                    </th>
                </tr>
            <?php endif; ?>

            <tr valign="top">
                <th scope="row">
                    <label for="link-tracker"><?php esc_html_e('Link Tracker', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="link-tracker" type="checkbox" value="1" name="wps_link_tracker" <?php checked(WP_STATISTICS\Option::get('link_tracker')) ?>>
                    <label for="link-tracker"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('See which outside links people click on your site.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="download-tracker"><?php esc_html_e('Download Tracker', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="download-tracker" type="checkbox" value="1" name="wps_download_tracker" <?php checked(WP_STATISTICS\Option::get('download_tracker')) ?>>
                    <label for="download-tracker"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Watch which files people download and learn what’s popular.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

<?php
if ($isDataPlusActive) {
    submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='data-plus-settings'"));
}
?>