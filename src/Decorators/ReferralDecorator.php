<?php

namespace WP_Statistics\Decorators;

use WP_Statistics\Service\Analytics\Referrals\SourceChannels;
use WP_Statistics\Utils\Url;

class ReferralDecorator
{
    private $item;

    public function __construct($item)
    {
        $this->item = $item;
    }

    /**
     * Get the raw referrer value.
     *
     * @return string|null
     */
    public function getRawReferrer()
    {
        return $this->item->referred ?? null;
    }

    /**
     * Get the referrer url.
     *
     * @return string|null
     */
    public function getReferrer()
    {
        return $this->item->referred ? Url::formatUrl($this->item->referred) : null;
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
        return $this->item->source_channel ?? 'unassigned';
    }

    /**
     * Get the source name (e.g., Google, Yandex, etc.).
     *
     * @return string|null
     */
    public function getSourceName()
    {
        return $this->item->source_name ?? null;
    }

    /**
     * Get the date of the referral.
     *
     * @return string
     */
    public function getDate()
    {
        return $this->item->last_counter ?? null;
    }

    /**
     * Get the total number of referrals.
     *
     * @param bool $raw Whether return raw value or formatted.
     * @return int|string
     */
    public function getTotalReferrals($raw = false)
    {
        if (empty($this->item->visitors)) return 0;

        return $raw ? intval($this->item->visitors) : number_format_i18n($this->item->visitors);
    }
}