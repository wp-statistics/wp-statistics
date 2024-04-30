<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery("#purge-data-submit").click(function () {

            var action = jQuery('#purge-data').val();

            if (action == 0)
                return false;

            var agree = confirm('<?php esc_html_e('Are you sure?', 'wp-statistics'); ?>');

            if (!agree)
                return false;

            jQuery("#purge-data-submit").attr("disabled", "disabled");
            jQuery("#purge-data-status").html("<img src='<?php echo esc_url(plugins_url('wp-statistics')); ?>/assets/images/loading.gif'/>");
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    'action': 'wp_statistics_purge_data',
                    'purge-days': action,
                    'wps_nonce': '<?php echo wp_create_nonce('wp_rest'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'
                },
                datatype: 'json',
            })
                .always(function (result) {
                    jQuery("#purge-data-status").html("");
                    jQuery("#purge-data-result").html(result);
                    jQuery("#purge-data-submit").removeAttr("disabled");
                    jQuery("#wps_historical_purge").show();
                });
        });

        jQuery("#purge-visitor-hits-submit").click(function () {

            var action = jQuery('#purge-visitor-hits').val();

            if (action == 0)
                return false;

            var agree = confirm('<?php esc_html_e('Are you sure?', 'wp-statistics'); ?>');

            if (!agree)
                return false;

            jQuery("#purge-visitor-hits-submit").attr("disabled", "disabled");
            jQuery("#purge-visitor-hits-status").html("<img src='<?php echo esc_url(plugins_url('wp-statistics')); ?>/assets/images/loading.gif'/>");
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    'action': 'wp_statistics_purge_visitor_hits',
                    'purge-hits': action,
                    'wps_nonce': '<?php echo wp_create_nonce('wp_rest'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'
                },
                datatype: 'json',
            })
                .always(function (result) {
                    jQuery("#purge-visitor-hits-status").html("");
                    jQuery("#purge-visitor-hits-result").html(result);
                    jQuery("#purge-visitor-hits-submit").removeAttr("disabled");
                });
        });

        jQuery("#empty-table-submit").click(function () {

            var action = jQuery('#empty-table').val();

            if (action == 0)
                return false;

            var agree = confirm('<?php esc_html_e('Are you sure?', 'wp-statistics'); ?>');

            if (!agree)
                return false;

            jQuery("#empty-table-submit").attr("disabled", "disabled");
            jQuery("#empty-status").html("<img src='<?php echo esc_url(plugins_url('wp-statistics')); ?>/assets/images/loading.gif'/>");
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    'action': 'wp_statistics_empty_table',
                    'table-name': action,
                    'wps_nonce': '<?php echo wp_create_nonce('wp_rest'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'
                },
                datatype: 'json',
            })
                .always(function (result) {
                    jQuery("#empty-status").html("");
                    jQuery("#empty-result").html(result);
                    jQuery("#empty-table-submit").removeAttr("disabled");
                });
        });

        jQuery("#delete-agents-submit").click(function () {

            var action = jQuery('#delete-agent').val();

            if (action == 0)
                return false;

            var agree = confirm('<?php esc_html_e('Are you sure?', 'wp-statistics'); ?>');

            if (!agree)
                return false;

            jQuery("#delete-agents-submit").attr("disabled", "disabled");
            jQuery("#delete-agents-status").html("<img src='<?php echo esc_url(plugins_url('wp-statistics')); ?>/assets/images/loading.gif'/>");
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    'action': 'wp_statistics_delete_agents',
                    'agent-name': action,
                    'wps_nonce': '<?php echo wp_create_nonce('wp_rest'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'
                },
                datatype: 'json',
            })
                .always(function (result) {
                    jQuery("#delete-agents-status").html("");
                    jQuery("#delete-agents-result").html(result);
                    jQuery("#delete-agents-submit").removeAttr("disabled");
                    aid = data['agent-name'].replace(/[^a-zA-Z]/g, "");
                    jQuery("#agent-" + aid + "-id").remove();
                });
        });

        jQuery("#delete-platforms-submit").click(function () {

            var action = jQuery('#delete-platform').val();

            if (action == 0)
                return false;

            var agree = confirm('<?php esc_html_e('Are you sure?', 'wp-statistics'); ?>');

            if (!agree)
                return false;

            jQuery("#delete-platforms-submit").attr("disabled", "disabled");
            jQuery("#delete-platforms-status").html("<img src='<?php echo esc_url(plugins_url('wp-statistics')); ?>/assets/images/loading.gif'/>");
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    'action': 'wp_statistics_delete_platforms',
                    'platform-name': action,
                    'wps_nonce': '<?php echo wp_create_nonce('wp_rest'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'
                },
                datatype: 'json',
            })
                .always(function (result) {
                    jQuery("#delete-platforms-status").html("");
                    jQuery("#delete-platforms-result").html(result);
                    jQuery("#delete-platforms-submit").removeAttr("disabled");
                    pid = data['platform-name'].replace(/[^a-zA-Z]/g, "");
                    jQuery("#platform-" + pid + "-id").remove();
                });
        });

        jQuery("#delete-ip-submit").click(function () {

            var value = jQuery('#delete-ip').val();

            if (value == 0)
                return false;

            var agree = confirm('<?php esc_html_e('Are you sure?', 'wp-statistics'); ?>');

            if (!agree)
                return false;

            jQuery("#delete-ip-submit").attr("disabled", "disabled");
            jQuery("#delete-ip-status").html("<img src='<?php echo esc_url(plugins_url('wp-statistics')); ?>/assets/images/loading.gif'/>");
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    'action': 'wp_statistics_delete_ip',
                    'ip-address': value,
                    'wps_nonce': '<?php echo wp_create_nonce('wp_rest');  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'
                },
                datatype: 'json',
            })
                .always(function (result) {
                    jQuery("#delete-ip-status").html("");
                    jQuery("#delete-ip-result").html(result);
                    jQuery("#delete-ip-submit").removeAttr("disabled");
                    jQuery("#delete-ip").value('');
                });
        });

        jQuery("#delete-user-ids-submit").click(function () {

            var agree = confirm('<?php esc_html_e('Are you sure?', 'wp-statistics'); ?>');

            if (!agree)
                return false;

            jQuery("#delete-user-ids-submit").attr("disabled", "disabled");
            jQuery("#delete-user-ids-status").html("<img src='<?php echo esc_url(plugins_url('wp-statistics')); ?>/assets/images/loading.gif'/>");
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    'action': 'wp_statistics_delete_user_ids',
                    'wps_nonce': '<?php echo wp_create_nonce('wp_rest'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'
                },
                datatype: 'json',
            })
                .always(function (result) {
                    jQuery("#delete-user-ids-status").html("");
                    jQuery("#delete-user-ids-result").html(result);
                    jQuery("#delete-user-ids-submit").removeAttr("disabled");
                });
        });

        jQuery("#clear-user-agent-strings-submit").click(function () {

            var agree = confirm('<?php esc_html_e('Are you sure?', 'wp-statistics'); ?>');

            if (!agree)
                return false;

            jQuery("#clear-user-agent-strings-submit").attr("disabled", "disabled");
            jQuery("#clear-user-agent-strings-status").html("<img src='<?php echo esc_url(plugins_url('wp-statistics')); ?>/assets/images/loading.gif'/>");
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    'action': 'wp_statistics_clear_user_agent_strings',
                    'wps_nonce': '<?php echo wp_create_nonce('wp_rest'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'
                },
                datatype: 'json',
            })
                .always(function (result) {
                    jQuery("#clear-user-agent-strings-status").html("");
                    jQuery("#clear-user-agent-strings-result").html(result);
                    jQuery("#clear-user-agent-strings-submit").removeAttr("disabled");
                });
        });

        jQuery("#query-params-cleanup-submit").click(function () {

            var agree = confirm('<?php esc_html_e('Are you sure?', 'wp-statistics'); ?>');

            if (!agree)
                return false;

            jQuery("#query-params-cleanup-submit").attr("disabled", "disabled");
            jQuery("#query-params-cleanup-status").html("<img src='<?php echo esc_url(plugins_url('wp-statistics')); ?>/assets/images/loading.gif'/>");
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: {
                    'action': 'wp_statistics_query_params_cleanup',
                    'wps_nonce': '<?php echo wp_create_nonce('wp_rest'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'
                },
                datatype: 'json',
            })
                .always(function (result) {
                    jQuery("#query-params-cleanup-status").html("");
                    jQuery("#query-params-cleanup-result").html(result);
                    jQuery("#query-params-cleanup-submit").removeAttr("disabled");
                });
        });
    });
</script>
<div class="wrap wps-wrap">
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Data', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="empty-table"><?php esc_html_e('Clear Table Contents', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <select dir="<?php echo(is_rtl() ? 'rtl' : 'ltr'); ?>" id="empty-table" name="empty-table">
                        <option value="0"><?php esc_html_e('Select an Option', 'wp-statistics'); ?></option>
                        <?php
                        foreach (WP_STATISTICS\DB::table('all', 'historical') as $tbl_key => $tbl_name) {
                            echo '<option value="' . esc_attr($tbl_key) . '">' . esc_attr($tbl_name) . '</option>';
                        }
                        ?>
                        <option value="all"><?php echo esc_html__('All', 'wp-statistics'); ?></option>
                    </select>

                    <p class="description">
                        <span class="wps-note"><?php esc_html_e('Caution:', 'wp-statistics'); ?></span>
                        <?php esc_html_e('All data in the table will be permanently deleted.', 'wp-statistics'); ?>
                    </p>
                    <input id="empty-table-submit" class="button button-primary" type="submit" value="<?php esc_html_e('Erase Data Now', 'wp-statistics'); ?>" name="empty-table-submit" Onclick="return false;"/>
                    <span id="empty-status"></span>
                    <div id="empty-result"></div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="purge-data"><?php esc_html_e('Delete Records Older Than', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input type="text" class="small-text code" id="purge-data" name="wps_purge_data" value="365"/>
                    <label for="purge-data"><?php esc_html_e('Days', 'wp-statistics'); ?></label>

                    <p class="description"><?php echo esc_html__('Erase User Stats Older Than Specified Days.', 'wp-statistics') . ' ' . esc_html__('Minimum Age for Deletion: 30 Days.', 'wp-statistics'); ?></p>
                    <input id="purge-data-submit" class="button button-primary" type="submit" value="<?php esc_html_e('Start Purging Now', 'wp-statistics'); ?>" name="purge-data-submit" Onclick="return false;"/>
                    <span id="purge-data-status"></span>
                    <div id="purge-data-result"></div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="purge-visitor-hits"><?php esc_html_e('Remove Visitors Exceeding', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input type="text" class="small-text code" id="purge-visitor-hits" name="wps_purge_visitor_hits" value="10"/>
                    <label for="purge-visitor-hits"><?php esc_html_e('Views', 'wp-statistics'); ?></label>

                    <p class="description"><?php echo esc_html__('Erase User Stats for Visitors Exceeding Daily View Limit. Useful for cleaning bot-related data. Removes visitor and their site visits, but not individual page visits, as they are not recorded per user. Minimum View Threshold: 10 Views.', 'wp-statistics'); ?></p>
                    <input id="purge-visitor-hits-submit" class="button button-primary" type="submit" value="<?php esc_html_e('Start Purging Now', 'wp-statistics'); ?>" name="purge-visitor-hits-submit" Onclick="return false;"/>
                    <span id="purge-visitor-hits-status"></span>
                    <div id="purge-visitor-hits-result"></div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="delete-user-ids-submit"><?php esc_html_e('Remove User IDs', 'wp-statistics'); ?></label>
                </th>
                <td>
                    <input id="delete-user-ids-submit" class="button button-primary" type="submit" value="<?php esc_html_e('Delete User IDs Now', 'wp-statistics'); ?>" name="delete_user_ids_submit">
                    <p class="description">
                        <?php esc_html_e('Permanently deletes all stored User IDs from the database to anonymize user visit records or to comply with privacy regulations.', 'wp-statistics'); ?><br>
                        <span class="wps-note"><?php esc_html_e('Caution:', 'wp-statistics'); ?></span>
                        <?php esc_html_e('Permanent and cannot be reversed.', 'wp-statistics'); ?>
                    </p>
                    <span id="delete-user-ids-status"></span>
                    <div id="delete-user-ids-result"></div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="clear-user-agent-strings-submit"><?php esc_html_e('Clear User Agent Strings', 'wp-statistics'); ?></label>
                </th>
                <td>
                    <input id="clear-user-agent-strings-submit" class="button button-primary" type="submit" value="<?php esc_html_e('Clear User Agent Data Now', 'wp-statistics'); ?>" name="clear_user_agent_strings_submit">
                    <p class="description">
                        <?php esc_html_e('Permanently erases all User Agent Strings from the database, typically done after troubleshooting to remove unnecessary data.', 'wp-statistics'); ?><br>
                        <span class="wps-note"><?php esc_html_e('Caution:', 'wp-statistics'); ?></span>
                        <?php esc_html_e('Permanent and cannot be undone.', 'wp-statistics'); ?>
                    </p>
                    <span id="clear-user-agent-strings-status"></span>
                    <div id="clear-user-agent-strings-result"></div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="query-params-cleanup-submit"><?php esc_html_e('Clean Up Recorded Query Parameters', 'wp-statistics'); ?></label>
                </th>
                <td>
                    <input id="query-params-cleanup-submit" class="button button-primary" type="submit" value="<?php esc_html_e('Run Cleanup', 'wp-statistics'); ?>" name="query_params_cleanup_submit">
                    <p class="description">
                        <?php esc_html_e('Removes previously stored query parameters from your historical data, ensuring consistency with your current privacy settings.', 'wp-statistics'); ?><br>
                        <span class="wps-note"><?php esc_html_e('Caution:', 'wp-statistics'); ?></span>
                        <?php esc_html_e('It is recommended to back up your database before proceeding, as this cleanup is irreversible.', 'wp-statistics'); ?>
                    </p>
                    <span id="query-params-cleanup-status"></span>
                    <div id="query-params-cleanup-result"></div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Remove Certain User Agent Types', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="delete-agent"><?php esc_html_e('Choose Agents to Delete', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <select dir="ltr" id="delete-agent" name="delete-agent">
                        <option value="0"><?php esc_html_e('Select an Option', 'wp-statistics'); ?></option>
                        <?php
                        $agents = wp_statistics_ua_list();
                        foreach ($agents as $agent) {
                            $aid = preg_replace("/[^a-zA-Z]/", "", $agent);
                            echo "<option value='$agent' id='agent-" . esc_attr($aid) . "-id'>" . esc_attr($agent) . "</option>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                        }
                        ?>
                    </select>

                    <p class="description"><?php esc_html_e('Select and delete specific User Agents from the database. All associated data will be permanently removed.', 'wp-statistics'); ?></p>
                    <input id="delete-agents-submit" class="button button-primary" type="submit" value="<?php esc_html_e('Delete Selected Items Now', 'wp-statistics'); ?>" name="delete-agents-submit" Onclick="return false;">
                    <span id="delete-agents-status"></span>
                    <div id="delete-agents-result"></div>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="delete-platform"><?php esc_html_e('Choose Operating Systems to Delete', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <select dir="ltr" id="delete-platform" name="delete-platform">
                        <option value="0"><?php esc_html_e('Select an Option', 'wp-statistics'); ?></option>
                        <?php
                        $platforms = wp_statistics_platform_list();
                        foreach ($platforms as $platform) {
                            if (!empty($platform)) {
                                $pid = preg_replace("/[^a-zA-Z]/", "", $platform);
                                echo "<option value='$platform' id='platform-" . esc_attr($pid) . "-id'>" . esc_attr($platform) . "</option>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            }
                        }
                        ?>
                    </select>

                    <p class="description"><?php esc_html_e('Select and delete specific platforms from the database. All associated data will be permanently removed.', 'wp-statistics'); ?></p>
                    <input id="delete-platforms-submit" class="button button-primary" type="submit" value="<?php esc_html_e('Delete Selected Items Now', 'wp-statistics'); ?>" name="delete-platforms-submit" Onclick="return false;">
                    <span id="delete-platforms-status"></span>
                    <div id="delete-platforms-result"></div>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="delete-ip"><?php esc_html_e('Erase Data for Specific IP', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input dir="ltr" id="delete-ip" type="text" name="delete-ip"/>

                    <p class="description"><?php esc_html_e('Input and delete all data associated with a particular IP address. All associated data will be permanently removed.', 'wp-statistics'); ?></p>
                    <input id="delete-ip-submit" class="button button-primary" type="submit" value="<?php esc_html_e('Delete Selected Items Now', 'wp-statistics'); ?>" name="delete-ip-submit" Onclick="return false;">
                    <span id="delete-ip-status"></span>
                    <div id="delete-ip-result"></div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
