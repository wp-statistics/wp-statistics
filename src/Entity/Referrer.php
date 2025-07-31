<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Records\RecordFactory;

/**
 * Entity for detecting and recording visitor referrer information.
 *
 * This includes referrer URL, domain, channel (e.g., organic, paid, direct),
 * and source name based on the detected referral source.
 *
 * @since 15.0.0
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
    public function record()
    {
        if (!$this->isActive('referrers')) {
            return $this;
        }

        $domain  = $this->profile->getReferrer();
        $source  = $this->profile->getSource();
        $channel = $source->getChannel();
        $name    = $source->getName();

        if (empty($channel) || empty($domain) || empty($name)) {
            return $this;
        }

        $record = RecordFactory::referrer()->get([
            'channel' => $channel,
            'name'    => $name,
            'domain'  => $domain,
        ]);

        if (!empty($record) && isset($record->ID)) {
            $this->profile->setReferrerId((int)$record->ID);
            return $this;
        }

        $referrerId = RecordFactory::referrer()->insert([
            'channel' => $channel,
            'name'    => $name,
            'domain'  => $domain,
        ]);

        $this->profile->setReferrerId($referrerId);
        return $this;
    }
}
