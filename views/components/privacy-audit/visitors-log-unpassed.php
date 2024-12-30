<div class="wps-mb-16"><?php echo esc_html__('This status means that individual user page views and WordPress user IDs are being actively tracked. While this functionality provides valuable insights into user behavior, it’s important to handle the collected data responsibly.', 'wp-statistics') ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Why is this important?', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <?php echo __('<p>Enabling this feature necessitates a careful approach to privacy and data protection. To maintain compliance with privacy laws such as GDPR and CCPA, and to uphold user trust, please ensure the following:</p>') ?>
        <ol>
            <li><?php echo __('<b>Transparency:</b> Your website’s privacy policy should clearly describe the data collection practices, including the specific types of data collected and their intended use.', 'wp-statistics') ?></li>
            <li><?php echo __('<b>Informed Consent:</b> Adequate measures are in place to inform users about the data collection and to obtain their consent where necessary. This may include consent banners, notifications, or other user interfaces that clearly communicate this information.', 'wp-statistics') ?></li>
            <li><?php echo __('<b>Review and Action:</b> Regularly review the necessity of keeping this feature enabled. If the feature is no longer needed, or if you wish to enhance user privacy, consider disabling it. Refer to our guide on <a href="https://wp-statistics.com/resources/avoiding-pii-data-collection/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy" target="_blank">Adjusting Your Privacy Settings</a> for detailed instructions on managing this feature.', 'wp-statistics') ?></li>
        </ol>
    </div>
</div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('How to Disable This Feature', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <p><?php echo esc_html__('To disable this feature, navigate to Settings -> General and uncheck "Track Logged-In User Activity".', 'wp-statistics'); ?></p>
    </div>
</div>