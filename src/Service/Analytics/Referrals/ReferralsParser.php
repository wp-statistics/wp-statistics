<?php

namespace WP_Statistics\Service\Analytics\Referrals;

use WP_Statistics\Utils\Url;

class ReferralsParser
{
    private $referralsList;

    public function __construct()
    {
        $referralsDatabase   = new ReferralsDatabase();
        $this->referralsList = $referralsDatabase->getList();
    }

    /**
     * Parse the given URL to determine the referral source.
     *
     * @param string $url The URL to parse.
     * @return array An array containing the referral source information.
     */
    public function parse($url)
    {
        $domain = Url::getDomain($url);

        foreach ($this->referralsList['source_channels'] as $channelType => $channelData) {
            foreach ($channelData['channels'] as $channel) {
                foreach ($channel['domains'] as $channelDomain) {
                    if ($channelDomain === $domain) {
                        return [
                            'name'          => $channel['name'],
                            'identifier'    => $channel['identifier'],
                            'domain'        => $channelDomain,
                            'channel'       => $channelType
                        ];
                    }
                }
            }
        }

        // Fallback to direct
        return [
            'name'          => '',
            'identifier'    => '',
            'domain'        => $domain,
            'channel'       => 'direct'
        ];
    }
}