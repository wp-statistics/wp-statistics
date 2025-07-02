<div class="wps-wrap__main">
    <div class="wp-header-end"></div>

    <div class="wps-postbox-addon__step">
        <div class="wps-addon__step__info">
            <span class="wps-addon__step__image wps-addon__step__image--lock"></span>
            <h2 class="wps-addon__step__title"><?php esc_html_e('Unlock Premium Features', 'wp-statistics'); ?></h2>
            <p class="wps-addon__step__desc"><?php esc_html_e('Enter your license key to unlock premium add-ons and enhance your experience.', 'wp-statistics'); ?></p>
        </div>
        <div class="wps-addon__step__license">
            <div class="wps-addon__step__active-license">
                <!--   Add wps-danger or wps-warning class to input-->
                <input type="text" placeholder="xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                <button class="wps-postbox-addon-button js-addon-active-license disabled"><?php esc_html_e('Activate License', 'wp-statistics'); ?></button>
            </div>
         </div>
        <div class="wps-addon__step__faq">
            <ul>
                <li>
                    <a href="https://wp-statistics.com/pricing/?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon" target="_blank" title="<?php esc_html_e('Buy Premium Now', 'wp-statistics'); ?>"><?php esc_html_e('Buy Premium Now', 'wp-statistics'); ?></a>
                </li>
                <li>
                    <a href="https://wp-statistics.com/resources/finding-and-entering-your-license-key/?utm_source=wp-statistics&utm_medium=link&utm_campaign=install-addon" target="_blank" title="<?php esc_html_e('I bought Premium, where is my license key?', 'wp-statistics'); ?>"><?php esc_html_e('I bought Premium, where is my license key?', 'wp-statistics'); ?></a>
                </li>
                <li>
                    <a href="https://wp-statistics.com/contact-us/?step=purchasing" target="_blank" title="<?php esc_html_e('Have questions or trouble activating your license?', 'wp-statistics'); ?>"><?php esc_html_e('Have questions or trouble activating your license?', 'wp-statistics'); ?></a>
                </li>
            </ul>
        </div>
        <a class="wps-addon__step__back-to-addons" href="<?php echo esc_url(admin_url('admin.php?page=wps_plugins_page')) ?>" title="<?php esc_html_e('Back to Add-Ons', 'wp-statistics'); ?>"><?php esc_html_e('Back to Add-Ons', 'wp-statistics'); ?></a>

    </div>
</div>