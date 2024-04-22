<?php
//Get List Roles Wordpress
global $wp_roles;
?>
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2">
                    <h3><?php esc_html_e('Access Control', 'wp-statistics'); ?></h3>
                </th>
            </tr>
            <?php

            //Get List Of Capability
            foreach ($wp_roles->roles as $role) {
                $cap_list = $role['capabilities'];
                foreach ($cap_list as $key => $cap) {

                    //remove level_ from List
                    if (substr($key, 0, 6) != 'level_') {
                        $all_caps[$key] = 1;
                    }
                }
            }

            ksort($all_caps);
            $read_cap    = WP_STATISTICS\Option::get('read_capability', 'manage_options');
            $option_list = '';
            foreach ($all_caps as $key => $cap) {
                if ($key == $read_cap) {
                    $selected = " SELECTED";
                } else {
                    $selected = "";
                }

                $option_list .= sprintf("<option value='%s' %s>%s</option>", esc_attr($key), $selected, esc_attr($key));
            }
            ?>
            <tr valign="top">
                <th scope="row">
                    <label for="wps_read_capability"><?php esc_html_e('Minimum Role to View Statistics', 'wp-statistics') ?></label>
                </th>
                <td>
                    <select dir="ltr" id="wps_read_capability" name="wps_read_capability"><?php echo $option_list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                        ?></select>
                    <p class="description"><?php esc_html_e('Select the least privileged user role allowed to view WP Statistics. Note that higher roles will also have this permission.', 'wp-statistics') ?></p>
                </td>
            </tr>

            <?php
            $manage_cap = WP_STATISTICS\Option::get('manage_capability', 'manage_options');
            foreach ($all_caps as $key => $cap) {
                if ($key == $manage_cap) {
                    $selected = " SELECTED";
                } else {
                    $selected = "";
                }

                $option_list .= sprintf("<option value='%s' %s>%s</option>", esc_attr($key), esc_attr($selected), esc_attr($key));
            }
            ?>
            <tr valign="top">
                <th scope="row">
                    <label for="wps_manage_capability"><?php esc_html_e('Minimum Role to Manage Settings', 'wp-statistics') ?></label>
                </th>
                <td>
                    <select dir="ltr" id="wps_manage_capability" name="wps_manage_capability"><?php echo $option_list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                        ?></select>
                    <p class="description"><?php esc_html_e('Select the least privileged user role allowed to change WP Statistics settings. This should typically be reserved for trusted roles.', 'wp-statistics') ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row" colspan="2">
                    <p class="description"><?php echo sprintf(__('For a deeper understanding of user roles and capabilities in WordPress, you can refer to the <a target=_blank href="%s">WordPress Roles and Capabilities</a> page.', 'wp-statistics'), 'https://wordpress.org/support/article/roles-and-capabilities/');  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></p>
                    <p class="description"><?php echo __('<b>Hints on Capabilities:</b>', 'wp-statistics');   // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	?></p>
                    <div style="font-weight: normal; line-height: 25px"><?php echo __('<ul><li><code>manage_network</code> corresponds to the Super Admin role in a network setup.</li><li><code>manage_options</code> is typically an Administrator capability.</li><li><code>edit_others_posts</code> is usually associated with the Editor role.</li><li><code>publish_posts</code> is a capability given to Authors.</li><li>... and so on. Remember, capabilities define what a user role can do, and roles are a collection of these capabilities.</li></ul>', 'wp-statistics')  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></div>
                    <p class="description"><?php echo sprintf(__('If your needs go beyond the default capabilities and roles, or you wish for a more granular control, consider using the <a href="%s" target=_blank> Capability Manager Enhanced </a> plugin for a robust solution.', 'wp-statistics'), 'https://wordpress.org/plugins/capability-manager-enhanced/');  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></p>
                </th>
            </tr>

            </tbody>
        </table>
    </div>

<?php submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='access-settings'")); ?>