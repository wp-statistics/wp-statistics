<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Service\Tracking\Core\Visitor as VisitorData;

/**
 * Factory class to create entity instances tied to a VisitorData.
 *
 * Each method initializes and returns a corresponding entity using a given VisitorData instance.
 *
 * @since 15.0.0
 */
class EntityFactory
{
    /**
     * Create a new Device entity for the given context.
     *
     * @param VisitorData $visitor Resolved visitor data.
     * @return Device
     */
    public static function device(VisitorData $visitor)
    {
        return new Device($visitor);
    }

    /**
     * Create a new Visitor entity for the given context.
     *
     * @param VisitorData $visitor Resolved visitor data.
     * @return Visitor
     */
    public static function visitor(VisitorData $visitor)
    {
        return new Visitor($visitor);
    }

    /**
     * Create a new Session entity for the given context.
     *
     * @param VisitorData $visitor Resolved visitor data.
     * @return Session
     */
    public static function session(VisitorData $visitor)
    {
        return new Session($visitor);
    }

    /**
     * Create a new Geo entity for the given context.
     *
     * @param VisitorData $visitor Resolved visitor data.
     * @return Geo
     */
    public static function geo(VisitorData $visitor)
    {
        return new Geo($visitor);
    }

    /**
     * Create a new Locale entity for the given context.
     *
     * @param VisitorData $visitor Resolved visitor data.
     * @return Locale
     */
    public static function locale(VisitorData $visitor)
    {
        return new Locale($visitor);
    }

    /**
     * Create a new Referrer entity for the given context.
     *
     * @param VisitorData $visitor Resolved visitor data.
     * @return Referrer
     */
    public static function referrer(VisitorData $visitor)
    {
        return new Referrer($visitor);
    }

    /**
     * Create a new View entity for the given context.
     *
     * @param VisitorData $visitor Resolved visitor data.
     * @return View
     */
    public static function view(VisitorData $visitor)
    {
        return new View($visitor);
    }

    /**
     * Create a new Parameter entity for the given context.
     *
     * @param VisitorData $visitor Resolved visitor data.
     * @return Parameter
     */
    public static function parameter(VisitorData $visitor)
    {
        return new Parameter($visitor);
    }
}
