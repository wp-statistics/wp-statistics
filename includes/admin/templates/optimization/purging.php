<script type="text/javascript">
    jQuery(document).ready(function ($) {
        const wpsConfig = {
            defaultMessage: '<?php esc_html_e('Are you sure you want to permanently delete this data? ', 'wp-statistics'); ?>',
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
                const title= this.getAttribute('data-agree') ;
                const wpsResult = $(resultId);

                // Get value if select exists
                const wpsValue = selectId ? $(selectId).val() : null;
                if (selectId && (!wpsValue || wpsValue == '0' || (validateValue && !validateValue(wpsValue)))) {
                    wpsResult.html('<div class="wps-alert wps-alert__danger"><p><?php esc_html_e('Please select a valid option or enter a valid value.', 'wp-statistics'); ?></p></div>');
                    return false;
                }

                // Open the confirmation modal
                const modalId = 'setting-confirmation';
                const modal = document.getElementById(modalId);
                if (modal) {
                     const message = title || wpsConfig.defaultMessage;
                    const modalDescription = modal.querySelector('.wps-modal__description');
                    if (modalDescription) {
                        modalDescription.textContent = message;
                    }
                    modal.classList.add('wps-modal--open');

                    // Attach event listener to the primary button (resolve action)
                    const primaryButton = modal.querySelector('button[data-action="resolve"]');
                    if (primaryButton) {
                        // Remove any existing listeners to prevent duplicates
                        const newPrimaryButton = primaryButton.cloneNode(true);
                        primaryButton.parentNode.replaceChild(newPrimaryButton, primaryButton);
                        newPrimaryButton.addEventListener('click', function () {
                            wpsButton.addClass(wpsActionConfig.classes.loading);
                            this.classList.add(wpsActionConfig.classes.loading);

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
                                data: wpsData
                            })
                                .done(function (wpsResultData) {
                                    // Attempt to parse as JSON, but handle as text if it fails
                                    let responseText = wpsResultData;
                                    try {
                                        const parsedData = typeof wpsResultData === 'string' ? JSON.parse(wpsResultData) : wpsResultData;
                                        responseText = parsedData.data || parsedData.message || JSON.stringify(parsedData);
                                    } catch (e) {
                                        // If parsing fails, use the raw response
                                        responseText = wpsResultData || 'No response data.';
                                    }

                                    wpsResult.html('<div class="wps-alert wps-alert__success"><p>' + responseText + '</p></div>');
                                    if (callback) {
                                        callback(wpsValue, wpsData);
                                    }
                                })
                                .fail(function (jqXHR, wpsTextStatus, wpsErrorThrown) {
                                    // Display the raw response for debugging
                                    const errorMessage = jqXHR.responseText || wpsTextStatus + ': ' + wpsErrorThrown;
                                    wpsResult.html('<div class="wps-alert wps-alert__danger"><p>' + errorMessage + '</p></div>');
                                })
                                .always(function () {
                                    wpsButton.removeClass(wpsActionConfig.classes.loading);
                                    newPrimaryButton.classList.remove(wpsActionConfig.classes.loading);
                                    modal.classList.remove('wps-modal--open');
                                });
                        });
                    }
                } else {
                    console.error(`Modal with ID "${modalId}" not found.`);
                }

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
                buttonId: '#delete-word-count-data-submit',
                resultId: '#delete-word-count-data-result',
                action: 'wp_statistics_delete_word_count_data'
            },
            {
                buttonId: '#query-params-cleanup-submit',
                resultId: '#query-params-cleanup-result',
                action: 'wp_statistics_query_params_cleanup'
            },
            {
                buttonId: '#event-data-cleanup-submit',
                resultId: '#event-data-cleanup-result',
                action: 'wp_statistics_event_data_cleanup',
                selectId: '#event-name',
                dataKey: 'event_name'
            }
        ];

        // Initialize all purge actions
        wpsPurgeActions.forEach(wpsActionConfig => wpsHandlePurgeAction({
            ...wpsActionConfig,
            classes: wpsConfig.classes,
            nonce: wpsConfig.nonce
        }));

        // Handle form submissions with modal confirmation
        const wpsForms = document.querySelectorAll('.wps-submit-agree');
        if (wpsForms.length > 0) {
            wpsForms.forEach(function (wpsForm) {
                const wpsSubmitButton = wpsForm.querySelector('button[class*="js-openModal-"]');
                if (wpsSubmitButton) {
                    wpsSubmitButton.addEventListener('click', function (e) {
                        e.preventDefault();
                        const modalId = 'setting-confirmation';
                        const modal = document.getElementById(modalId);
                        if (modal) {
                            const message = wpsForm.getAttribute('data-agree') || wpsConfig.defaultMessage;
                            const modalDescription = modal.querySelector('.wps-modal__description');
                            if (modalDescription) {
                                modalDescription.textContent = message;
                            }
                            modal.classList.add('wps-modal--open');
                            const primaryButton = modal.querySelector('button[data-action="resolve"]');
                            if (primaryButton) {
                                const newPrimaryButton = primaryButton.cloneNode(true);
                                primaryButton.parentNode.replaceChild(newPrimaryButton, primaryButton);
                                newPrimaryButton.addEventListener('click', function () {
                                    wpsSubmitButton.classList.add(wpsConfig.classes.loading);
                                    this.classList.add(wpsConfig.classes.loading);
                                    wpsForm.submit();
                                    modal.classList.remove('wps-modal--open');
                                });
                            }
                        } else {
                            console.error(`Modal with ID "${modalId}" not found.`);
                        }
                    });
                }
            });
        }

    });
</script>
<h2 class="wps-settings-box__title">
    <span><?php esc_html_e('Data Cleanup', 'wp-statistics'); ?></span>
    <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/resources/optimization-data-cleanup/?utm_source=wp-statistics&utm_medium=link&utm_campaign=optimization') ?>" target="_blank"><?php esc_html_e('View Guide', 'wp-statistics'); ?></a>
</h2>
<div class="wrap wps-wrap wps-wrap__setting-form">
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr class="wps-settings-box_head">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Data', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr data-id="delete_records_older_than_tr">
                <th scope="row">
                    <label for="purge-data"><?php esc_html_e('Delete Records Older Than', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <div class="wps-input-group wps-input-group__small">
                        <input type="text" class="wps-input-group__field wps-input-group__field--small code" id="purge-data" name="wps_purge_data" value="365">
                        <span class="wps-input-group__label wps-input-group__label-side"><?php esc_html_e('Days', 'wp-statistics'); ?></span>
                    </div>
                    <p class="description"><?php echo esc_html__('Erase User Stats Older Than Specified Days.', 'wp-statistics') . ' ' . esc_html__('Minimum Age for Deletion: 30 Days.', 'wp-statistics'); ?></p>
                    <button id="purge-data-submit" class="js-openModal-setting-confirmation wps-mt-12 wps-button wps-button--danger-outline" aria-label="<?php esc_attr_e('Purge data older than specified days', 'wp-statistics'); ?>"
                            data-agree="<?php esc_html_e('Are you sure you want to permanently delete this data?', 'wp-statistics'); ?>" type="button" name="purge-data-submit"><?php esc_html_e('Start Purging Now', 'wp-statistics'); ?></button>
                     <div id="purge-data-result" class="wps-mt-12"></div>
                </td>
            </tr>

            <tr data-id="remove_visitors_exceeding_tr">
                <th scope="row">
                    <label for="purge-visitor-hits"><?php esc_html_e('Remove Visitors Exceeding', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <div class="wps-input-group wps-input-group__small">
                        <input type="text" class="wps-input-group__field wps-input-group__field--small code" id="purge-visitor-hits" name="wps_purge_visitor_hits" value="100">
                        <span class="wps-input-group__label wps-input-group__label-side"><?php esc_html_e('Views', 'wp-statistics'); ?></span>
                    </div>

                    <p class="description"><?php echo esc_html__('Erase User Stats for Visitors Exceeding Daily View Limit. Useful for cleaning bot-related data. Removes visitor and their site visits, but not individual page visits, as they are not recorded per user. Minimum View Threshold: 10 Views.', 'wp-statistics'); ?></p>
                    <button id="purge-visitor-hits-submit" class="js-openModal-setting-confirmation wps-button wps-button--danger-outline wps-mt-12" type="button" name="purge-visitor-hits-submit"><?php esc_html_e('Start Purging Now', 'wp-statistics'); ?></button>
                    <div id="purge-visitor-hits-result" class="wps-mt-12"></div>
                </td>
            </tr>

            <tr data-id="remove_user_ids_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Remove User IDs', 'wp-statistics'); ?></span>
                </th>
                <td>
                    <button id="delete-user-ids-submit" class="js-openModal-setting-confirmation wps-button wps-button--danger-outline wps-mt-0" type="button" name="delete_user_ids_submit"><?php esc_html_e('Delete User IDs Now', 'wp-statistics'); ?></button>
                    <p class="description">
                        <?php esc_html_e('Permanently deletes all stored User IDs from the database to anonymize user visit records or to comply with privacy regulations.', 'wp-statistics'); ?><br>
                    </p>
                    <div class="wps-alert wps-alert__danger">
                        <?php esc_html_e('Permanent and cannot be reversed.', 'wp-statistics'); ?>
                    </div>
                    <div id="delete-user-ids-result" class="wps-mt-12"></div>
                </td>
            </tr>

            <tr data-id="clear_user_agent_strings_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Clear User Agent Strings', 'wp-statistics'); ?></span>
                </th>
                <td>
                    <button id="clear-user-agent-strings-submit" class="js-openModal-setting-confirmation wps-button wps-button--danger-outline wps-mt-0" type="button" name="clear_user_agent_strings_submit"><?php esc_html_e('Clear User Agent Data Now', 'wp-statistics'); ?></button>
                    <p class="description">
                        <?php esc_html_e('Permanently erases all User Agent Strings from the database, typically done after troubleshooting to remove unnecessary data.', 'wp-statistics'); ?><br>
                    </p>
                    <div class="wps-alert wps-alert__danger">
                        <?php esc_html_e('Permanent and cannot be undone.', 'wp-statistics'); ?>
                    </div>
                    <div id="clear-user-agent-strings-result" class="wps-mt-12"></div>
                </td>
            </tr>

            <tr data-id="clean_up_recorded_query_parameters_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Clear Word Count Data', 'wp-statistics'); ?></span>
                </th>
                <td>
                    <button id="delete-word-count-data-submit" class="js-openModal-setting-confirmation wps-button  wps-button--danger-outline wps-mt-0" type="submit" name="delete_word_count_data_submit"><?php esc_html_e('Clear Word Count Data Now', 'wp-statistics'); ?></button>
                    <div class="description">
                        <?php esc_html_e('Permanently deletes all stored word count data from the database.', 'wp-statistics'); ?><br>
                         <div class="wps-alert wps-alert__danger"><?php esc_html_e('This action is irreversible.', 'wp-statistics'); ?></div>
                    </div>
                    <div id="delete-word-count-data-result"></div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Clean Up Recorded Query Parameters', 'wp-statistics'); ?></span>
                </th>
                <td>
                    <button id="query-params-cleanup-submit" class="wps-button wps-button--danger-outline js-openModal-setting-confirmation wps-mt-0" type="button" name="query_params_cleanup_submit"><?php esc_html_e('Run Cleanup', 'wp-statistics'); ?></button>
                    <p class="description">
                        <?php esc_html_e('Removes previously stored query parameters from your historical data, ensuring consistency with your current privacy settings.', 'wp-statistics'); ?><br>
                    </p>
                    <div class="wps-alert wps-alert__danger">
                        <?php esc_html_e('It is recommended to back up your database before proceeding, as this cleanup is irreversible.', 'wp-statistics'); ?>
                    </div>
                    <div id="query-params-cleanup-result" class="wps-mt-12"></div>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="event-data-cleanup-submit"><?php esc_html_e('Select Event to Delete', 'wp-statistics'); ?></label>
                </th>
                <td>
                    <?php
                        $eventsModel = new WP_Statistics\Models\EventsModel();
                        $events      = $eventsModel->getEvents(['fields' => 'DISTINCT event_name', 'per_page' => false]);
                    ?>
                    <select dir="ltr" id="event-name" name="event_name">
                        <option value=""><?php esc_html_e('Select an Option', 'wp-statistics'); ?></option>

                        <?php foreach ($events as $event) : ?>
                            <option value="<?php echo esc_attr($event->event_name); ?>"><?php echo esc_html($event->event_name); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <p class="description">
                        <?php esc_html_e('Choose which event to remove from the database. Once deleted, the data cannot be recovered. To stop recording this data in the future, please disable the event.', 'wp-statistics'); ?><br>
                    </p>

                    <button id="event-data-cleanup-submit" class="button button-primary" type="submit" name="event_data_cleanup_submit"><?php esc_html_e('Delete Data', 'wp-statistics'); ?></button>
                    <div id="event-data-cleanup-result"></div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr class="wps-settings-box_head">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Remove Certain User Agent Types', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr data-id="choose_agents_to_delete_tr">
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
                    <button id="delete-agents-submit" class="wps-button wps-button--danger-outline wps-mt-12 js-openModal-setting-confirmation" type="button" name="delete-agents-submit"><?php esc_html_e('Delete Selected Items Now', 'wp-statistics'); ?></button>
                    <div id="delete-agents-result" class="wps-mt-12"></div>
                </td>
            </tr>

            <tr data-id="choose_operating_systems_to_delete_tr">
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
                    <button id="delete-platforms-submit" class="wps-button wps-button--danger-outline wps-mt-12 js-openModal-setting-confirmation" type="button" name="delete-platforms-submit"><?php esc_html_e('Delete Selected Items Now', 'wp-statistics'); ?></button>
                    <div id="delete-platforms-result" class="wps-mt-12"></div>
                </td>
            </tr>

            <tr data-id="erase_data_for_specific_ip_tr">
                <th scope="row">
                    <label for="delete-ip"><?php esc_html_e('Erase Data for Specific IP', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input dir="ltr" id="delete-ip" type="text" name="delete-ip"/>

                    <p class="description"><?php esc_html_e('Input and delete all data associated with a particular IP address. All associated data will be permanently removed.', 'wp-statistics'); ?></p>
                    <button id="delete-ip-submit" class="wps-button wps-button--danger-outline wps-mt-12 js-openModal-setting-confirmation" type="button" name="delete-ip-submit"><?php esc_html_e('Delete Selected Items Now', 'wp-statistics'); ?></button>
                    <div id="delete-ip-result" class="wps-mt-12"></div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
