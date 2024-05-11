<?php 
namespace WP_Statistics\Service\PrivacyAudit\Audits\Abstracts;


abstract class BaseAudit 
{

    /**
     * Get the privacy status of the audit item. This status will be used to get the current state of the audit.
     * @return string
     */
    abstract public static function getStatus();

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

}