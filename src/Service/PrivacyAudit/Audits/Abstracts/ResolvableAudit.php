<?php 
namespace WP_Statistics\Service\PrivacyAudit\Audits\Abstracts;

use WP_Statistics\Service\PrivacyAudit\PrivacyStatusOption;
use WP_STATISTICS\Option;

abstract class ResolvableAudit extends BaseAudit
{
    static public $optionKey;

    /**
     * Returns the content of audit in passed state.
     * 
     * @return array
     */
    abstract static public function getPassedStateInfo();

    /**
     * Returns the content of audit in when it is not in passed state.
     * 
     * @return array
     */
    abstract static public function getUnpassedStateInfo();
        
    /**
     * Get the privacy status of the audit item stored in `wp_statistics_privacy_status` option.
     * If audit related option is passed, return 'passed' regardless of the independent status of the audit item.
     * 
     * @return string
     */
    public static function getStatus()
    {
        // Get the independent status of the audit item
        $status = PrivacyStatusOption::get(static::$optionKey, 'action_required');
        
        // If audit related option is passed based on user settings, set status to 'passed' 
        if (static::isOptionPassed()) {
            $status = 'passed';
        }

        return $status;
    }


    /**
     * If the option related to the audit item is enabled in the settings return true, otherwise false.
     * 
     * @return bool
     */
    public static function isOptionEnabled()
    {
        return Option::get(static::$optionKey) == true;
    }

    /**
     * Returns true when audit option value is considered passed, false otherwise. By default if option is true, is it considered passed.
     * 
     * @return bool
     */
    static public function isOptionPassed() 
    {
        return self::isOptionEnabled();
    }

    /**
     * Update audit item status to 'resolved' when resolve button is clicked.
     */
    public static function resolve()
    {
        PrivacyStatusOption::update(static::$optionKey, 'resolved');
    }

    /**
     * Update audit item status to 'action_required' when undo button is clicked.
     */
    public static function undo()
    {
        PrivacyStatusOption::update(static::$optionKey, 'action_required');
    }

    /**
     * Returns an array of all states an audit item could have with details.
     * 
     * @return array
     */
    static public function getStates()
    {
        $passedInfo     = static::getPassedStateInfo();
        $unpassedInfo   = static::getUnpassedStateInfo();

        return [
            'passed' => [
                'status'     => 'success',
                'title'      => $passedInfo['title'],
                'notes'      => $passedInfo['notes'],
                'compliance' => [
                    'key'   => 'passed',
                    'value' => esc_html__('Passed', 'wp-statistics'),
                ],
            ],
            'resolved' => [
                'status'        => 'success',
                'title'         => $unpassedInfo['title'],
                'notes'         => $unpassedInfo['notes'],
                'compliance'    => [
                    'key'   =>'resolved',
                    'value' => esc_html__('Resolved', 'wp-statistics'),
                ],
                'action'    => [
                    'key' => 'undo', 
                    'value' => esc_html__('Undo', 'wp-statistics')
                ]
            ],
            'action_required' => [
                'status'        => 'warning',
                'title'         => $unpassedInfo['title'],
                'notes'         => $unpassedInfo['notes'],
                'compliance'    => [
                    'key'   => 'action_required',
                    'value' => esc_html__('Action Required', 'wp-statistics'),
                ],
                'action'        => [
                    'key'   => 'resolve',
                    'value' => esc_html__('Resolve', 'wp-statistics'),
                ]
            ]
        ];
    }
}