<?php if (\WP_STATISTICS\GeoIP::active()) { ?>
    <div class="wrap wps-wrap">
        <div class="postbox">
            <form action="<?php echo esc_url(admin_url('admin.php?page=wps_optimization_page&tab=updates')) ?>" method="post">
                <?php wp_nonce_field('wps_optimization_nonce'); ?>
                <table class="form-table">
                    <tbody>
                    <tr valign="top">
                        <th scope="row" colspan="2">
                            <h3><?php esc_html_e('GeoLocation Settings', 'wp-statistics'); ?></h3>
                        </th>
                    </tr>

                    <tr valign="top">
                        <th scope="row">
                            <label for="populate-submit"><?php esc_html_e('Update Country Data', 'wp-statistics'); ?></label>
                        </th>

                        <td>
                            <input type="hidden" name="submit" value="1"/>
                            <button id="populate-submit" class="button button-primary" type="submit" value="1" name="populate-submit"><?php esc_html_e('Refresh Country Data', 'wp-statistics'); ?></button>
                            <p class="description"><?php esc_html_e('This action updates and corrects any unidentified or missing country data in the database. Please be patient, as the process might take some time, depending on the amount of data.', 'wp-statistics'); ?></p>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </form>
        </div>
    </div>
<?php } ?>

<div class="wrap wps-wrap">
    <div class="postbox">
        <form action="<?php echo esc_url(admin_url('admin.php?page=wps_optimization_page&tab=updates')) ?>" method="post">
            <?php wp_nonce_field('wps_optimization_nonce'); ?>
            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" colspan="2">
                        <h3><?php esc_html_e('IP Address Management', 'wp-statistics'); ?></h3>
                    </th>
                </tr>

                <tr valign="top">
                    <th scope="row">
                        <label for="hash-ips-submit"><?php esc_html_e('Convert IP Addresses to Hash', 'wp-statistics'); ?></label>
                    </th>

                    <td>
                        <input type="hidden" name="submit" value="1"/>
                        <button id="hash-ips-submit" class="button button-primary" type="submit" value="1" name="hash-ips-submit" onclick="return confirm('<?php esc_html_e('This will replace all IP addresses in the database with hash values and cannot be undo, are you sure?', 'wp-statistics'); ?>')"><?php esc_html_e('Initiate Conversion', 'wp-statistics'); ?></button>
                        <p class="description"><?php esc_html_e('This function will transform all stored IP addresses into hashed values. Please note that after hashing, original IP addresses cannot be retrieved. The hashing process ensures user privacy while still allowing the system to generate accurate location-based insights. This operation might be time-consuming, so we recommend performing it during off-peak hours.', 'wp-statistics'); ?></p>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>