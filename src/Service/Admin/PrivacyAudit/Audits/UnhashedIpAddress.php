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

        $isOptionEnabled = HashIpAddress::isOptionEnabled();

        // Count unhashed IPs from the visitors table.
        $unhashedIPs = $wpdb->get_var('SELECT COUNT(DISTINCT ' . self::$columnName . ') FROM ' . DB::table('visitor') . ' WHERE ' . self::$columnName . ' NOT LIKE "#hash#%"');

        return $isOptionEnabled && $unhashedIPs > 0 ? 'action_required' : 'passed';
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