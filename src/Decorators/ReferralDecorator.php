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
     * Get the source channel (e.g., direct, search, etc.).
     *
     * @return string|null
     */
    public function getSourceChannel()
    {
        return SourceChannels::getName($this->item->source_channel) ?? null;
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
     * Get the total number of referrals.
     *
     * @return int
     */
    public function getTotalReferrals()
    {
        return $this->item->visitors ? number_format_i18n($this->item->visitors) : 0;
    }
}