<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Service\Tracking\Core\HitContext;

/**
 * Factory class to create entity instances tied to a HitContext.
 *
 * Each method initializes and returns a corresponding entity using a given HitContext instance.
 *
 * @since 15.0.0
 */
class EntityFactory
{
    /**
     * Create a new Device entity for the given context.
     *
     * @param HitContext $context Hit context instance.
     * @return Device
     */
    public static function device(HitContext $context)
    {
        return new Device($context);
    }

    /**
     * Create a new Visitor entity for the given context.
     *
     * @param HitContext $context Hit context instance.
     * @return Visitor
     */
    public static function visitor(HitContext $context)
    {
        return new Visitor($context);
    }

    /**
     * Create a new Session entity for the given context.
     *
     * @param HitContext $context Hit context instance.
     * @return Session
     */
    public static function session(HitContext $context)
    {
        return new Session($context);
    }

    /**
     * Create a new Geo entity for the given context.
     *
     * @param HitContext $context Hit context instance.
     * @return Geo
     */
    public static function geo(HitContext $context)
    {
        return new Geo($context);
    }

    /**
     * Create a new Locale entity for the given context.
     *
     * @param HitContext $context Hit context instance.
     * @return Locale
     */
    public static function locale(HitContext $context)
    {
        return new Locale($context);
    }

    /**
     * Create a new Referrer entity for the given context.
     *
     * @param HitContext $context Hit context instance.
     * @return Referrer
     */
    public static function referrer(HitContext $context)
    {
        return new Referrer($context);
    }

    /**
     * Create a new View entity for the given context.
     *
     * @param HitContext $context Hit context instance.
     * @return View
     */
    public static function view(HitContext $context)
    {
        return new View($context);
    }

    /**
     * Create a new Parameter entity for the given context.
     *
     * @param HitContext $context Hit context instance.
     * @return Parameter
     */
    public static function parameter(HitContext $context)
    {
        return new Parameter($context);
    }
}
