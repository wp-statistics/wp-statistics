<div class="wrap wps-wrap">
    <div class="postbox">
        <form method="post">
            <input type="hidden" name="wps_export" value="true">
            <?php wp_nonce_field('wp_statistics_export_nonce', 'wps_export_file'); ?>
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row" colspan="2"><h3><?php esc_html_e('Export', 'wp-statistics'); ?></h3></th>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="table-to-export"><?php esc_html_e('Select Data Source', 'wp-statistics'); ?></label>
                    </th>

                    <td>
                        <select dir="<?php echo esc_attr((is_rtl() ? 'rtl' : 'ltr')); ?>" id="table-to-export" name="table-to-export" required>
                            <option value=""><?php esc_html_e('Please select', 'wp-statistics'); ?></option>
                            <?php
                            foreach (WP_STATISTICS\DB::table('all', array('historical', 'visitor_relationships')) as $tbl_key => $tbl_name) {
                                echo '<option value="' . esc_attr($tbl_key) . '">' . esc_attr($tbl_name) . '</option>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	
                            }
                            ?>
                        </select>

                        <p class="description"><?php esc_html_e('Choose a specific table or dataset for export.', 'wp-statistics'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="export-file-type"><?php esc_html_e('Choose Export Format', 'wp-statistics'); ?></label>
                    </th>

                    <td>
                        <select dir="ltr" id="export-file-type" name="export-file-type" required>
                            <option value=""><?php esc_html_e('Please select', 'wp-statistics'); ?></option>
                            <option value="xml">XML</option>
                            <option value="csv">CSV</option>
                            <option value="tsv">TSV</option>
                        </select>

                        <p class="description"><?php esc_html_e('Select a file format for the exported data.', 'wp-statistics'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th scope="row">
                        <label for="export-headers"><?php esc_html_e('Add Header Row', 'wp-statistics'); ?></label>
                    </th>

                    <td>
                        <input id="export-headers" type="checkbox" value="1" name="export-headers">
                        <p class="description"><?php esc_html_e('Include column names at the top of the exported file.', 'wp-statistics'); ?></p>
                    </td>
                </tr>

                <tr>
                    <td></td>
                    <td>
                        <div class="wp-statistics-notice">
                            <strong><?php esc_html_e('Privacy Notice for Data Export', 'wp-statistics') ?></strong>
                            <p><?php _e('Exported data may contain personal information. Review our <a href="https://wp-statistics.com/resources/handling-of-personal-data-during-export-procedures/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">Handling of Personal Data During Export Procedures</a> before exporting to ensure compliance with privacy laws.', 'wp-statistics') // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction	 ?></p>
                        </div>

                        <?php submit_button(esc_html__('Begin Export', 'wp-statistics'), 'primary', 'export-file-submit'); ?>
                    </td>
                </tr>

                </tbody>
            </table>
        </form>
    </div>
</div>
