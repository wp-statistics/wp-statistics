<div class="postbox wps-postbox-wrap wps-privacy-list">
    <div class="postbox-header">
        <h2><?php esc_html_e('Privacy Audit', 'wp-statistics'); ?></h2>
        <p><?php esc_html_e('Audit List: Monitor Compliance Status', 'wp-statistics'); ?></p>
    </div>
    <div class="wps-privacy-list__items">
        <div class="wps-privacy-list__item wps-privacy-list__item--success">
            <div class="wps-privacy-list__title">
                <div>
                    <span class="wps-privacy-list__icon wps-privacy-list__icon--success"></span>
                    <span><?php esc_html_e('The “Record User Page Visits” feature is currently disabled on your website.', 'wp-statistics'); ?></span>
                </div>
                <a class="wps-privacy-list__button wps-privacy-list__button--success"><?php esc_html_e('Passed', 'wp-statistics'); ?></a>
            </div>
            <div class="wps-privacy-list__content">
                <?php esc_html_e('content...', 'wp-statistics'); ?>
            </div>
        </div>
        <div class="wps-privacy-list__item wps-privacy-list__item--success">
            <div class="wps-privacy-list__title">
                <div>
                    <span class="wps-privacy-list__icon wps-privacy-list__icon--success"></span>
                    <span><?php esc_html_e('Your recorded IPs in row format.', 'wp-statistics'); ?></span>
                </div>
                <a class="wps-privacy-list__button wps-privacy-list__button--undo"><?php esc_html_e('Undo', 'wp-statistics'); ?></a>
            </div>
            <div class="wps-privacy-list__content">
                <?php esc_html_e('content...', 'wp-statistics'); ?>
            </div>
        </div>
        <div class="wps-privacy-list__item wps-privacy-list__item--success">
            <div class="wps-privacy-list__title">
                <div>
                    <span class="wps-privacy-list__icon wps-privacy-list__icon--success"></span>
                    <span><?php esc_html_e(' You don\'t have any recorded data attached to your registered users.', 'wp-statistics'); ?></span>
                </div>
                <a class="wps-privacy-list__button wps-privacy-list__button--success"><?php esc_html_e('Passed', 'wp-statistics'); ?></a>
            </div>
            <div class="wps-privacy-list__content">
                <?php esc_html_e('content...', 'wp-statistics'); ?>
            </div>
        </div>
        <div class="wps-privacy-list__item wps-privacy-list__item--warning">
            <div class="wps-privacy-list__title">
                <div>
                    <span class="wps-privacy-list__icon wps-privacy-list__icon--warning"></span>
                    <span><?php esc_html_e('The “Record User Page Visits” feature is currently disabled on your website.', 'wp-statistics'); ?></span>
                </div>
                <a class="wps-privacy-list__button wps-privacy-list__button--warning"><?php esc_html_e('Action Required', 'wp-statistics'); ?></a>
            </div>
            <div class="wps-privacy-list__content">
                <?php esc_html_e('content...', 'wp-statistics'); ?>
            </div>
        </div>
        <div class="wps-privacy-list__item wps-privacy-list__item--warning">
            <div class="wps-privacy-list__title">
                <div>
                    <span class="wps-privacy-list__icon wps-privacy-list__icon--warning-square"></span>
                    <span><?php esc_html_e('The “Record User Page Visits” feature is currently disabled on your website.', 'wp-statistics'); ?></span>
                </div>
                <a class="wps-privacy-list__button wps-privacy-list__button--resolve"><?php esc_html_e('Resolve', 'wp-statistics'); ?></a>
            </div>
            <div class="wps-privacy-list__content">
                <?php echo sprintf(__('<p>This status means that individual user page visits and WordPress user IDs are being actively tracked. While this functionality provides valuable insights into user behavior, it’s important to handle the collected data responsibly.<br/>Why is this important?<br/>Enabling this feature necessitates a careful approach to privacy and data protection. To maintain compliance with privacy laws such as GDPR and CCPA, and to uphold user trust, please ensure the following:</p> 
                        <ol>
                        <li>Transparency: Your website’s privacy policy should clearly describe the data collection practices, including the specific types of data collected and their intended use.</li>
                        <li>Informed Consent: Adequate measures are in place to inform users about the data collection and to obtain their consent where necessary. This may include consent banners, notifications, or other user interfaces that clearly communicate this information.</li>
                        <li>Review and Action: Regularly review the necessity of keeping this feature enabled. If the feature is no longer needed, or if you wish to enhance user privacy, consider disabling it. Refer to our guide on <a href="">Adjusting Your Privacy Settings </a>for detailed instructions on managing this feature.</li>
                        </ol>'), 'wp-statistics') ?>
            </div>
        </div>
    </div>
</div>


