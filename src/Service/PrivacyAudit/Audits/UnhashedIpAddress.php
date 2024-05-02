<?php 
namespace WP_Statistics\Service\PrivacyAudit\Audits;

use WP_STATISTICS\DB;
use WP_STATISTICS\IP;

class UnhashedIpAddress extends AbstractAudit
{
    private static $columnName = 'ip';

    public static function getStatus()
    {
        global $wpdb;

        // Count unhashed IPs from the visitors table.
        $unhashedIPs = $wpdb->get_var('SELECT COUNT(DISTINCT ' . self::$columnName . ') FROM ' . DB::table('visitor') . ' WHERE ' . self::$columnName . ' NOT LIKE "#hash#%"');

        return $unhashedIPs > 0 ? 'action_required' : 'passed';
    }

    public static function resolve()
    {
        IP::Update_HashIP_Visitor();
    }

    public static function getStates()
    {
        return [
            'action_required' => [
                'status'        => 'warning',
                'title'         => esc_html__('Unhashed IP Addresses Detected', 'wp-statistics'),
                'notes'         => __('<p>Our system has identified that raw IP addresses are stored in your database, likely due to the “Hash IP Addresses” feature being disabled in the past. To enhance data protection and align with privacy best practices, converting these IP addresses to a hashed format is strongly recommended.</p>
                    <p>How to Convert IP Addresses to Hash</p>
                    <ol>
                        <li>Go to the <b>Optimization</b> section.</li>
                        <li>Select <b>Plugin Maintenance</b>.</li>
                        <li>Choose <b>Convert IP Addresses to Hash</b> to start the conversion process.</li>
                    </ol>
                    <p>This step will transform all existing raw IP addresses in your database into hashed formats, significantly improving user privacy and your website’s compliance with data protection regulations.</p>
                    <p>Need More Information?</p>
                    <p>For a comprehensive guide on this process and to understand the benefits of IP address hashing, please refer to our detailed documentation: <a target="_blank" href="https://wp-statistics.com/resources/converting-ip-addresses-to-hash/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy">Converting IP Addresses to Hash</a>.</p>
                    ', 'wp-statistics'),
                'compliance'    => [
                    'key'   => 'action_required',
                    'value' => esc_html__('Action Required', 'wp-statistics'),
                ],
                'action'        => [
                    'key'           => 'resolve',
                    'value'         => esc_html__('Resolve', 'wp-statistics'),
                    'confirm'       => true,
                    'removable'     => true,
                    'confirm_text'  => esc_html__('This will replace all IP addresses in the database with hash values and cannot be undone, are you sure?', 'wp-statistics'),
                    'success_text'  => esc_html__('Successfully anonymized IP addresses using hash values.', 'wp-statistics')
                ]
            ]
        ];
    }
}