<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Service\Analytics\VisitorProfile;

/**
 * Factory class to create entity instances tied to a visitor profile.
 *
 * Each method initializes and returns a corresponding entity using a given VisitorProfile instance.
 */
class EntityFactory
{
    /**
     * Create a new Device entity for the given visitor profile.
     *
     * @param VisitorProfile $profile Visitor profile instance.
     * @return Device
     */
    public static function device(VisitorProfile $profile)
    {
        return new Device($profile);
    }

    /**
     * Create a new Visitor entity for the given visitor profile.
     *
     * @param VisitorProfile $profile Visitor profile instance.
     * @return Visitor
     */
    public static function visitor(VisitorProfile $profile)
    {
        return new Visitor($profile);
    }

    /**
     * Create a new Session entity for the given visitor profile.
     *
     * @param VisitorProfile $profile Visitor profile instance.
     * @return Session
     */
    public static function session(VisitorProfile $profile)
    {
        return new Session($profile);
    }

    /**
     * Create a new Geo entity for the given visitor profile.
     *
     * @param VisitorProfile $profile Visitor profile instance.
     * @return Geo
     */
    public static function geo(VisitorProfile $profile)
    {
        return new Geo($profile);
    }

    /**
     * Create a new Locale entity for the given visitor profile.
     *
     * @param VisitorProfile $profile Visitor profile instance.
     * @return Locale
     */
    public static function locale(VisitorProfile $profile)
    {
        return new Locale($profile);
    }

    /**
     * Create a new Referrer entity for the given visitor profile.
     *
     * @param VisitorProfile $profile Visitor profile instance.
     * @return Referrer
     */
    public static function referrer(VisitorProfile $profile)
    {
        return new Referrer($profile);
    }

    /**
     * Create a new View entity for the given visitor profile.
     *
     * @param VisitorProfile $profile Visitor profile instance.
     * @return View
     */
    public static function view(VisitorProfile $profile)
    {
        return new View($profile);
    }

    /**
     * Create a new Parameter entity for the given visitor profile.
     *
     * @param VisitorProfile $profile Visitor profile instance.
     * @return Parameter
     */
    public static function parameter(VisitorProfile $profile)
    {
        return new Parameter($profile);
    }
}
