<div class="wrap wps-wrap">
    <div class="postbox">
        <form action="<?php echo admin_url('admin.php?page=wps_optimization_page&tab=database') ?>" method="post">
            <?php wp_nonce_field('wps_optimization_nonce'); ?>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" colspan="2"><h3><?php _e('Database Setup', 'wp-statistics'); ?></h3></th>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="index-submit"><?php _e('Re-run Install:', 'wp-statistics'); ?></label>
                    </th>
                    <td>
                        <input type="hidden" name="submit" value="1"/>
                        <button id="install-submit" class="button button-primary" type="submit" value="1" name="install-submit"><?php _e('Install Now!', 'wp-statistics'); ?></button>
                        <p class="description"><?php _e('If for some reason your installation of WP Statistics is missing the database tables or other core items, this will re-execute the install process.', 'wp-statistics'); ?></p>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
    <div class="postbox">
        <form action="<?php echo admin_url('admin.php?page=wps_optimization_page&tab=database') ?>" method="post" id="wps-run-optimize-database-form">
            <?php wp_nonce_field('wps_optimization_nonce'); ?>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" colspan="2">
                        <h3><?php _e('Repair and Optimization Database Tables', 'wp-statistics'); ?></h3></th>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="index-submit"><?php _e('Optimize Table:', 'wp-statistics'); ?></label>
                    </th>
                    <td>
                        <select dir="<?php echo(is_rtl() ? 'rtl' : 'ltr'); ?>" id="optimize-table" name="optimize-table">
                            <option value="0"><?php _e('Please select', 'wp-statistics'); ?></option>
                            <?php
                            foreach (WP_STATISTICS\DB::table('all') as $tbl_key => $tbl_name) {
                                echo '<option value="' . esc_attr($tbl_key) . '">' . esc_attr($tbl_name) . '</option>';
                            }
                            ?>
                            <option value="all"><?php echo __('All', 'wp-statistics'); ?></option>
                        </select>
                        <p class="description"><?php _e('Please select the table you would like to optimize and repair',
                                'wp-statistics'); ?></p>

                        <input type="hidden" name="submit" value="1"/>
                        <button class="button button-primary" type="submit" value="1" name="optimize-database-submit" style="margin-top:5px;"><?php _e('Run Now!', 'wp-statistics'); ?></button>
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
                alert('<?php _e("Please select database table", "wp-statistics"); ?>');
                e.preventDefault();
            }
        });
    });
</script>