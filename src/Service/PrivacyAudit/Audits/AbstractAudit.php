<?php 
namespace WP_Statistics\Service\PrivacyAudit\Audits;

use WP_Statistics\Service\PrivacyAudit\PrivacyStatusOption;

abstract class AbstractAudit 
{
    static public $optionKey;

    abstract static public function getStates();
        
    static public function getState() 
    {
        $states = static::getStates();
        $status = static::getStatus();

        $currentState = isset($states[$status]) ? $states[$status] : null;
        return $currentState;
    }

    static public function hasAction()
    {
        $states     = static::getStates();
        $hasAction  = array_filter(array_column($states, 'action'));
        return !empty($hasAction) ? true : false;
    }

    public static function getStatus()
    {
        return PrivacyStatusOption::get(static::$optionKey, 'action_required');
    }

    public static function resolve()
    {
        PrivacyStatusOption::update(static::$optionKey, 'passed');
    }

    public static function undo()
    {
        PrivacyStatusOption::update(static::$optionKey, 'action_required');
    }
}