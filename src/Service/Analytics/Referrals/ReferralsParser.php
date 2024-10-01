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
     * @param string $referrerUrl The referrer URL to parse.
     * @param string $pageUrl    The URL of the page being accessed.
     *
     * @return array|bool An array containing the referral source information. False if no match is found, or is self-referral.
     */
    public function parse($referrerUrl, $pageUrl)
    {
        $referrerUrl = Url::getDomain($referrerUrl);

        // Return false if self referral
        if (Url::isInternal($referrerUrl)) return false;

        foreach ($this->referralsList['source_channels'] as $channelType => $channelData) {
            // check if rules don't match, skip to the next channel
            if (!$this->checkRules($channelData['rules'], $pageUrl)) {
                continue;
            }

            foreach ($channelData['channels'] as $channel) {
                foreach ($channel['domains'] as $channelDomain) {

                    // check if domains don't match, skip
                    if ($channelDomain !== $referrerUrl) {
                        continue;
                    }

                    return [
                        'name'         => $channel['name'],
                        'identifier'   => $channel['identifier'],
                        'channel'      => $channelType
                    ];
                }
            }
        }

        return false;
    }

    /**
     * Checks if a given page URL passes a set of rules.
     *
     * @param array $rules An array of regular expression patterns to match against the page URL.
     * @param string $pageUrl The URL of the page to check against the rules.
     *
     * @return bool true if the page URL passes all rules, false otherwise.
     */
    public function checkRules($rules, $pageUrl)
    {
        // If pageUrl is empty, set it to empty string
        $pageUrl = !empty($pageUrl) ? $pageUrl : '';

        foreach ($rules as $rule) {
            switch ($rule['operator']) {
                case 'MATCH':
                    if (!preg_match($rule['pattern'], $pageUrl)) return false;
                    break;

                case 'NOT MATCH':
                    if (preg_match($rule['pattern'], $pageUrl)) return false;
                    break;
            }
        }

        return true;
    }
}