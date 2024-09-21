<?php

namespace WP_Statistics\Decorators;

use WP_Statistics\Service\Analytics\Referrals\SourceChannels;
use WP_Statistics\Utils\Url;

class ReferralDecorator
{
    private $visitor;

    public function __construct($visitor)
    {
        $this->visitor = $visitor;
    }

    /**
     * Get the visitor's raw referrer value.
     *
     * @return string|null
     */
    public function getRawReferrer()
    {
        return $this->visitor->referred ?? null;
    }

    /**
     * Get the visitor's referrer url.
     *
     * @return string|null
     */
    public function getReferrer()
    {
        return $this->visitor->referred ? Url::formatUrl($this->visitor->referred) : null;
    }

    /**
     * Get the visitor's source channel (e.g., direct, search, etc.).
     *
     * @return string|null
     */
    public function getSourceChannel()
    {
        return SourceChannels::getName($this->visitor->source_channel) ?? null;
    }

    /**
     * Get the visitor's source name (e.g., Google, Yandex, etc.).
     *
     * @return string|null
     */
    public function getSourceName()
    {
        return $this->visitor->source_name ?? null;
    }
}