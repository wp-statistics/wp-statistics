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
                <button class="wps-postbox-addon-button"><?php esc_html_e('Activate License', 'wp-statistics'); ?></button>
            </div>
            <div class="wps-alert wps-alert--warning">
                <span class="icon"></span>
                <div>
                    <p><?php esc_html_e('Almost There! Your license is valid. To proceed, please whitelist this domain in customer portal.', 'wp-statistics'); ?></p>
                    <div>
                        <a href=""><?php esc_html_e('Learn how to whitelist your domain', 'wp-statistics'); ?></a>
                    </div>
                </div>
            </div>
            <div class="wps-alert wps-alert--danger">
                <span class="icon"></span>
                <div>
                    <p><?php esc_html_e('License Key Error! The license key you entered does not exist. Please double-check your key or purchase a new one.', 'wp-statistics'); ?></p>
                    <div>
                        <a href=""><?php esc_html_e('Renew Your License Now'); ?></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="wps-addon__step__faq">
            <ul>
                <li>
                    <a href="" target="_blank" title="<?php esc_html_e('Buy Premium Now', 'wp-statistics'); ?>"><?php esc_html_e('Buy Premium Now', 'wp-statistics'); ?></a>
                </li>
                <li>
                    <a href="" target="_blank" title="<?php esc_html_e('I brought Premium, where is my license key?', 'wp-statistics'); ?>"><?php esc_html_e('I brought Premium, where is my license key?', 'wp-statistics'); ?></a>
                </li>
                <li>
                    <a href="" target="_blank" title="<?php esc_html_e('Have a question or trouble with your license?', 'wp-statistics'); ?>"><?php esc_html_e('Have a question or trouble with your license?', 'wp-statistics'); ?></a>
                </li>
            </ul>
        </div>
    </div>
</div>