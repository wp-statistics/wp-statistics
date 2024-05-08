<?php 
namespace WP_Statistics\Service\PrivacyAudit\Audits;

use WP_Statistics\Service\PrivacyAudit\PrivacyStatusOption;
use WP_STATISTICS\Option;

abstract class AbstractAudit 
{
    static public $optionKey;

    /**
     * Returns an array of all states with details an audit item could have.
     * 
     * @return array
     */
    abstract static public function getStates();
        
    /**
     * Returns the current state details of the audit item based on its current status
     * 
     * @return array
     */
    static public function getState() 
    {
        $states = static::getStates();

        // If audit related option is not enabled, set status to 'passed' otherwise get independent status of the audit
        $status = self::isOptionEnabled() ? 'passed' : self::getStatus();

        $currentState = isset($states[$status]) ? $states[$status] : null;
        return $currentState;
    }

    /**
     * Returns a boolean value indicating whether the audit item has any action or not
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
     * If the audit is resolved from Privacy Audit page, return 'passed' otherwise 'action_required'.
     * 
     * @return string
     */
    public static function getStatus()
    {
        return PrivacyStatusOption::get(static::$optionKey, 'action_required');
    }

    /**
     * Based on the `wp_statistics_options` option, return the privacy status of the audit item. 
     * If the option is enabled in the settings, returns 'passed', otherwise 'action_required'
     * 
     * @return string
     */
    public static function getStatusByOption()
    {
        return self::isOptionEnabled() ? 'passed' : 'action_required';
    }

    /**
     * If the option related to the audit item is enabled in the settings return true, otherwise false
     * 
     * @return bool
     */
    public static function isOptionEnabled()
    {
        return Option::get(static::$optionKey) == true;
    }

    /**
     * Update audit item status to 'passed' when resolve button is clicked.
     */
    public static function resolve()
    {
        PrivacyStatusOption::update(static::$optionKey, 'passed');
    }

    /**
     * Update audit item status to 'action_required' when undo button is clicked.
     */
    public static function undo()
    {
        PrivacyStatusOption::update(static::$optionKey, 'action_required');
    }
}