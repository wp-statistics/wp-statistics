<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr>
            <th scope="row" colspan="2"><h3><?php _e('License Settings', 'wp-statistics'); ?></h3></th>
        </tr>

        <?php foreach (\WP_STATISTICS\License::getAddOns() as $key => $addon) : ?>
            <tr>
                <th scope="row" colspan="2">
                    <label><?php echo esc_html($addon); ?></label>
                </th>
                <td colspan="2">
                    <input type="text" name="wp_statistics_license[<?php echo esc_attr($key); ?>]" value="<?php echo $key; ?>" class="regular-text"/>
                    <p class="describe"><?php echo sprintf(__('To get the license, please go to <a href="%s" target="_blank">your account</a>.', 'wp-sms'), esc_url(WP_STATISTICS_SITE_URL . '/my-account/orders/')); ?></p>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='add-ons-settings'")); ?>
