<?php 
namespace WP_Statistics\Service\Admin\PrivacyAudit\Audits;

use WP_Statistics\Components\View;
use WP_STATISTICS\DB;
use WP_Statistics\Service\Admin\PrivacyAudit\Audits\Abstracts\BaseAudit;

class StoredUserIdData extends BaseAudit
{
    private static $columnName = 'user_id';

    public static function getStatus()
    {
        global $wpdb;

        $isOptionEnabled = RecordUserPageVisits::isOptionEnabled();
        
        // Count previously stored user id data
        $userIDs = $wpdb->get_var('SELECT COUNT(`' . self::$columnName . '`) FROM ' . DB::table('visitor') . ' WHERE `' . self::$columnName . '` != 0');

        return !$isOptionEnabled && $userIDs > 0 ? 'action_required' : 'passed';
    }

    public static function getStates()
    {
        return [
            'action_required' => [
                'status'        => 'warning',
                'title'         => esc_html__('Previous Recording of User IDs Detected', 'wp-statistics'),
                'notes' => View::load('components/privacy-audit/stored-user-id', [], true),
                'compliance'    => [
                    'key'   => 'action_required',
                    'value' => esc_html__('Action Required', 'wp-statistics'),
                ]
            ]
        ];
    }
}