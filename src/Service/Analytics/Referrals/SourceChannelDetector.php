<?php

namespace WP_Statistics\Service\Analytics\Referrals;

class SourceChannelDetector
{
    public $currentChannel;

    public function __construct($referrer)
    {
    }

    /**
     * Check if the current traffic source is organic.
     */
    public function isOrganic()
    {
        // TODO: regex check UTM parameters
        return $this->currentChannel['category'] === SourceChannels::SEARCH;
    }

    /**
     * Check if the current traffic source is from paid search.
     */
    public function isPaidSearch()
    {
        return $this->currentChannel['category'] === SourceChannels::PAID_SEARCH;
    }

    /**
     * Check if the current traffic source is from social media.
     */
    public function isSocialMedia()
    {
        return $this->currentChannel['category'] === SourceChannels::SOCIAL_MEDIA;
    }

    /**
     * Check if the current traffic source is from paid social media.
     */
    public function isPaidSocialMedia()
    {
        return $this->currentChannel['category'] === SourceChannels::PAID_SOCIAL_MEDIA;
    }

    /**
     * Check if the current traffic source is from referral sites.
     */
    public function isReferral()
    {
        return $this->currentChannel['category'] === SourceChannels::REFERRAL_SITES;
    }

    /**
     * Check if the current traffic source is direct.
     */
    public function isDirect()
    {
        return $this->currentChannel['category'] === SourceChannels::DIRECT;
    }
}