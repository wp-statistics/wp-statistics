<?php 
namespace WP_Statistics\Service\PrivacyAudit\Faqs;

abstract class AbstractFaq 
{
    abstract static public function getStatus();
    
    abstract static public function getStates();
    
    static public function getState() {
        $states = static::getStates();
        $status = static::getStatus();

        return $states[$status];
    }
}