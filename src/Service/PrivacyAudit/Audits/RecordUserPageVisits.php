<?php

namespace WP_Statistics\Service\PrivacyAudit\Audits;

use WP_Statistics\Service\PrivacyAudit\Audits\Abstracts\ResolvableAudit;

class RecordUserPageVisits extends ResolvableAudit
{
    public static $optionKey = 'visitors_log';

    public static function isOptionPassed()
    {
        // If option is disabled, consider it passed.
        return !self::isOptionEnabled();
    }

    public static function getPassedStateInfo()
    {
        return [
            'title' => esc_html__('The “Record User Page Views” feature is currently disabled on your website.', 'wp-statistics'),
            'notes' => __('<p> This status indicates that individual user page views and WordPress user IDs are not being tracked. Your privacy settings are configured to prioritize user privacy in alignment with applicable laws and regulations.</p><p><b>Why is this important?</b></p><p>Keeping this feature disabled ensures that your website minimally impacts user privacy, aligning with best practices for data protection and compliance with privacy laws such as GDPR and CCPA. If your operational or analytical needs change, please review our Guide to <a target="_blank" href="https://wp-statistics.com/resources/avoiding-pii-data-collection/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy">Avoiding PII Data Collection</a> to ensure compliance and user transparency before enabling this feature.</p>', 'wp-statistics')
        ];
    }

    public static function getUnpassedStateInfo()
    {
        return [
            'title' => esc_html__('The “Record User Page Views” feature is currently enabled on your website.', 'wp-statistics'),
            'notes' => __('<p>This status means that individual user page views and WordPress user IDs are being actively tracked. While this functionality provides valuable insights into user behavior, it’s important to handle the collected data responsibly.</p><p><b>Why is this important?</b></p>
            <p>Enabling this feature necessitates a careful approach to privacy and data protection. To maintain compliance with privacy laws such as GDPR and CCPA, and to uphold user trust, please ensure the following:</p>
            <ol>
                <li><b>Transparency:</b> Your website’s privacy policy should clearly describe the data collection practices, including the specific types of data collected and their intended use.</li>
                <li><b>Informed Consent:</b> Adequate measures are in place to inform users about the data collection and to obtain their consent where necessary. This may include consent banners, notifications, or other user interfaces that clearly communicate this information.</li>
                <li><b>Review and Action:</b> Regularly review the necessity of keeping this feature enabled. If the feature is no longer needed, or if you wish to enhance user privacy, consider disabling it. Refer to our guide on <a href="https://wp-statistics.com/resources/avoiding-pii-data-collection/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy" target="_blank">Adjusting Your Privacy Settings</a> for detailed instructions on managing this feature.</li>
            </ol>
            <div class="wps-privacy-list__content--note">
                <b>To disable this feature,</b> navigate to <b>Settings -> Basic Tracking -> Record User Page Views</b> and uncheck <b>"Track User Activity"</b>.
            </div>', 'wp-statistics')
        ];
    }
}