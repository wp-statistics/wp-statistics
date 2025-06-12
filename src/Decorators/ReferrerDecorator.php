<?php

namespace WP_Statistics\Decorators;

use WP_Statistics\Service\Analytics\Referrals\SourceChannels;
use WP_Statistics\Utils\Url;

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
     * Get the source channel name (e.g., Direct, Organic Search, etc.).
     *
     * @return string|null
     */
    public function getSourceChannel()
    {
        return SourceChannels::getName($this->getRawSourceChannel());
    }

    /**
     * Get the raw source channel value (e.g., direct, search, etc.).
     *
     * @return string|null
     */
    public function getRawSourceChannel()
    {
        return empty($this->referrer->channel) ? 'unassigned' : $this->referrer->channel;
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

    /**
     * Get the referrer url.
     *
     * @return string|null
     */
    public function getReferrer()
    {
        $domain = $this->getDomain();
        return $domain ? Url::formatUrl($domain) : null;
    }

    /**
     * Get the raw referrer value.
     * For backward compatibility.
     *
     * @return string|null
     */
    public function getRawReferrer()
    {
        return $this->getDomain();
    }

    /**
     * Get the total number of referrals.
     *
     * @param bool $raw Whether return raw value or formatted.
     * @return int|string
     */
    public function getTotalReferrals($raw = false)
    {
        if (empty($this->referrer->visitors)) {
            return 0;
        }

        return $raw ? intval($this->referrer->visitors) : number_format_i18n($this->referrer->visitors);
    }
}
