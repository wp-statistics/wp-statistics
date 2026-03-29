<?php

namespace WP_Statistics\Service\CustomEvent;

/**
 * @deprecated Use WP_Statistics\Pro\Modules\EventTracker\EventRegistry
 *             and WP_Statistics\Pro\Modules\EventTracker\EventValidator instead.
 */
class CustomEventHelper
{
    /**
     * @deprecated Use EventRegistry::getAll()
     * @return array
     */
    public static function getCustomEvents()
    {
        return [];
    }

    /**
     * @deprecated Use EventRegistry::getActive()
     * @return string[]
     */
    public static function getActiveCustomEvents()
    {
        return [];
    }

    /**
     * @deprecated Use EventRegistry::isActive()
     * @param string $name
     * @return bool
     */
    public static function isEventActive($name)
    {
        return false;
    }

    /**
     * @deprecated Use EventRegistry::findByName()
     * @param string $name
     * @return array|null
     */
    public static function findEventByName(string $name): ?array
    {
        return null;
    }

    /**
     * @deprecated Use EventValidator::validateName()
     * @param string $name
     * @param string|null $excludeSource
     * @return array
     */
    public static function validateEventName(string $name, ?string $excludeSource = null): array
    {
        return ['valid' => true];
    }

    /**
     * @deprecated Use EventValidator::isReserved()
     * @param string $name
     * @return bool
     */
    public static function isEventNameReserved($name)
    {
        return false;
    }

    /**
     * @deprecated Use EventValidator::validateName() instead.
     * @param string $name
     * @return bool
     */
    public static function isEventNameValid($name)
    {
        return true;
    }

    /**
     * @deprecated Use EventValidator::getReservedNames()
     * @return string[]
     */
    public static function getReservedEventNames()
    {
        return [];
    }
}
