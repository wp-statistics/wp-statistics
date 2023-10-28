<div class="wrap wps-wrap">
    <div class="postbox">
        <form method="post">
            <input type="hidden" name="wps_export" value="true">
            <?php wp_nonce_field('wp_statistics_export_nonce', 'wps_export_file'); ?>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" colspan="2"><h3><?php _e('Export', 'wp-statistics'); ?></h3></th>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="table-to-export"><?php _e('Select Data Source', 'wp-statistics'); ?></label>
                    </th>

                    <td>
                        <select dir="<?php echo(is_rtl() ? 'rtl' : 'ltr'); ?>" id="table-to-export" name="table-to-export" required>
                            <option value=""><?php _e('Please select', 'wp-statistics'); ?></option>
                            <?php
                            foreach (WP_STATISTICS\DB::table('all', array('historical', 'visitor_relationships')) as $tbl_key => $tbl_name) {
                                echo '<option value="' . esc_attr($tbl_key) . '">' . esc_attr($tbl_name) . '</option>';
                            }
                            ?>
                        </select>

                        <p class="description"><?php _e('Choose the specific table or data source you wish to export from.', 'wp-statistics'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="export-file-type"><?php _e('Choose Export Format', 'wp-statistics'); ?></label>
                    </th>

                    <td>
                        <select dir="ltr" id="export-file-type" name="export-file-type" required>
                            <option value=""><?php _e('Please select', 'wp-statistics'); ?></option>
                            <option value="xml">XML</option>
                            <option value="csv">CSV</option>
                            <option value="tsv">TSV</option>
                        </select>

                        <p class="description"><?php _e('Select the desired file format for your exported data.', 'wp-statistics'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="export-headers"><?php _e('Add Header Row', 'wp-statistics'); ?></label>
                    </th>

                    <td>
                        <input id="export-headers" type="checkbox" value="1" name="export-headers">
                        <p class="description"><?php _e('Toggle this option to include a header row in your exported file, which provides column names for clarity.', 'wp-statistics'); ?></p>
                        <?php submit_button(__('Begin Export', 'wp-statistics'), 'primary', 'export-file-submit'); ?>
                    </td>
                </tr>

                </tbody>
            </table>
        </form>
    </div>
</div>
