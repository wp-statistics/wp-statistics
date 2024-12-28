<?php
namespace WP_Statistics\Service\Admin\PrivacyAudit\Faqs;

abstract class AbstractFaq
{
    abstract public static function getStatus();

    abstract public static function getStates();

    public static function getState() {
        $states = static::getStates();
        $status = static::getStatus();

        return $states[$status];
    }
}