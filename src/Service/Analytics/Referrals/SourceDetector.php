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
        return $this->referral['name'];
    }

    /**
     * Returns the identifier of the referral.
     *
     * @return string referral identifier
     */
    public function getIdentifier()
    {
        return $this->referral['identifier'];
    }

    /**
     * Returns the channel of the referral source.
     *
     * @return string The channel of the referral source.
     */
    public function getChannel()
    {
        return $this->referral['channel'];
    }

    /**
     * Returns the domain of the referral source.
     *
     * @return string The domain of the referral source.
     */
    public function getDomain()
    {
        return $this->referral['domain'];
    }
}