<?php

namespace WP_Statistics\Service\Analytics\Referrals;

class SourceDetector
{
    public $referral;

    public function __construct($referrerUrl, $pageUrl)
    {
        $parser         = new ReferralsParser();
        $this->referral = $parser->parse($referrerUrl, $pageUrl);
    }

    /**
     * Returns the name of the referral source.
     *
     * @return string
     */
    public function getName()
    {
        return $this->referral['name'] ?? null;
    }

    /**
     * Returns the identifier of the referral.
     *
     * @return string referral identifier
     */
    public function getIdentifier()
    {
        return $this->referral['identifier'] ?? null;
    }

    /**
     * Returns the channel of the referral source.
     *
     * @return string The channel of the referral source.
     */
    public function getChannel()
    {
        return $this->referral['channel'] ?? null;
    }

    /**
     * Returns the human-readable channel name of the referral source.
     *
     * @return string The channel of the referral source.
     */
    public function getChannelName()
    {
        $sourceChannel = $this->referral['channel'] ?? 'unassigned';

        return SourceChannels::getName($sourceChannel);
    }
}