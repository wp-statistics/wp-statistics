<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('User Role Exclusions', 'wp-statistics'); ?> <a href="#" class="wps-tooltip" title="<?php esc_html_e('Exclude specific user roles from data collection.', 'wp-statistics') ?>"><i class="wps-tooltip-icon"></i></a></h3></th>
        </tr>

        <?php
        $role_option_list = '';
        foreach (\WP_STATISTICS\User::get_role_list() as $role) {
            $store_name       = 'exclude_' . str_replace(" ", "_", strtolower($role));
            $option_name      = 'wps_' . $store_name;
            $role_option_list .= $option_name . ',';

            $translated_role_name = translate_user_role($role);
            ?>

            <tr valign="top">
                <th scope="row"><label for="<?php echo esc_attr($option_name); ?>"><?php echo esc_attr($translated_role_name); ?></label>
                </th>
                <td>
                    <input id="<?php echo esc_attr($option_name); ?>" type="checkbox" value="1" name="<?php echo esc_attr($option_name); ?>" <?php echo WP_STATISTICS\Option::get($store_name) == true ? "checked='checked'" : ''; ?>><label for="<?php echo esc_attr($option_name); ?>"><?php esc_html_e('Exclude', 'wp-statistics'); ?></label>
                    <p class="description"><?php echo sprintf(esc_html__('Exclude data collection for users with the %s role.', 'wp-statistics'), esc_attr($translated_role_name)); ?></p>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('IP Exclusions', 'wp-statistics'); ?> <a href="#" class="wps-tooltip" title="<?php esc_html_e('Specify which IP addresses or ranges should be excluded from statistics.', 'wp-statistics') ?>"><i class="wps-tooltip-icon"></i></a></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row"><label for="wps_exclude_ip"><?php esc_html_e('Excluded IP Address List', 'wp-statistics'); ?></label></th>
            <td>
                <textarea id="wps_exclude_ip" name="wps_exclude_ip" rows="5" cols="60" class="code" dir="ltr"><?php echo esc_textarea(WP_STATISTICS\Option::get('exclude_ip')); ?></textarea>
                <p class="description"><?php echo esc_html__('Specify the IP addresses you want to exclude. Enter one IP address or range per line.', 'wp-statistics'); ?></p>
                <p class="description"><?php echo esc_html__('For IPv4 addresses, both 192.168.0.0/24 and 192.168.0.0/255.255.255.0 formats are acceptable. To specify an IP address, use a subnet value of 32 or 255.255.255.255.', 'wp-statistics'); ?></p>
                <p class="description"><?php echo esc_html__('For IPv6 addresses, use the fc00::/7 format.', 'wp-statistics'); ?></p>
                <?php
                foreach (\WP_STATISTICS\IP::$private_SubNets as $ip) {
                    ?>
                    <a onclick="var wps_exclude_ip = getElementById('wps_exclude_ip'); if( wps_exclude_ip != null ) { wps_exclude_ip.value = jQuery.trim( wps_exclude_ip.value + '\n<?php echo esc_attr($ip); ?>' ); }" class="button"><?php esc_html_e('Add', 'wp-statistics'); ?><?php echo esc_attr($ip); ?></a>
                    <?php
                }
                ?>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Robot Exclusions', 'wp-statistics'); ?> <a href="#" class="wps-tooltip" title="<?php esc_html_e('Define bots and spiders to exclude from your website\'s statistics.', 'wp-statistics') ?>"><i class="wps-tooltip-icon"></i></a></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row"><label for="wps_robotlist"><?php esc_html_e('Robot List', 'wp-statistics'); ?></label></th>
            <td>
                    <textarea name="wps_robotlist" class="code textarea-input-reset" dir="ltr" rows="10" cols="60" id="wps_robotlist"><?php
                        $robotlist = WP_STATISTICS\Option::get('robotlist');
                        if ($robotlist == '') {
                            $robotlist = WP_STATISTICS\Helper::get_robots_list();
                            update_option('wps_robotlist', $robotlist);
                        }
                        echo esc_textarea($robotlist);
                        ?>
                    </textarea>
                <p class="description"><?php echo esc_html__('Enter robot agents to exclude. One agent name per line, minimum four characters.', 'wp-statistics'); ?></p>
                <a onclick="var wps_robotlist = getElementById('wps_robotlist'); wps_robotlist.value = '<?php echo esc_attr(str_replace(array("\r\n", "\n", "\r"), '\n', esc_html(\WP_STATISTICS\Helper::get_robots_list()))); ?>';" class="button"><?php esc_html_e('Reset to Default', 'wp-statistics'); ?></a>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="wps_robot_threshold"><?php esc_html_e('Robot View Threshold', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="wps_robot_threshold" type="text" size="5" name="wps_robot_threshold" value="<?php echo esc_attr(WP_STATISTICS\Option::get('robot_threshold')); ?>">
                <p class="description"><?php echo esc_html__('Set a threshold for daily robot visits. Robots exceeding this number daily will be identified as bots.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="force_robot_update"><?php esc_html_e('Force Robot List Update After Upgrades', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="force_robot_update" type="checkbox" value="1" name="wps_force_robot_update" <?php echo WP_STATISTICS\Option::get('force_robot_update') == true ? "checked='checked'" : ''; ?>><label for="force_robot_update"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php echo sprintf(esc_html__('Reset the robot list to default after WP Statistics updates. Custom entries will be lost if enabled.', 'wp-statistics'), esc_html($role)); // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction	 ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row"><label for="use_honeypot"><?php esc_html_e('Activate Honey Pot Protection', 'wp-statistics'); ?></label></th>
            <td>
                <input id="use_honeypot" type="checkbox" value="1" name="wps_use_honeypot" <?php echo WP_STATISTICS\Option::get('use_honeypot') == true ? "checked='checked'" : ''; ?>><label for="wps_use_honeypot"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php echo esc_html__('Turn on Honey Pot to detect and filter out bots. This adds a hidden trap for malicious automated scripts.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row"><label for="honeypot_postid"><?php esc_html_e('Honey Pot Trap Page', 'wp-statistics'); ?></label></th>
            <td>
                <?php wp_dropdown_pages(array('show_option_none' => esc_html__('Please select', 'wp-statistics'), 'id' => 'honeypot_postid', 'name' => 'wps_honeypot_postid', 'selected' => WP_STATISTICS\Option::get('honeypot_postid'))); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?>
                <p class="description"><?php echo esc_html__('Choose an existing Honey Pot trap page from the list or set up a new one to catch bots.', 'wp-statistics'); ?></p>
                <p><input id="wps_create_honeypot" type="checkbox" value="1" name="wps_create_honeypot"> <label for="wps_create_honeypot"><?php esc_html_e('Create a new Honey Pot page', 'wp-statistics'); ?></label></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="corrupt_browser_info"><?php esc_html_e('Identify Incomplete Browser Data as Bot Activity', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="corrupt_browser_info" type="checkbox" value="1" name="wps_corrupt_browser_info" <?php echo WP_STATISTICS\Option::get('corrupt_browser_info') == true ? "checked='checked'" : ''; ?>><label for="corrupt_browser_info"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php echo esc_html__('Visitors with missing browser details such as IP address or user agent are marked as bots.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('GeoIP Exclusions', 'wp-statistics'); ?> <a href="#" class="wps-tooltip" title="<?php esc_html_e('Filter out or specifically include visits from certain countries.', 'wp-statistics') ?>"><i class="wps-tooltip-icon"></i></a></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row"><label for="wps_excluded_countries"><?php esc_html_e('Excluded Countries', 'wp-statistics'); ?></label></th>
            <td>
                <textarea id="wps_excluded_countries" name="wps_excluded_countries" rows="5" cols="50" class="code" dir="ltr"><?php echo esc_textarea(WP_STATISTICS\Option::get('excluded_countries')); ?></textarea>
                <p class="description"><?php echo esc_html__('Enter country codes to exclude from stats. Use \'000\' for unknown countries.', 'wp-statistics') ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row"><label for="wps_included_countries"><?php esc_html_e('Included Countries', 'wp-statistics'); ?></label></th>
            <td>
                <textarea id="wps_included_countries" name="wps_included_countries" rows="5" cols="50" class="code" dir="ltr"><?php echo esc_textarea(WP_STATISTICS\Option::get('included_countries')); ?></textarea>
                <p class="description"><?php echo esc_html__('Specify country codes to include in stats. \'000\' means unknown countries.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('URL Exclusions', 'wp-statistics'); ?> <a href="#" class="wps-tooltip" title="<?php esc_html_e('Choose specific site URLs to keep out of the statistics.', 'wp-statistics') ?>"><i class="wps-tooltip-icon"></i></a></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row"><label for="wps-exclude-loginpage"><?php esc_html_e('Excluded Login Page', 'wp-statistics'); ?></label></th>
            <td>
                <input id="wps-exclude-loginpage" type="checkbox" value="1" name="wps_exclude_loginpage" <?php echo WP_STATISTICS\Option::get('exclude_loginpage') == true ? "checked='checked'" : ''; ?>><label for="wps-exclude-loginpage"><?php esc_html_e('Exclude', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Login page visits will not be included in site visit counts.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wps-exclude-feeds"><?php esc_html_e('Excluded RSS Feeds', 'wp-statistics'); ?></label></th>
            <td>
                <input id="wps-exclude-feeds" type="checkbox" value="1" name="wps_exclude_feeds" <?php echo WP_STATISTICS\Option::get('exclude_feeds') == true ? "checked='checked'" : ''; ?>><label for="wps-exclude-feeds"><?php esc_html_e('Exclude', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('RSS feeds visits will not be included in site visit counts.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wps-exclude-404s"><?php esc_html_e('Excluded 404 Pages', 'wp-statistics'); ?></label></th>
            <td>
                <input id="wps-exclude-404s" type="checkbox" value="1" name="wps_exclude_404s" <?php echo WP_STATISTICS\Option::get('exclude_404s') == true ? "checked='checked'" : ''; ?>><label for="wps-exclude-404s"><?php esc_html_e('Exclude', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('404 Page visits will not be included in site visit counts.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wps_excluded_urls"><?php esc_html_e('Excluded URLs', 'wp-statistics'); ?></label></th>
            <td>
                <textarea id="wps_excluded_urls" name="wps_excluded_urls" rows="5" cols="80" class="code" dir="ltr"><?php echo esc_textarea(WP_STATISTICS\Option::get('excluded_urls')); ?></textarea>
                <p class="description"><?php echo esc_html__('List specific URLs here that you wish to exclude from tracking. URL parameters aren\'t considered.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('URL Query Parameters', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row"><label for="wps_query_params_allow_list"><?php esc_html_e('Allowed Query Parameters', 'wp-statistics'); ?></label></th>
            <td>
                <textarea name="wps_query_params_allow_list" class="code textarea-input-reset" dir="ltr" rows="10" cols="60" id="wps_query_params_allow_list"><?php echo esc_textarea(WP_STATISTICS\Helper::get_query_params_allow_list('string')); ?></textarea>
                <p class="description"><?php echo __('Control which URL query parameters are retained in your statistics. The default parameters allowed are: <code>ref</code>, <code>source</code>, <code>utm_source</code>, <code>utm_medium</code>, <code>utm_campaign</code>, <code>utm_content</code>, <code>utm_term</code>, <code>utm_id</code>, <code>s</code>, <code>p</code>. You can add or remove parameters from this list to suit your tracking needs. Enter one parameter per line. For a detailed explanation of each default parameter and guidance on customizing this list, visit our documentation <a href="https://wp-statistics.com/resources/managing-url-query-parameters/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">here</a>.', 'wp-statistics'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	?></p>
                <a onclick="var wps_query_params_allow_list = getElementById('wps_query_params_allow_list'); wps_query_params_allow_list.value = '<?php echo esc_attr(str_replace(array("\r\n", "\n", "\r"), '\n', esc_html(WP_STATISTICS\Helper::get_default_query_params_allow_list('string')))); ?>';" class="button"><?php esc_html_e('Reset to Default', 'wp-statistics'); ?></a>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Host Exclusions', 'wp-statistics'); ?> <a href="#" class="wps-tooltip" title="<?php esc_html_e('Filter out visits from specific hosts.', 'wp-statistics') ?>"><i class="wps-tooltip-icon"></i></a></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row"><label for="wps_excluded_hosts"><?php esc_html_e('Excluded Hosts', 'wp-statistics'); ?></label></th>
            <td>
                <textarea id="wps_excluded_hosts" name="wps_excluded_hosts" rows="5" cols="80" class="code" dir="ltr"><?php echo esc_textarea(WP_STATISTICS\Option::get('excluded_hosts')); ?></textarea>
                <p class="description"><?php echo esc_html__('Provide host names to exclude. Relies on cached IP, not live DNS lookup.', 'wp-statistics'); ?></p><br>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('General Exclusions', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row"><label for="wps-exclusions"><?php esc_html_e('Log Record Exclusions', 'wp-statistics'); ?></label></th>
            <td>
                <input id="wps-exclusions" type="checkbox" value="1" name="wps_record_exclusions" <?php echo WP_STATISTICS\Option::get('record_exclusions') == true ? "checked='checked'" : ''; ?>><label for="wps-exclusions"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php echo esc_html__('Maintain a log of all excluded visits for insight into exclusions.', 'wp-statistics') ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<?php submit_button(esc_html__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='exclusions-settings'")); ?>