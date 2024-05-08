<?php 
namespace WP_Statistics\Service\PrivacyAudit\Audits;

use WP_STATISTICS\Option;

class StoreUserAgentString extends AbstractAudit
{
    public static $optionKey = 'store_ua';

    public static function getStates()
    {
        return [
            'passed' => [
                'status'     => 'success',
                'title'      => esc_html__('The “Store Entire User Agent String” feature is currently disabled on your website.', 'wp-statistics'),
                'notes'      => __('<p>This default setting ensures that extensive details about your visitors’ browsing environments are not recorded, aligning with best practices for user privacy and data minimization.</p><p>Why This Matters</p>
                    <ol>
                        <li><b>Privacy Preservation: </b> Disabling this feature helps prevent the collection of data that could potentially identify individuals, fostering a safer and more private browsing experience.</li>
                        <li><b>Compliance with Privacy Laws: </b> Keeping this setting disabled by default supports compliance with stringent privacy regulations by avoiding the unnecessary collection of detailed user information.
                            Recommendations for Use.</li>
                        <li><b>Considerations for Enabling: </b> Should you need to enable this feature for debugging or optimization purposes, ensure it’s used judiciously and for a limited time only.</li>
                        <li><b>Transparency with Users: </b> If activated, update your privacy policy to reflect the temporary collection of full user agent strings, including the purpose and scope of data collection.</li>
                    </ol>', 'wp-statistics'),
                'compliance' => [
                    'key'   => 'passed',
                    'value' => esc_html__('Passed', 'wp-statistics'),
                ],
                // If option is enabled in the setting, no action could be performed.
                'action'     => Option::get(self::$optionKey)  ? [] : ['key' => 'undo', 'value' => esc_html__('Undo', 'wp-statistics')]
            ],
            'action_required' => [
                'status'        => 'warning',
                'title'         => esc_html__('The “Store Entire User Agent String” feature is currently enabled on your website.', 'wp-statistics'),
                'notes'         => __('<p>This setting allows for the collection of complete user agent strings from your visitors, offering detailed insights into their browsing devices and environments. While invaluable for debugging and optimizing user experience, this feature gathers detailed user information, warranting careful use and consideration for privacy.</p>
                    <p>Privacy Considerations</p>
                    <ol>
                        <li><b>Temporary Activation:</b> Intended for short-term diagnostic purposes, it’s recommended to disable this feature once specific issues have been resolved to minimize the collection of extensive user data.</li>
                        <li><b>Privacy Compliance:</b> The activation of this feature necessitates clear disclosure within your privacy policy about the collection of full user agent strings and their purpose.
                            Management Recommendations.</li>
                        <li><b>Selective Use:</b> Enable this feature only as needed for troubleshooting or enhancing website functionality.</li>
                        <li><b>Disabling After Use:</b> Remember to deactivate this setting after debugging processes to ensure unnecessary data is not collected.</li>
                        <li><b>Data Removal:</b> For instructions on deleting previously stored user agent data, refer to our guide here.</li>
                    </ol>', 'wp-statistics'),
                'compliance'    => [
                    'key'   => 'action_required',
                    'value' => esc_html__('Action Required', 'wp-statistics'),
                ],
                'action'        => [
                    'key'   => 'resolve',
                    'value' => esc_html__('Resolve', 'wp-statistics'),
                ],
            ]
        ];
    }
}