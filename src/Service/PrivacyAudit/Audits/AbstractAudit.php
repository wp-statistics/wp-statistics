<?php 
namespace WP_Statistics\Service\PrivacyAudit\Audits;

use WP_Statistics\Service\PrivacyAudit\PrivacyStatusOption;
use WP_STATISTICS\Option;

abstract class AbstractAudit 
{
    static public $optionKey;

    /**
     * Returns an array of all states an audit item could have with details.
     * 
     * @return array
     */
    abstract static public function getStates();
        
    /**
     * Returns the current state of the audit item based on its current status
     * 
     * @return array
     */
    static public function getState() 
    {
        $states = static::getStates();
        $status = static::getStatus();

        $currentState = isset($states[$status]) ? $states[$status] : null;
        return $currentState;
    }

    /**
     * Returns a boolean value indicating whether the audit item has any action or not.
     * 
     * @return bool
     */
    static public function hasAction()
    {
        $states     = static::getStates();
        $hasAction  = array_filter(array_column($states, 'action'));
        return !empty($hasAction) ? true : false;
    }

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
}