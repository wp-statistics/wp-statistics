<?php 
namespace WP_Statistics\Service\PrivacyAudit\Audits;

use WP_STATISTICS\Option;

class RecordUserPageVisits extends AbstractAudit
{
    private static $optionKey = 'visitors_log';

    public static function resolve()
    {
        Option::update(self::$optionKey, false);
    }

    public static function undo()
    {
        Option::update(self::$optionKey, true);
    }

    public static function getStatus()
    {
        return Option::get(self::$optionKey) == true ? 'action_required' : 'passed';
    }

    public static function getStates()
    {
        return [
            'passed' => [
                'status'     => 'success',
                'title'      => esc_html__('The “Record User Page Visits” feature is currently disabled on your website.', 'wp-statistics'),
                'notes'      => __('<p> This status indicates that individual user page visits and WordPress user IDs are not being tracked. Your privacy settings are configured to prioritize user privacy in alignment with applicable laws and regulations.</p><p>Why is this important?</p><p>Keeping this feature disabled ensures that your website minimally impacts user privacy, aligning with best practices for data protection and compliance with privacy laws such as GDPR and CCPA. If your operational or analytical needs change, please review our Guide to <a target="_blank" href="https://wp-statistics.com/resources/avoiding-pii-data-collection/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy">Avoiding PII Data Collection</a> to ensure compliance and user transparency before enabling this feature.</p>', 'wp-statistics'),
                'compliance' => [
                    'key'   => 'passed',
                    'value' => esc_html__('Passed', 'wp-statistics'),
                ],
                'action'     => [
                    'key'   => 'undo',
                    'value' => esc_html__('Undo', 'wp-statistics'),
                ],
            ],
            'action_required' => [
                'status'        => 'warning',
                'title'         => esc_html__('The “Record User Page Visits” feature is currently enabled on your website.', 'wp-statistics'),
                'notes'         => __('<p>This status means that individual user page visits and WordPress user IDs are being actively tracked. While this functionality provides valuable insights into user behavior, it’s important to handle the collected data responsibly.</p><p>Why is this important?</p>
                        <p>Enabling this feature necessitates a careful approach to privacy and data protection. To maintain compliance with privacy laws such as GDPR and CCPA, and to uphold user trust, please ensure the following:</p>
                    <ol>
                            <li><b>Transparency:</b> Your website’s privacy policy should clearly describe the data collection practices, including the specific types of data collected and their intended use.</li>
                            <li><b>Informed Consent:</b> Adequate measures are in place to inform users about the data collection and to obtain their consent where necessary. This may include consent banners, notifications, or other user interfaces that clearly communicate this information.</li>
                            <li><b>Review and Action:</b> Regularly review the necessity of keeping this feature enabled. If the feature is no longer needed, or if you wish to enhance user privacy, consider disabling it. Refer to our guide on <a href="https://chat.openai.com/c/42e80126-57c8-4608-9440-b13d86b8bf5a#" target="_blank">Adjusting Your Privacy Settings</a> for detailed instructions on managing this feature.</li>
                        </ol>', 'wp-statistics'),
                'compliance'    => [
                    'key'   => 'action_required',
                    'value' => esc_html__('Action Required', 'wp-statistics'),
                ],
                'action'     => [
                    'key'   => 'resolve',
                    'value' => esc_html__('Resolve', 'wp-statistics'),
                ],
            ]
        ];
    }
}