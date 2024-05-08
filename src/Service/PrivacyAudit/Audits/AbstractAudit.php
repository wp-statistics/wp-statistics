<?php 
namespace WP_Statistics\Service\PrivacyAudit\Audits;

abstract class AbstractAudit 
{
    abstract static public function getStatus();
    
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
}