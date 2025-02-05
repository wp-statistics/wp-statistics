<div id="wps-modal-filter-popup" dir="<?php echo is_rtl() ? 'rtl' : 'ltr'; ?>" style="display:none;">
     <form action="<?php echo esc_url(admin_url('admin.php')); ?>" method="get" id="wp_statistics_visitors_filter_form">
         <input type="hidden" name="page" value="<?php echo esc_attr($pageName); ?>">
        <div id="wps-visitors-filter-form" class="wps-modal-filter-form">
            <table class="o-table">
                <!-- Dropdown Filters -->
                <tr>
                    <td><?php esc_html_e('Browsers', 'wp-statistics'); ?></td>
                </tr>
                <tr>
                    <td>
                        <select name="agent" class="select2 wps-width-100 filter-select" data-type-show="select2" data-type="browsers">
                            <option value=""><?php esc_html_e('All', 'wp-statistics'); ?></option>
                            <!-- Options will be populated dynamically via JavaScript -->
                        </select>
                    </td>
                </tr>

                <tr>
                    <td><?php esc_html_e('Country', 'wp-statistics'); ?></td>
                </tr>
                <tr>
                    <td>
                        <select name="location" class="select2 wps-width-100 filter-select" data-type-show="select2" data-type="location">
                            <option value=""><?php esc_html_e('All', 'wp-statistics'); ?></option>
                            <!-- Options will be populated dynamically via JavaScript -->
                        </select>
                    </td>
                </tr>

                <tr>
                    <td><?php esc_html_e('Platform', 'wp-statistics'); ?></td>
                </tr>
                <tr>
                    <td>
                        <select name="platform" class="select2 wps-width-100 filter-select" data-type-show="select2" data-type="platform">
                            <option value=""><?php esc_html_e('All', 'wp-statistics'); ?></option>
                            <!-- Options will be populated dynamically via JavaScript -->
                        </select>
                    </td>
                </tr>

                <tr>
                    <td><?php esc_html_e('Referrer', 'wp-statistics'); ?></td>
                </tr>
                <tr>
                    <td>
                        <select name="referrer" class="select2 wps-width-100 filter-select" data-type-show="select2" data-type="referrer">
                            <option value=""><?php esc_html_e('All', 'wp-statistics'); ?></option>
                            <!-- Options will be populated dynamically via JavaScript -->
                        </select>
                    </td>
                </tr>

                <tr>
                    <td><?php esc_html_e('User', 'wp-statistics'); ?></td>
                </tr>
                <tr>
                    <td>
                        <select name="user_id" class="select2 wps-width-100 filter-select" data-type-show="select2" data-type="users">
                            <option value=""><?php esc_html_e('All', 'wp-statistics'); ?></option>
                            <!-- Options will be populated dynamically via JavaScript -->
                        </select>
                    </td>
                </tr>

                <!-- Input Filter -->
                <tr>
                    <td><?php esc_html_e('IP Address', 'wp-statistics'); ?></td>
                </tr>
                <tr>
                    <td>
                        <input type="text" name="ip" class="wps-width-100 filter-input" placeholder="<?php esc_attr_e('Enter IP (e.g., 192.168.1.1) or hash (#...)', 'wp-statistics'); ?>" autocomplete="off">
                    </td>
                </tr>

                <!-- Submit Button -->
                <tr>
                    <td>
                        <div class="wps-tb-window-footer">
                            <button type="button" class="wps-reset-filter wps-modal-reset-filter"><?php esc_html_e('Reset', 'wp-statistics'); ?></button>
                            <button type="submit" class="button-primary"><?php esc_html_e('Filter', 'wp-statistics'); ?></button>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>