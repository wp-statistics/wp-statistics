<?php 
namespace WP_Statistics\Service\PrivacyAudit\Actions;

abstract class AbstractPrivacyAction 
{
    abstract static public function getStatus();

    abstract static public function getState();

    abstract static public function getStates();
}