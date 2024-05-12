<?php 
namespace WP_Statistics\Service\PrivacyAudit\Audits;

use WP_STATISTICS\DB;
use WP_Statistics\Service\PrivacyAudit\Audits\Abstracts\BaseAudit;

class StoredUserAgentStringData extends BaseAudit
{
    private static $columnName = 'UAString';

    public static function getStatus()
    {
        global $wpdb;

        $isOptionEnabled = StoreUserAgentString::isOptionEnabled();

        // Count previously stored user agent string data
        $userAgentData = $wpdb->get_var('SELECT COUNT(`' . self::$columnName . '`) FROM ' . DB::table('visitor') . ' WHERE `' . self::$columnName . '` IS NOT NULL');

        return !$isOptionEnabled && $userAgentData > 0 ? 'action_required' : 'passed';
    }

    public static function getStates()
    {
        return [
            'action_required' => [
                'status'        => 'warning',
                'title'         => esc_html__('Previous Use of “Store Entire User Agent String” Detected', 'wp-statistics'),
                'notes'         => __('<p>Our system has detected remnants of full user agent strings in your database, indicating that the “Store Entire User Agent String” feature was enabled at some point in the past. To align with best practices for user privacy, we recommend clearing this data if it is no longer necessary for diagnostic purposes.</p>
                    <p><b>How to Clear User Agent String Data?</b></p>
                    <ol>
                        <li>Navigate to the <b>Optimization</b> tab.</li>
                        <li>Select <b>Data Cleanup</b>.</li>
                        <li>Click on <b>Clear User Agent Strings</b> to initiate the cleanup process.</li>
                    </ol>
                    <p>This action will remove all previously stored full user agent strings from your database, enhancing privacy and data protection on your website.</p>
                    <p><b>Need More Information?</b></p>
                    <p>For detailed instructions and further information on the importance of this cleanup process, please visit our dedicated resource: <a target="_blank" href="https://wp-statistics.com/resources/how-to-clear-user-agent-strings/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy">How to Clear User Agent Strings</a>.</p>
                    ', 'wp-statistics'),
                'compliance'    => [
                    'key'   => 'action_required',
                    'value' => esc_html__('Action Required', 'wp-statistics'),
                ]
            ]
        ];
    }
}