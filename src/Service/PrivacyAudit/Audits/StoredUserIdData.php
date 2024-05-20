<?php 
namespace WP_Statistics\Service\PrivacyAudit\Audits;

use WP_STATISTICS\DB;
use WP_Statistics\Service\PrivacyAudit\Audits\Abstracts\BaseAudit;

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
                'notes'         => __('<p>Our system has found that User IDs have previously been recorded in your database, which may have occurred while the “Record User Page Visits” feature was active. To ensure the privacy and security of your users, we recommend removing these User IDs from your database.</p>
                    <p><b>How to Remove User IDs?</b></p>
                    <ol>
                        <li>Go to the <b>Optimization</b> tab in the WP Statistics settings.</li>
                        <li>Click on <b>Data Cleanup</b>.</li>
                        <li>Select <b>Remove User IDs</b> to start the removal process.</li>
                    </ol>
                    <p>Initiating this process will delete all previously stored User IDs, further securing user data and aligning your site with best privacy practices.</p>
                    <p><b>Need More Information?</b></p>
                    <p>For step-by-step instructions and additional details on the importance of removing User IDs, please consult our guide: <a target="_blank" href="https://wp-statistics.com/resources/removing-user-ids-from-your-database/?utm_source=wp-statistics&utm_medium=link&utm_campaign=privacy">Removing User IDs from Your Database.</a>.</p>
                    ', 'wp-statistics'),
                'compliance'    => [
                    'key'   => 'action_required',
                    'value' => esc_html__('Action Required', 'wp-statistics'),
                ]
            ]
        ];
    }
}