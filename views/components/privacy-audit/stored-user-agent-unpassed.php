<div class="wps-mb-16"><?php echo esc_html__('This setting allows for the collection of complete user agent strings from your visitors, offering detailed insights into their browsing devices and environments. While invaluable for debugging and optimizing user experience, this feature gathers detailed user information, warranting careful use and consideration for privacy.', 'wp-statistics') ?></div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Privacy Considerations', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <ol>
            <li><?php echo __('<b>Temporary Activation:</b> Intended for short-term diagnostic purposes, it’s recommended to disable this feature once specific issues have been resolved to minimize the collection of extensive user data.', 'wp-statistics') ?></li>
            <li><?php echo __('<b>Privacy Compliance:</b> The activation of this feature necessitates clear disclosure within your privacy policy about the collection of full user agent strings and their purpose.', 'wp-statistics') ?></li>
        </ol>
    </div>
</div>
<div class="wps-audit-card__suggestion wps-mb-16">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('Management Recommendations', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <ol>
            <li><?php echo __('<b>Selective Use:</b> Enable this feature only as needed for troubleshooting or enhancing website functionality.', 'wp-statistics') ?></li>
            <li><?php echo __('<b>Disabling After Use:</b> Remember to deactivate this setting after debugging processes to ensure unnecessary data is not collected.', 'wp-statistics') ?></li>
            <li><?php echo __('<b>Data Removal:</b> For instructions on deleting previously stored user agent data, refer to our guide <a href="https://wp-statistics.com/resources/how-to-clear-user-agent-strings/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy" target="_blank">here</a>.', 'wp-statistics') ?></li>
         </ol>
    </div>
</div>
<div class="wps-audit-card__suggestion">
    <div class="wps-audit-card__suggestion-head"><?php echo esc_html__('How to Disable This Feature', 'wp-statistics') ?></div>
    <div class="wps-audit-card__suggestion-text">
        <p><?php echo esc_html__('To disable this feature, navigate to Settings -> General -> Store Entire User Agent String and uncheck "Enable".', 'wp-statistics'); ?></p>
    </div>
</div>