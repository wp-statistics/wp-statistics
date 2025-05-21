<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;
use WP_Statistics\Utils\Query;

/**
 * Handles database interactions for the `referrers` table.
 *
 * This class provides convenience methods for retrieving referrers
 * by channel, name, or domain.
 */
class ReferrerRecord extends BaseRecord
{
   /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'referrers';

    /**
     * Get all referrers by channel.
     *
     * @param string $channel The channel value to filter by.
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByChannel($channel)
    {
        return empty($channel) ? [] : $this->getAll(['channel' => $channel]);
    }

    /**
     * Get all referrers by name.
     *
     * @param string $name The name value to filter by.
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByName($name)
    {
        return empty($name) ? [] : $this->getAll(['name' => $name]);
    }

    /**
     * Get all referrers by domain.
     *
     * @param string $domain The domain value to filter by.
     * @return array
     * @todo This method is a sample usage; may be updated or removed based on future needs.
     */
    public function getAllByDomain($domain)
    {
        if (empty($domain)) {
            return [];
        }

        return Query::select('*')
            ->from($this->tableName)
            ->where('domain', 'LIKE', '%' . $domain . '%')
            ->getAll();
    }
}
