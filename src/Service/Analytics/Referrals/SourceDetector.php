<?php

namespace WP_Statistics\Service\Analytics\Referrals;

class SourceDetector
{
    public $referral;

    public function __construct($url)
    {
        $parser         = new ReferralsParser();
        $this->referral = $parser->parse($url);
    }

    public function getName()
    {
        return $this->referral['name'];
    }

    public function getIdentifier()
    {
        return $this->referral['identifier'];
    }

    public function getChannel()
    {
        return $this->referral['channel'];
    }

    public function getDomain()
    {
        return $this->referral['domain'];
    }
}