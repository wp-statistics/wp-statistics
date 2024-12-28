<p class="wps-mb-16">
    <?php echo esc_html__('This status means that individual user page views and WordPress user IDs are being actively tracked. While this functionality provides valuable insights into user behavior, it’s important to handle the collected data responsibly.') ?>
</p>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head">
        <?php echo esc_html__('Why is This Important?', 'wp-statistics') ?>
    </div>
    <div class="wps-audit-card__suggestion-text">
        <ol>
            <li><?php echo __('<b>Transparency:</b> Your website’s privacy policy should clearly describe the data collection practices, including the specific types of data collected and their intended use.', 'wp-statistics') ?></li>
            <li><?php echo __('<b>Informed Consent:</b> Adequate measures are in place to inform users about the data collection and to obtain their consent where necessary. This may include consent banners, notifications, or other user interfaces that clearly communicate this information.', 'wp-statistics') ?></li>
            <li><?php echo __('<b>Review and Action:</b> Regularly review the necessity of keeping this feature enabled. If the feature is no longer needed, or if you wish to enhance user privacy, consider disabling it. Refer to our guide on <a target="_blank" href=" ' . esc_url(WP_STATISTICS_SITE_URL . '/resources/data-protection-settings/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy') . '">Adjusting Your Privacy Settings</a> for detailed instructions on managing this feature.', 'wp-statistics') ?></li>
        </ol>
    </div>
</div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head">
        <?php echo esc_html__('How to Disable This Feature', 'wp-statistics') ?>
    </div>
    <p class="wps-audit-card__suggestion-text">
        <?php echo esc_html__('navigate to Settings -> General and uncheck "Track Logged-In User Activity".', 'wp-statistics') ?>
    </p>
</div>