<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr>
            <th scope="row" colspan="2"><h3><?php _e('License Settings', 'wp-statistics'); ?></h3></th>
        </tr>

        <?php foreach (\WP_Statistics\Utils\LicenseHelper::getAddOns() as $addon) : ?>
            <tr>
                <th scope="row" colspan="2">
                    <label><?php echo esc_html($addon->getName()); ?></label>
                </th>
                <td colspan="2">
                    <div>
                        <input type="text" name="<?php echo esc_attr($addon->getHtmlOptionName()); ?>" value="<?php echo esc_attr($addon->getLicenseFromOption()); ?>" class="regular-text"/>
                        <?php echo esc_html($addon->getStatus()); ?>
                    </div>
                    <p class="describe"><?php echo sprintf(__('To get the license, please go to <a href="%s" target="_blank">your account</a>.', 'wp-statistics'), $addon->getAccoutUrl()); ?></p>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='add-ons-settings'")); ?>
