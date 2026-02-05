<?php
namespace WP_Statistics\Service\Admin\PrivacyAudit\Audits;

use WP_Statistics\Components\View;
use WP_STATISTICS\DB;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\Abstracts\BaseAudit;

class UnhashedIpAddress extends BaseAudit
{
    private static $columnName = 'ip';

    public static function getStatus()
    {
        global $wpdb;

        $storeIpEnabled = HashIpAddress::isOptionEnabled();

        // Count raw IPs from the visitors table.
        // Real IPs contain '.' (IPv4) or ':' (IPv6)
        $unhashedIPs = $wpdb->get_var('SELECT COUNT(DISTINCT ' . self::$columnName . ') FROM ' . DB::table('visitor') . ' WHERE ' . self::$columnName . ' LIKE "%.%" OR ' . self::$columnName . ' LIKE "%:%"');

        // When store_ip is disabled but raw IPs exist in DB → legacy data needs cleanup
        // When store_ip is enabled, raw IPs in DB are expected → passed
        return !$storeIpEnabled && $unhashedIPs > 0 ? 'action_required' : 'passed';
    }

    public static function getStates()
    {
        return [
            'action_required' => [
                'status'        => 'warning',
                'title'         => esc_html__('Unhashed IP Addresses Detected', 'wp-statistics'),
                'notes' => View::load('components/privacy-audit/unhashed-ip', [], true),
                'compliance'    => [
                    'key'   => 'action_required',
                    'value' => esc_html__('Action Required', 'wp-statistics'),
                ]
            ]
        ];
    }
}
