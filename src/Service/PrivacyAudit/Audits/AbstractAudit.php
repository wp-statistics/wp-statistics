<?php 
namespace WP_Statistics\Service\PrivacyAudit\Audits;

abstract class AbstractAudit 
{
    abstract static public function getStatus();
    
    abstract static public function getStates();
    
    static public function getState() {
        return static::getStates()[static::getStatus()];
    }
}