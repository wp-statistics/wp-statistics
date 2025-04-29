<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Records\VisitorRecord;
use WP_STATISTICS\TimeZone;

/**
 * Entity for recording or retrieving visitor information based on IP hash.
 *
 * This ensures unique visitor entries, using the hashed IP address
 * as the primary lookup key to avoid duplication.
 */
class Visitor extends BaseEntity
{
    /**
     * Record or retrieve a visitor based on the hashed IP address.
     *
     * - If a visitor exists with the same hash, reuse the visitor ID.
     * - Otherwise, create a new visitor record with the current timestamp.
     *
     * @return $this
     *
     * @todo Improve IP storage: clean up unnecessary prefixes like "#hash#".
     */
    public function record()
    {
        if (! $this->isActive('visitors')) {
            return $this;
        }

        $hash = $this->profile->getProcessedIPForStorage();

        if (empty($hash)) {
            return $this;
        }

        $cacheKey = 'visitor_' . md5($hash);

        $visitorId = $this->getCachedData($cacheKey, function () use ($hash) {
            $model  = new VisitorRecord();
            $record = $model->get(['hash' => $hash]);

            return !empty($record) && isset($record->ID)
                ? (int)$record->ID
                : $model->insert([
                    'hash'       => $hash,
                    'created_at' => TimeZone::getCurrentDate('Y-m-d H:i:s'),
                ]);
        });

        $this->profile->setVisitorId($visitorId);
        return $this;
    }
}
