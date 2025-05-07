<?php

namespace WP_Statistics\Decorators;

/**
 * Decorator for a record from the 'referrers' table.
 *
 * Provides accessors for each column in the 'referrers' schema.
 */
class ReferrerDecorator
{
    /**
     * The referrer record.
     *
     * @var object|null
     */
    private $referrer;

    /**
     * ReferrerDecorator constructor.
     *
     * @param object|null $referrer A stdClass representing a 'referrers' row, or null.
     */
    public function __construct($referrer)
    {
        $this->referrer = $referrer;
    }

    /**
     * Get referrer ID.
     *
     * @return int|null
     */
    public function getId()
    {
        return empty($this->referrer->ID) ? null : (int)$this->referrer->ID;
    }

    /**
     * Get referrer channel.
     *
     * @return string
     */
    public function getChannel()
    {
        return empty($this->referrer->channel) ? '' : $this->referrer->channel;
    }

    /**
     * Get referrer name.
     *
     * @return string
     */
    public function getName()
    {
        return empty($this->referrer->name) ? '' : $this->referrer->name;
    }

    /**
     * Get referrer domain.
     *
     * @return string
     */
    public function getDomain()
    {
        return empty($this->referrer->domain) ? '' : $this->referrer->domain;
    }
}
