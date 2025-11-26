<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Records\RecordFactory;

/**
 * Entity for recording or retrieving visitor information based on IP hash.
 *
 * This ensures unique visitor entries, using the hashed IP address
 * as the primary lookup key to avoid duplication.
 *
 * @since 15.0.0
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
        if (!$this->isActive('visitors')) {
            return $this;
        }

        $hash = $this->profile->getHashedIPForStorage();

        if (empty($hash)) {
            return $this;
        }

        $record = RecordFactory::visitor()->get(['hash' => $hash]);

        $visitorId = !empty($record) && isset($record->ID)
            ? (int)$record->ID
            : RecordFactory::visitor()->insert([
                'hash'       => $hash,
                'created_at' => DateTime::getUtc(),
            ]);

        $this->profile->setVisitorId($visitorId);
        return $this;
    }
}
