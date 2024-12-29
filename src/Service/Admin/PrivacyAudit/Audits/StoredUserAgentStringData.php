<?php 
namespace WP_Statistics\Service\Admin\PrivacyAudit\Audits;

use WP_Statistics\Components\View;
use WP_STATISTICS\DB;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\Abstracts\BaseAudit;

class StoredUserAgentStringData extends BaseAudit
{
    private static $columnName = 'UAString';

    public static function getStatus()
    {
        global $wpdb;

        $isOptionEnabled = StoreUserAgentString::isOptionEnabled();

        // Count previously stored user agent string data
        $userAgentData = $wpdb->get_var('SELECT COUNT(`' . self::$columnName . '`) FROM ' . DB::table('visitor') . ' WHERE `' . self::$columnName . '` IS NOT NULL AND `' . self::$columnName . '` != ""');

        return !$isOptionEnabled && $userAgentData > 0 ? 'action_required' : 'passed';
    }

    public static function getStates()
    {
        return [
            'action_required' => [
                'status'        => 'warning',
                'title'         => esc_html__('Previous Use of “Store Entire User Agent String” Detected', 'wp-statistics'),
                'notes'         => View::load('components/privacy-audit/stored-user-agent', [], true),
                'compliance'    => [
                    'key'   => 'action_required',
                    'value' => esc_html__('Action Required', 'wp-statistics'),
                ]
            ]
        ];
    }
}