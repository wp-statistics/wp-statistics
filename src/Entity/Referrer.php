<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Records\ReferrerRecord;

/**
 * Entity for detecting and recording visitor referrer information.
 *
 * This includes referrer URL, domain, channel (e.g., organic, paid, direct),
 * and source name based on the detected referral source.
 */
class Referrer extends BaseEntity
{
    /**
     * Detect and record visitor referrer details.
     *
     * Extracts the domain, source name, and channel from the referrer URL,
     * and saves them in the database for future lookup.
     *
     * @return $this
     */
    public function recordReferrer()
    {
        $refUrl = $this->profile->getReferrer();
        if (empty($refUrl)) {
            return $this;
        }

        $domain = parse_url($refUrl, PHP_URL_HOST) ?: $refUrl;

        $source  = $this->profile->getSource();
        $channel = method_exists($source, 'getChannel')
            ? $source->getChannel()
            : '';
        $name    = method_exists($source, 'getName')
            ? $source->getName()
            : $domain;

        if (empty($name)) {
            return $this;
        }

        // Cache key to prevent duplicate lookups/inserts in one request
        $cacheKey = 'referrer_' . md5($channel . '|' . $name . '|' . $domain);

        $referrerId = $this->getCachedData($cacheKey, function () use ($channel, $name, $domain) {
            $model  = new ReferrerRecord();
            $record = $model->get([
                'channel' => $channel,
                'name'    => $name,
                'domain'  => $domain,
            ]);

            if (!empty($record) && isset($record->ID)) {
                return (int)$record->ID;
            }

            return (int)$model->insert([
                'channel' => $channel,
                'name'    => $name,
                'domain'  => $domain,
            ]);
        });

        $this->profile->setReferrerId($referrerId);
        return $this;
    }
}
