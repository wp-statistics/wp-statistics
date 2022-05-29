<div class="wrap wps-wrap">
    <div class="postbox">
        <form action="<?php echo admin_url('admin.php?page=wps_optimization_page&tab=updates') ?>" method="post">
            <?php wp_nonce_field('wps_optimization_nonce'); ?>
            <table class="form-table">
                <tbody>
                <?php if (\WP_STATISTICS\GeoIP::active()) { ?>
                    <tr valign="top">
                        <th scope="row" colspan="2"><h3><?php _e('GeoIP Options', 'wp-statistics'); ?></h3></th>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <label for="populate-submit"><?php _e('Countries:', 'wp-statistics'); ?></label>
                        </th>

                        <td>
                            <input type="hidden" name="submit" value="1"/>
                            <button id="populate-submit" class="button button-primary" type="submit" value="1" name="populate-submit"><?php _e('Update Now!', 'wp-statistics'); ?></button>
                            <p class="description"><?php _e('Updates any unknown location data in the database, this may take a while', 'wp-statistics'); ?></p>
                        </td>
                    </tr>
                <?php } ?>

                <tr valign="top">
                    <th scope="row" colspan="2"><h3><?php _e('IP Addresses', 'wp-statistics'); ?></h3></th>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="populate-submit"><?php _e('Hash IP Addresses:', 'wp-statistics'); ?></label>
                    </th>

                    <td>
                        <input type="hidden" name="submit" value="1"/>
                        <button id="hash-ips-submit" class="button button-primary" type="submit" value="1" name="hash-ips-submit" onclick="return confirm('<?php _e('This will replace all IP addresses in the database with hash values and cannot be undo, are you sure?', 'wp-statistics'); ?>')"><?php _e('Update Now!', 'wp-statistics'); ?></button>
                        <p class="description"><?php _e('Replace IP addresses in the database with hash values, you will not be able to recover the IP addresses in the future to populate location information afterwards and this may take a while', 'wp-statistics'); ?></p>
                    </td>
                </tr>

                </tbody>
            </table>
        </form>
    </div>
</div>
