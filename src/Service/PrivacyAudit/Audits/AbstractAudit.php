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
}