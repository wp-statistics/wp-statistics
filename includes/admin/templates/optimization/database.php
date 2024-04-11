<div class="wrap wps-wrap">
    <div class="postbox">
        <form action="<?php echo esc_url(admin_url('admin.php?page=wps_optimization_page&tab=database')) ?>" method="post">
            <?php wp_nonce_field('wps_optimization_nonce'); ?>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" colspan="2"><h3><?php esc_html_e('Database Configuration', 'wp-statistics'); ?></h3></th>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="index-submit"><?php esc_html_e('Reinitialize Plugin', 'wp-statistics'); ?></label>
                    </th>
                    <td>
                        <input type="hidden" name="submit" value="1"/>
                        <button id="install-submit" class="button button-primary" type="submit" value="1" name="install-submit"><?php esc_html_e('Reinitialize Now', 'wp-statistics'); ?></button>
                        <p class="description">
                            <?php esc_html_e('Click to begin the setup process for the plugin from scratch, useful when you encounter issues with missing elements or inconsistencies in the database setup.', 'wp-statistics'); ?><br>
                            <span class="wps-note"><?php esc_html_e('Caution:', 'wp-statistics'); ?></span>
                            <?php esc_html_e('Executing this may lead to the loss of certain data.', 'wp-statistics'); ?>
                        </p>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
    <div class="postbox">
        <form action="<?php echo esc_url(admin_url('admin.php?page=wps_optimization_page&tab=database')) ?>" method="post" id="wps-run-optimize-database-form">
            <?php wp_nonce_field('wps_optimization_nonce'); ?>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" colspan="2">
                        <h3><?php esc_html_e('Optimize & Repair', 'wp-statistics'); ?></h3></th>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="index-submit"><?php esc_html_e('Choose a Table to Optimize', 'wp-statistics'); ?></label>
                    </th>
                    <td>
                        <select dir="<?php echo(is_rtl() ? 'rtl' : 'ltr'); ?>" id="optimize-table" name="optimize-table">
                            <option value="0"><?php esc_html_e('Select an Option', 'wp-statistics'); ?></option>
                            <?php
                            foreach (WP_STATISTICS\DB::table('all') as $tbl_key => $tbl_name) {
                                echo '<option value="' . esc_attr($tbl_key) . '">' . esc_attr($tbl_name) . '</option>';
                            }
                            ?>
                            <option value="all"><?php echo esc_html__('All', 'wp-statistics'); ?></option>
                        </select>
                        <p class="description"><?php esc_html_e('Select a specific database table to optimize and repair. This can help improve the efficiency and performance of the selected table.',
                                'wp-statistics'); ?></p>

                        <input type="hidden" name="submit" value="1"/>
                        <button class="button button-primary" type="submit" value="1" name="optimize-database-submit" style="margin-top:5px;"><?php esc_html_e('Execute Optimization', 'wp-statistics'); ?></button>
                    </td>
                </tr>

                </tbody>
            </table>
        </form>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        jQuery("#wps-run-optimize-database-form").submit(function (e) {
            var tbl = jQuery('#optimize-table').val();
            if (tbl == "0") {
                alert('<?php esc_html_e("Please select database table", "wp-statistics"); ?>');
                e.preventDefault();
            }
        });
    });
</script>