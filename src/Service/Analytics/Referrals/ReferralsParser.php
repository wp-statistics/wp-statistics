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
        $channels = [];

        $referrerUrl = Url::getDomain($referrerUrl);

        // Get a list of all source parameters to check
        $sourceParams = array_filter([
            'utm_source'    => Url::getParam($pageUrl, 'utm_source'),
            'source'        => Url::getParam($pageUrl, 'source'),
            'ref'           => Url::getParam($pageUrl, 'ref')
        ]);

        foreach ($this->referralsList as $channelData) {
            // "fallback" groups only apply when nothing else matched (see getFallbackSource()).
            if (!empty($channelData['fallback'])) {
                continue;
            }

            // check if rules don't match, skip to the next channel
            if (!$this->checkRules($channelData['rules'], $pageUrl, $referrerUrl)) {
                continue;
            }

            // A "rules_only" group is identified by its rules alone (for example an ad click id
            // in the landing URL, which often comes without a referrer): when its rules match,
            // its first channel is the source, whatever the referrer or utm_source says.
            if (!empty($channelData['rules_only']) && !empty($channelData['channels'][0])) {
                return [
                    'name'       => $channelData['channels'][0]['name'],
                    'identifier' => $channelData['channels'][0]['identifier'],
                    'channel'    => $channelData['type'],
                ];
            }

            foreach ($channelData['channels'] as $channel) {
                $currentChannel = [
                    'name'         => $channel['name'],
                    'identifier'   => $channel['identifier'],
                    'channel'      => $channelData['type']
                ];

                foreach ($channel['domains'] as $channelDomain) {
                    // Check if the current source matches any of the source parameters
                    foreach ($sourceParams as $key => $value) {
                        if ($this->checkDomain($channelDomain, $value)) {

                            // Set the source channel if not already set
                            if (empty($channels[$key])) {
                                $channels[$key] = $currentChannel;

                                // Set the source name if the domain is wildcard
                                if ($channelDomain == '*') {
                                    $channels[$key]['name'] = sanitize_text_field($value);
                                }
                            }

                        }
                    }

                    // Check if the current source matches the referrer
                    if ($this->checkDomain($channelDomain, $referrerUrl)) {
                        // Set the source channel if not already set
                        $channels['referrer'] = empty($channels['referrer']) ? $currentChannel : $channels['referrer'];
                    }

                    // Break if all available params and referrer have channels
                    if (count($channels) === count($sourceParams) + 1) {
                        break 3;
                    }
                }
            }
        }

        $source = $this->getSourceInfo($channels);

        if ($source === false && empty($referrerUrl)) {
            $source = $this->getFallbackSource($pageUrl, $referrerUrl);
        }

        return $source;
    }

    /**
     * Returns the first "fallback" group whose rules match.
     *
     * Fallback groups cover visits that carry only an ad or social click id
     * (gclid, gad_source, fbclid, ...) with no referrer and no known utm_source.
     * They are only checked when there is no referrer at all.
     * Without them such visits matched no channel and were stored with an empty
     * source channel.
     *
     * @param string $pageUrl
     * @param string $referrerDomain
     *
     * @return array|bool
     */
    private function getFallbackSource($pageUrl, $referrerDomain)
    {
        foreach ($this->referralsList as $channelData) {
            if (empty($channelData['fallback']) || empty($channelData['channels'][0])) {
                continue;
            }

            if (!$this->checkRules($channelData['rules'], $pageUrl, $referrerDomain)) {
                continue;
            }

            return [
                'name'       => $channelData['channels'][0]['name'],
                'identifier' => $channelData['channels'][0]['identifier'],
                'channel'    => $channelData['type'],
            ];
        }

        return false;
    }

    /**
     * Returns the source info based on the provided channels and priority.
     *
     * @param array $channels
     *
     * @return array|bool
     */
    private function getSourceInfo($channels)
    {
        if (!empty($channels['utm_source'])) {
            return $channels['utm_source'];
        }

        if (!empty($channels['source'])) {
            return $channels['source'];
        }

        if (!empty($channels['ref'])) {
            return $channels['ref'];
        }

        if (!empty($channels['referrer'])) {
            return $channels['referrer'];
        }

        return false;
    }

    /**
     * Match a domain against a channel domain. The pattern can contain * as a wildcard character.
     *
     * @param string $pattern
     * @param string $domain
     *
     * @return bool
     */
    private function checkDomain($pattern, $domain)
    {
        // If the pattern doesn't contain wildcards, perform a simple comparison
        if (strpos($pattern, '*') === false) {
            return strtolower($pattern) === strtolower($domain);
        }

        // Convert wildcard to regex pattern
        $pattern = str_replace('\*', '.*', preg_quote($pattern, '/'));

        return preg_match('/^' . $pattern . '$/i', $domain) === 1;
    }

    /**
     * Checks if a given page URL (and referrer domain) passes a set of rules.
     *
     * MATCH and NOT MATCH test the page URL. REFERRER MATCH tests the referrer domain,
     * without "www." (for example "l.facebook.com"), and fails when there is no referrer.
     *
     * @param array $rules An array of regular expression patterns to match against the page URL.
     * @param string $pageUrl The URL of the page to check against the rules.
     * @param string $referrerDomain The referrer domain, as returned by Url::getDomain().
     *
     * @return bool true if the page URL passes all rules, false otherwise.
     */
    public function checkRules($rules, $pageUrl, $referrerDomain = '')
    {
        // If pageUrl is empty, set it to empty string
        $pageUrl        = !empty($pageUrl) ? strtolower($pageUrl) : '';
        $referrerDomain = !empty($referrerDomain) ? strtolower($referrerDomain) : '';

        foreach ($rules as $rule) {
            switch ($rule['operator']) {
                case 'MATCH':
                    if (!preg_match($rule['pattern'], $pageUrl)) return false;
                    break;

                case 'NOT MATCH':
                    if (preg_match($rule['pattern'], $pageUrl)) return false;
                    break;

                case 'REFERRER MATCH':
                    if ($referrerDomain === '' || !preg_match($rule['pattern'], $referrerDomain)) return false;
                    break;
            }
        }

        return true;
    }
}