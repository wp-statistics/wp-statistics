<?php

use WP_STATISTICS\Admin_Template;

$isAdvancedReportingActive = WP_STATISTICS\Helper::isAddOnActive('advanced-reporting');
global $wp_version;
?>
<?php
if (!$isAdvancedReportingActive) echo Admin_Template::get_template('layout/partials/addon-premium-feature',
    ['addon_slug'           => esc_url(WP_STATISTICS_SITE_URL . '/product/wp-statistics-advanced-reporting/?utm_source=wp-statistics&utm_medium=link&utm_campaign=plugin-settings'),
     'addon_title'          => 'Advanced Reporting Add-On',
     'addon_description'    => 'The settings on this page are part of the Advanced Reporting add-on, which allows you to stay up-to-date on your website\'s performance by receiving graphical representations of your website\'s statistics directly in your inbox.',
     'addon_features'       => [
         'Receive graphical statistics charts via email.',
         'Schedule reports to be sent to any inbox of your choice.',
         'Monitor your website\'s traffic and activity with no hassle.',
     ],
     'addon_info'           => 'Keep a close eye on your website\'s performance with the Advanced Reporting add-on.',
    ], true);
?>

    <div class="postbox">
        <table class="form-table <?php echo !$isAdvancedReportingActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Report Scheduling', 'wp-statistics'); ?></h3></th>
            </tr>


            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][report_time_frame_type]"><?php esc_html_e('Choose Your Report Timing', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <select name="wps_addon_settings[advanced_reporting][report_time_frame_type]" id="wps_addon_settings[advanced_reporting][report_time_frame_type]">
                        <option value="specific_time" <?php selected(WP_STATISTICS\Option::getByAddon('report_time_frame_type', 'advanced_reporting'), 'specific_time'); ?>><?php esc_html_e('From a specific time', 'wp-statistics'); ?></option>
                        <option value="time_range" <?php selected(WP_STATISTICS\Option::getByAddon('report_time_frame_type', 'advanced_reporting'), 'time_range'); ?>><?php esc_html_e('Time-range', 'wp-statistics'); ?></option>
                    </select>
                    <p class="description"><?php esc_html_e('Select how often you’d like to receive updates. Opt for a specific date or a recurring schedule to keep your reports timely and relevant.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_start_date]"><?php esc_html_e('Specify Starting Date', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input type="date" id="wps_addon_settings[advanced_reporting][email_start_date]" name="wps_addon_settings[advanced_reporting][email_start_date]" class="regular-text" value="<?php echo esc_attr(WP_STATISTICS\Option::getByAddon('email_start_date', 'advanced_reporting')) ?>"/>
                    <p class="description"><?php esc_html_e('Enter a date to begin the data collection for your reports. This helps in focusing on a specific timeframe or event.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_stats_time_range]"><?php esc_html_e('Select Reporting Period', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <select name="wps_addon_settings[advanced_reporting][email_stats_time_range]" id="wps_addon_settings[advanced_reporting][email_stats_time_range]">
                        <option value="none" <?php selected(WP_STATISTICS\Option::getByAddon('email_stats_time_range', 'advanced_reporting'), 'none'); ?>><?php esc_html_e('None', 'wp-statistics'); ?></option>
                        <option value="last_day" <?php selected(WP_STATISTICS\Option::getByAddon('email_stats_time_range', 'advanced_reporting'), 'last_day'); ?>><?php esc_html_e('Last Day', 'wp-statistics'); ?></option>
                        <option value="last_week" <?php selected(WP_STATISTICS\Option::getByAddon('email_stats_time_range', 'advanced_reporting'), 'last_week'); ?>><?php esc_html_e('Last Week', 'wp-statistics'); ?></option>
                        <option value="last_month" <?php selected(WP_STATISTICS\Option::getByAddon('email_stats_time_range', 'advanced_reporting'), 'last_month'); ?>><?php esc_html_e('Last Month', 'wp-statistics'); ?></option>
                    </select>
                    <p class="description"><?php esc_html_e('Enter a date to begin the data collection for your reports. This helps in focusing on a specific timeframe or event.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="postbox">
        <table class="form-table <?php echo !$isAdvancedReportingActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Email Notifications', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_summary_stats]"><?php esc_html_e('Summary Statistics', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="wps_addon_settings[advanced_reporting][email_summary_stats]" name="wps_addon_settings[advanced_reporting][email_summary_stats]" type="checkbox" value="1" <?php checked(WP_STATISTICS\Option::getByAddon('email_summary_stats', 'advanced_reporting')) ?>>
                    <label for="wps_addon_settings[advanced_reporting][email_summary_stats]"><?php esc_html_e('Active', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Activate to receive a neatly summarized report of your website\'s key performance indicators.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_search_engine]"><?php esc_html_e('Search Engine Referrals', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="wps_addon_settings[advanced_reporting][email_search_engine]" name="wps_addon_settings[advanced_reporting][email_search_engine]" type="checkbox" value="1" <?php checked(WP_STATISTICS\Option::getByAddon('email_search_engine', 'advanced_reporting')) ?>>
                    <label for="wps_addon_settings[advanced_reporting][email_search_engine]"><?php esc_html_e('Active', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Toggle on to get insights on which search engines are driving traffic to your site.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_top_hits_visitors]"><?php esc_html_e('Visitor Insights', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="wps_addon_settings[advanced_reporting][email_top_hits_visitors]" name="wps_addon_settings[advanced_reporting][email_top_hits_visitors]" type="checkbox" value="1" <?php checked(WP_STATISTICS\Option::getByAddon('email_top_hits_visitors', 'advanced_reporting')) ?>>
                    <label for="wps_addon_settings[advanced_reporting][email_top_hits_visitors]"><?php esc_html_e('Active', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Choose to receive detailed charts on visitor counts and behavior patterns.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_top_hits_visits]"><?php esc_html_e('Views Insights', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="wps_addon_settings[advanced_reporting][email_top_hits_visits]" name="wps_addon_settings[advanced_reporting][email_top_hits_visits]" type="checkbox" value="1" <?php checked(WP_STATISTICS\Option::getByAddon('email_top_hits_visits', 'advanced_reporting')) ?>>
                    <label for="wps_addon_settings[advanced_reporting][email_top_hits_visits]"><?php esc_html_e('Active', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Choose to receive detailed charts on views counts and behavior patterns.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="postbox">
        <table class="form-table <?php echo !$isAdvancedReportingActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Report Components', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_top_referring]"><?php esc_html_e('Top Referring Websites', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="wps_addon_settings[advanced_reporting][email_top_referring]" name="wps_addon_settings[advanced_reporting][email_top_referring]" type="checkbox" value="1" <?php checked(WP_STATISTICS\Option::getByAddon('email_top_referring', 'advanced_reporting')) ?>>
                    <label for="wps_addon_settings[advanced_reporting][email_top_referring]"><?php esc_html_e('Active', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Stay informed on which external sites are most frequently linking to your content.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_top_search_engines]"><?php esc_html_e('Search Engine Chart', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="wps_addon_settings[advanced_reporting][email_top_search_engines]" name="wps_addon_settings[advanced_reporting][email_top_search_engines]" type="checkbox" value="1" <?php checked(WP_STATISTICS\Option::getByAddon('email_top_search_engines', 'advanced_reporting')) ?>>
                    <label for="wps_addon_settings[advanced_reporting][email_top_search_engines]"><?php esc_html_e('Active', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Get visual analytics on search engine referrals for a comprehensive view of traffic sources.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_top_ten_pages]"><?php esc_html_e('Top Pages', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="wps_addon_settings[advanced_reporting][email_top_ten_pages]" name="wps_addon_settings[advanced_reporting][email_top_ten_pages]" type="checkbox" value="1" <?php checked(WP_STATISTICS\Option::getByAddon('email_top_ten_pages', 'advanced_reporting')) ?>>
                    <label for="wps_addon_settings[advanced_reporting][email_top_ten_pages]"><?php esc_html_e('Active', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Find out which pages on your website are attracting the most attention.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_top_ten_countries]"><?php esc_html_e('Top Countries', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="wps_addon_settings[advanced_reporting][email_top_ten_countries]" name="wps_addon_settings[advanced_reporting][email_top_ten_countries]" type="checkbox" value="1" <?php checked(WP_STATISTICS\Option::getByAddon('email_top_ten_countries', 'advanced_reporting')) ?>>
                    <label for="wps_addon_settings[advanced_reporting][email_top_ten_countries]"><?php esc_html_e('Active', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Discover the geographical distribution of your audience.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="postbox">
        <table class="form-table <?php echo !$isAdvancedReportingActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Customization Options', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_top_ten_number]"><?php esc_html_e('Number of Top Pages', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input type="number" id="wps_addon_settings[advanced_reporting][email_top_ten_number]" name="wps_addon_settings[advanced_reporting][email_top_ten_number]" class="regular-text" value="<?php echo esc_attr(WP_STATISTICS\Option::getByAddon('email_top_ten_number', 'advanced_reporting', 10)) ?>"/>
                    <p class="description"><?php esc_html_e('Decide how many of your top pages to feature in your reports.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_top_pages_exclusion]"><?php esc_html_e('Page Exclusion', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="wps_addon_settings[advanced_reporting][email_top_pages_exclusion]" name="wps_addon_settings[advanced_reporting][email_top_pages_exclusion]" type="checkbox" value="1" <?php checked(WP_STATISTICS\Option::getByAddon('email_top_pages_exclusion', 'advanced_reporting')) ?>>
                    <label for="wps_addon_settings[advanced_reporting][email_top_pages_exclusion]"><?php esc_html_e('Active', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Select specific pages to exclude from reporting for a more focused analysis.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][excluded_top_pages]"><?php esc_html_e('Excluded Pages', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <select name="wps_addon_settings[advanced_reporting][excluded_top_pages][]" id="wps_addon_settings[advanced_reporting][excluded_top_pages]" multiple>
                        <?php foreach (get_pages(['hierarchical' => true]) as $page) { ?>
                            <?php $selected = in_array($page->ID, WP_STATISTICS\Option::getByAddon('excluded_top_pages', 'advanced_reporting', [])) ? ' selected="selected" ' : ''; ?>
                            <option value="<?php echo esc_attr($page->ID) ?>>" <?php echo $selected ?>><?php echo $page->post_title ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="postbox">
        <table class="form-table <?php echo !$isAdvancedReportingActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Visual Enhancements', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_chart_top_browsers]"><?php esc_html_e('Browser Chart', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="wps_addon_settings[advanced_reporting][email_chart_top_browsers]" name="wps_addon_settings[advanced_reporting][email_chart_top_browsers]" type="checkbox" value="1" <?php checked(WP_STATISTICS\Option::getByAddon('email_chart_top_browsers', 'advanced_reporting')) ?>>
                    <label for="wps_addon_settings[advanced_reporting][email_chart_top_browsers]"><?php esc_html_e('Active', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Select the preferred chart type to visualize browser statistics in your reports.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][chart_style]"><?php esc_html_e('Chart Style', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <select name="wps_addon_settings[advanced_reporting][chart_style]" id="wps_addon_settings[advanced_reporting][chart_style]">
                        <option value="2d" <?php selected(WP_STATISTICS\Option::getByAddon('chart_style', 'advanced_reporting'), '2d'); ?>><?php esc_html_e('2D', 'wp-statistics'); ?></option>
                        <option value="3d" <?php selected(WP_STATISTICS\Option::getByAddon('chart_style', 'advanced_reporting'), '3d'); ?>><?php esc_html_e('3D', 'wp-statistics'); ?></option>
                    </select>
                    <p class="description"><?php esc_html_e('Customize the visual styling of your charts.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="postbox">
        <table class="form-table <?php echo !$isAdvancedReportingActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Additional Features', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_more_info_button]"><?php esc_html_e('More Information Button', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="wps_addon_settings[advanced_reporting][email_more_info_button]" name="wps_addon_settings[advanced_reporting][email_more_info_button]" type="checkbox" value="1" <?php checked(WP_STATISTICS\Option::getByAddon('email_more_info_button', 'advanced_reporting')) ?>>
                    <label for="wps_addon_settings[advanced_reporting][email_more_info_button]"><?php esc_html_e('Active', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Add a convenient button to your report that links back to your full statistics dashboard.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_more_info_button_href]"><?php esc_html_e('Custom URL', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input type="text" name="wps_addon_settings[advanced_reporting][email_more_info_button_href]" id="wps_addon_settings[advanced_reporting][email_more_info_button_href]" class="regular-text" value="<?php echo esc_attr(WP_STATISTICS\Option::getByAddon('email_more_info_button_href', 'advanced_reporting')) ?>"/>
                    <p class="description"><?php esc_html_e('Personalize the destination of the ‘More Information’ button to direct recipients to a specific page on your website.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="postbox">
        <table class="form-table <?php echo !$isAdvancedReportingActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Branding Your Reports', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][custom_header_logo]"><?php esc_html_e('Report Logo Upload', 'wp-statistics'); ?></label>
                </th>

                <?php
                $custom_header_logo = WP_STATISTICS\Option::getByAddon('custom_header_logo', 'advanced_reporting');
                $default_logo_url   = WP_STATISTICS_URL . 'assets/images/logo-statistics-header-blue.png';
                $header_logo_url    = !empty($custom_header_logo) ? $custom_header_logo : $default_logo_url;
                $display_clear      = !empty($custom_header_logo) ? "" : "display: none;";

                wp_enqueue_media();
                ?>
                <script>
                    var wps_ar_vars = {
                        'default_avatar_url': "<?php echo $default_logo_url ?>"
                    }
                </script>
                <td>
                    <div class='wps-ar-preview-wrapper'><img id='wps-ar-image-preview' src='<?php echo esc_attr($header_logo_url) ?>' alt="Header Logo"></div>
                    <input id="wps_addon_settings[advanced_reporting][custom_header_logo]" name="wps_addon_settings[advanced_reporting][custom_header_logo]" type="text" class="regular-text" value="<?php echo esc_attr(WP_STATISTICS\Option::getByAddon('custom_header_logo', 'advanced_reporting')) ?>"/>
                    <span>&nbsp;<input type="button" class="wps_ar_settings_upload_button button" value="<?php esc_html_e('Upload File', 'wp-statistics-advanced-reporting') ?>" style="margin: 0; padding-top: 13px; padding-bottom: 13px;"/>&nbsp;<input type="button" class="wps_ar_settings_clear_upload_button button" style="<?php echo $display_clear ?> margin: 0; padding-top: 13px; padding-bottom: 13px;" value="<?php esc_html_e('X', 'wp-statistics-advanced-reporting') ?>"/></span>

                    <p class="description"><?php esc_html_e('Upload your own logo to replace the default in report headers, establishing your brand\'s presence in all reports.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][custom_header_logo_url]"><?php esc_html_e('Logo Link URL', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input type="text" name="wps_addon_settings[advanced_reporting][custom_header_logo_url]" id="wps_addon_settings[advanced_reporting][custom_header_logo_url]" class="regular-text" value="<?php echo esc_attr(WP_STATISTICS\Option::getByAddon('custom_header_logo_url', 'advanced_reporting')) ?>"/>
                    <p class="description"><?php esc_html_e('Provide the URL that the header logo should link to, such as your company homepage or a custom landing page.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_content_header]"><?php esc_html_e('Email Header Customization', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <?php if ($wp_version >= 3.3 && function_exists('wp_editor')) { ?>
                        <?php wp_editor(stripslashes(WP_STATISTICS\Option::getByAddon('email_content_header', 'advanced_reporting')), 'email_content_header', array('textarea_name' => 'wps_addon_settings[advanced_reporting][email_content_header]', 'editor_height' => 400)); ?>
                    <?php } else { ?>
                        <textarea class="large-text" rows="10" id="wps_addon_settings[advanced_reporting][email_content_header]" name="wps_addon_settings[advanced_reporting][email_content_header]"><?php echo esc_textarea(stripslashes(WP_STATISTICS\Option::getByAddon('email_content_header', 'advanced_reporting'))) ?></textarea>
                    <?php } ?>
                    <p class="description"><?php esc_html_e('Add a custom header to your email reports to introduce your brand or report summary.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_content_footer]"><?php esc_html_e('Email Footer Customization', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <?php if ($wp_version >= 3.3 && function_exists('wp_editor')) { ?>
                        <?php wp_editor(stripslashes(WP_STATISTICS\Option::getByAddon('email_content_footer', 'advanced_reporting')), 'email_content_footer', array('textarea_name' => 'wps_addon_settings[advanced_reporting][email_content_footer]', 'editor_height' => 400)); ?>
                    <?php } else { ?>
                        <textarea class="large-text" rows="10" id="wps_addon_settings[advanced_reporting][email_content_footer]" name="wps_addon_settings[advanced_reporting][email_content_footer]"><?php echo esc_textarea(stripslashes(WP_STATISTICS\Option::getByAddon('email_content_footer', 'advanced_reporting'))) ?></textarea>
                    <?php } ?>
                    <p class="description"><?php esc_html_e('Insert a custom footer in your email reports for additional notes, disclaimers, or contact information.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="postbox">
        <table class="form-table <?php echo !$isAdvancedReportingActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Control and Compliance', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_disable_copyright]"><?php esc_html_e('Copyright Notice', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="wps_addon_settings[advanced_reporting][email_disable_copyright]" name="wps_addon_settings[advanced_reporting][email_disable_copyright]" type="checkbox" value="1" <?php checked(WP_STATISTICS\Option::getByAddon('email_disable_copyright', 'advanced_reporting')) ?>>
                    <label for="wps_addon_settings[advanced_reporting][email_disable_copyright]"><?php esc_html_e('Active', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Opt to show or hide the plugin’s copyright notice, depending on your preference.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="postbox">
        <table class="form-table <?php echo !$isAdvancedReportingActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('PDF Reports', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][pdf_report_status]"><?php esc_html_e('Email PDF Report Attachments', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="wps_addon_settings[advanced_reporting][pdf_report_status]" name="wps_addon_settings[advanced_reporting][pdf_report_status]" type="checkbox" value="1" <?php checked(WP_STATISTICS\Option::getByAddon('pdf_report_status', 'advanced_reporting')) ?>>
                    <label for="wps_addon_settings[advanced_reporting][pdf_report_status]"><?php esc_html_e('Active', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Select this option to automatically attach a PDF version of your report to your email updates, providing a convenient and printable document for recipients.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="postbox">
        <table class="form-table <?php echo !$isAdvancedReportingActive ? 'form-table--preview' : '' ?>">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Preview and Send', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="wps_addon_settings[advanced_reporting][email_preview_content]"><?php esc_html_e('Test Your Report', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="wps_addon_settings[advanced_reporting][email_preview_content]" name="wps_addon_settings[advanced_reporting][email_preview_content]" type="text" class="regular-text" value="<?php echo esc_attr(WP_STATISTICS\Option::getByAddon('email_preview_content', 'advanced_reporting')) ?>"/> &nbsp; <input type="submit" name="submit-preview" id="submit-preview" class="button" value="Send" style="margin: 0; padding-top: 13px; padding-bottom: 13px;" onclick="var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='advanced-reporting-settings'"/>
                    <p class="description"><?php esc_html_e('Enter an email to send a preview, ensuring your report looks just right before it goes out to your audience.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

<?php
if ($isAdvancedReportingActive) {
    submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='advanced-reporting-settings'"));
}
?>