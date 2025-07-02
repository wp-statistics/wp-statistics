<script type="text/javascript">
    jQuery(document).ready(function ($) {
        const wpsConfig = {
            messages: {
                confirm: '<?php esc_html_e('Are you sure you want to permanently delete this data? This action cannot be undone.', 'wp-statistics'); ?>'
            },
            classes: {
                loading: 'wps-loading-button'
            },
            nonce: '<?php echo wp_create_nonce('wp_rest'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'
        };

        function wpsHandlePurgeAction(wpsActionConfig) {
            const {
                buttonId,
                selectId,
                resultId,
                action,
                dataKey,
                callback,
                validateValue
            } = wpsActionConfig;

            const wpsButton = $(buttonId);
            wpsButton.prop('onclick', null).off('click');

            wpsButton.on('click', function (e) {
                e.preventDefault();

                const wpsResult = $(resultId);

                // Get value if select exists
                const wpsValue = selectId ? $(selectId).val() : null;
                if (selectId && (!wpsValue || wpsValue == '0' || (validateValue && !validateValue(wpsValue)))) {
                    return false;
                }

                if (!confirm(wpsActionConfig.messages.confirm)) {
                    return false;
                }

                wpsButton.addClass(wpsActionConfig.classes.loading);

                const wpsData = {
                    'action': action,
                    'wps_nonce': wpsActionConfig.nonce
                };

                if (dataKey && wpsValue) {
                    wpsData[dataKey] = wpsValue;
                }

                $.ajax({
                    url: ajaxurl,
                    type: 'post',
                    data: wpsData,
                    datatype: 'json',
                })
                    .done(function (wpsResultData) {
                        wpsResult.html(wpsResultData);
                        if (callback) {
                            callback(wpsValue, wpsData);
                        }
                    })
                    .fail(function (jqXHR, wpsTextStatus, wpsErrorThrown) {
                        wpsResult.html('<div class="error"><p>' + wpsTextStatus + ': ' + wpsErrorThrown + '</p></div>');
                    })
                    .always(function () {
                        wpsButton.removeClass(wpsActionConfig.classes.loading);
                    });

                return false;
            });
        }

        // Configure each purge action
        const wpsPurgeActions = [
            {
                buttonId: '#purge-data-submit',
                selectId: '#purge-data',
                resultId: '#purge-data-result',
                action: 'wp_statistics_purge_data',
                dataKey: 'purge-days',
                validateValue: (wpsValue) => parseInt(wpsValue) >= 30,
                callback: () => $('#wps_historical_purge').show()
            },
            {
                buttonId: '#purge-visitor-hits-submit',
                selectId: '#purge-visitor-hits',
                resultId: '#purge-visitor-hits-result',
                action: 'wp_statistics_purge_visitor_hits',
                dataKey: 'purge-hits',
                validateValue: (wpsValue) => parseInt(wpsValue) >= 10
            },
            {
                buttonId: '#delete-agents-submit',
                selectId: '#delete-agent',
                resultId: '#delete-agents-result',
                action: 'wp_statistics_delete_agents',
                dataKey: 'agent-name',
                callback: (wpsValue) => {
                    const wpsAid = wpsValue.replace(/[^a-zA-Z]/g, "");
                    $('#agent-' + wpsAid + '-id').remove();
                }
            },
            {
                buttonId: '#delete-platforms-submit',
                selectId: '#delete-platform',
                resultId: '#delete-platforms-result',
                action: 'wp_statistics_delete_platforms',
                dataKey: 'platform-name',
                callback: (wpsValue) => {
                    const wpsPid = wpsValue.replace(/[^a-zA-Z]/g, "");
                    $('#platform-' + wpsPid + '-id').remove();
                }
            },
            {
                buttonId: '#delete-ip-submit',
                selectId: '#delete-ip',
                resultId: '#delete-ip-result',
                action: 'wp_statistics_delete_ip',
                dataKey: 'ip-address',
                validateValue: (wpsValue) => /^(\d{1,3}\.){3}\d{1,3}$/.test(wpsValue),
                callback: () => $('#delete-ip').val('')
            },
            {
                buttonId: '#delete-user-ids-submit',
                resultId: '#delete-user-ids-result',
                action: 'wp_statistics_delete_user_ids'
            },
            {
                buttonId: '#clear-user-agent-strings-submit',
                resultId: '#clear-user-agent-strings-result',
                action: 'wp_statistics_clear_user_agent_strings'
            },
            {
                buttonId: '#query-params-cleanup-submit',
                resultId: '#query-params-cleanup-result',
                action: 'wp_statistics_query_params_cleanup'
            }
        ];

        // Initialize all purge actions
        wpsPurgeActions.forEach(wpsActionConfig => wpsHandlePurgeAction({
            ...wpsActionConfig,
            messages: wpsConfig.messages,
            classes: wpsConfig.classes,
            nonce: wpsConfig.nonce
        }));

        // Handle form submissions with confirmation
        const wpsForms = document.querySelectorAll('.wps-submit-agree');
        if (wpsForms.length > 0) {
            wpsForms.forEach(function (wpsForm) {
                const wpsSubmitButton = wpsForm.querySelector('button[type="button"]');
                wpsSubmitButton.addEventListener('click', function (e) {
                    e.preventDefault();
                    const wpsAgreeMessage = wpsForm.getAttribute('data-agree');
                    if (!confirm(wpsAgreeMessage)) return;

                    wpsSubmitButton.classList.add(wpsConfig.classes.loading);
                    wpsForm.submit();
                });
            });
        }
    });
</script>
<div class="wrap wps-wrap">
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Data', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr>
                <th scope="row">
                    <label for="purge-data"><?php esc_html_e('Delete Records Older Than', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input type="text" class="small-text code" id="purge-data" name="wps_purge_data" value="365"/>
                    <label for="purge-data"><?php esc_html_e('Days', 'wp-statistics'); ?></label>

                    <p class="description"><?php echo esc_html__('Erase User Stats Older Than Specified Days.', 'wp-statistics') . ' ' . esc_html__('Minimum Age for Deletion: 30 Days.', 'wp-statistics'); ?></p>
                    <button id="purge-data-submit" class="button button-primary" type="submit" name="purge-data-submit"><?php esc_html_e('Start Purging Now', 'wp-statistics'); ?></button>
                    <div id="purge-data-result"></div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="purge-visitor-hits"><?php esc_html_e('Remove Visitors Exceeding', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input type="text" class="small-text code" id="purge-visitor-hits" name="wps_purge_visitor_hits" value="100"/>
                    <label for="purge-visitor-hits"><?php esc_html_e('Views', 'wp-statistics'); ?></label>

                    <p class="description"><?php echo esc_html__('Erase User Stats for Visitors Exceeding Daily View Limit. Useful for cleaning bot-related data. Removes visitor and their site visits, but not individual page visits, as they are not recorded per user. Minimum View Threshold: 10 Views.', 'wp-statistics'); ?></p>
                    <button id="purge-visitor-hits-submit" class="button button-primary" type="submit" name="purge-visitor-hits-submit"><?php esc_html_e('Start Purging Now', 'wp-statistics'); ?></button>
                    <div id="purge-visitor-hits-result"></div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="delete-user-ids-submit"><?php esc_html_e('Remove User IDs', 'wp-statistics'); ?></label>
                </th>
                <td>
                    <button id="delete-user-ids-submit" class="button button-primary" type="submit" name="delete_user_ids_submit"><?php esc_html_e('Delete User IDs Now', 'wp-statistics'); ?></button>
                    <p class="description">
                        <?php esc_html_e('Permanently deletes all stored User IDs from the database to anonymize user visit records or to comply with privacy regulations.', 'wp-statistics'); ?><br>
                        <span class="wps-note"><?php esc_html_e('Caution', 'wp-statistics'); ?>:</span>
                        <?php esc_html_e('Permanent and cannot be reversed.', 'wp-statistics'); ?>
                    </p>
                    <div id="delete-user-ids-result"></div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="clear-user-agent-strings-submit"><?php esc_html_e('Clear User Agent Strings', 'wp-statistics'); ?></label>
                </th>
                <td>
                    <button id="clear-user-agent-strings-submit" class="button button-primary" type="submit" name="clear_user_agent_strings_submit"><?php esc_html_e('Clear User Agent Data Now', 'wp-statistics'); ?></button>
                    <p class="description">
                        <?php esc_html_e('Permanently erases all User Agent Strings from the database, typically done after troubleshooting to remove unnecessary data.', 'wp-statistics'); ?><br>
                        <span class="wps-note"><?php esc_html_e('Caution', 'wp-statistics'); ?>:</span>
                        <?php esc_html_e('Permanent and cannot be undone.', 'wp-statistics'); ?>
                    </p>
                    <div id="clear-user-agent-strings-result"></div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="query-params-cleanup-submit"><?php esc_html_e('Clean Up Recorded Query Parameters', 'wp-statistics'); ?></label>
                </th>
                <td>
                    <button id="query-params-cleanup-submit" class="button button-primary" type="submit" name="query_params_cleanup_submit"><?php esc_html_e('Run Cleanup', 'wp-statistics'); ?></button>
                    <p class="description">
                        <?php esc_html_e('Removes previously stored query parameters from your historical data, ensuring consistency with your current privacy settings.', 'wp-statistics'); ?><br>
                        <span class="wps-note"><?php esc_html_e('Caution', 'wp-statistics'); ?>:</span>
                        <?php esc_html_e('It is recommended to back up your database before proceeding, as this cleanup is irreversible.', 'wp-statistics'); ?>
                    </p>
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
                    <button id="delete-agents-submit" class="button button-primary" type="submit" name="delete-agents-submit"><?php esc_html_e('Delete Selected Items Now', 'wp-statistics'); ?></button>
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
                    <button id="delete-platforms-submit" class="button button-primary" type="submit" name="delete-platforms-submit"><?php esc_html_e('Delete Selected Items Now', 'wp-statistics'); ?></button>
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
                    <button id="delete-ip-submit" class="button button-primary" type="submit" name="delete-ip-submit"><?php esc_html_e('Delete Selected Items Now', 'wp-statistics'); ?></button>
                    <div id="delete-ip-result"></div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
