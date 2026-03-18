<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Service\Tracking\Pipeline\Visitor;

/**
 * Factory class to create entity instances tied to a Visitor.
 *
 * Each method initializes and returns a corresponding entity using a given Visitor instance.
 *
 * @since 15.0.0
 */
class EntityFactory
{
    /**
     * Create a new Device entity for the given context.
     *
     * @param Visitor $visitor Hit context instance.
     * @return Device
     */
    public static function device(Visitor $visitor)
    {
        return new Device($visitor);
    }

    /**
     * Create a new Visitor entity for the given context.
     *
     * @param Visitor $visitor Hit context instance.
     * @return Visitor
     */
    public static function visitor(Visitor $visitor)
    {
        return new Visitor($visitor);
    }

    /**
     * Create a new Session entity for the given context.
     *
     * @param Visitor $visitor Hit context instance.
     * @return Session
     */
    public static function session(Visitor $visitor)
    {
        return new Session($visitor);
    }

    /**
     * Create a new Geo entity for the given context.
     *
     * @param Visitor $visitor Hit context instance.
     * @return Geo
     */
    public static function geo(Visitor $visitor)
    {
        return new Geo($visitor);
    }

    /**
     * Create a new Locale entity for the given context.
     *
     * @param Visitor $visitor Hit context instance.
     * @return Locale
     */
    public static function locale(Visitor $visitor)
    {
        return new Locale($visitor);
    }

    /**
     * Create a new Referrer entity for the given context.
     *
     * @param Visitor $visitor Hit context instance.
     * @return Referrer
     */
    public static function referrer(Visitor $visitor)
    {
        return new Referrer($visitor);
    }

    /**
     * Create a new View entity for the given context.
     *
     * @param Visitor $visitor Hit context instance.
     * @return View
     */
    public static function view(Visitor $visitor)
    {
        return new View($visitor);
    }

    /**
     * Create a new Parameter entity for the given context.
     *
     * @param Visitor $visitor Hit context instance.
     * @return Parameter
     */
    public static function parameter(Visitor $visitor)
    {
        return new Parameter($visitor);
    }
}
