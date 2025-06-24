<?php

namespace WP_Statistics\Records;

use WP_Statistics\Abstracts\BaseRecord;
use WP_STATISTICS\TimeZone;
use WP_Statistics\Utils\Query;

/**
 * Handles database interactions for the `sessions` table.
 *
 * Provides methods to retrieve sessions by indexed fields such as visitor, country, device, and more.
 *
 * @since 15.0.0
 */
class SessionRecord extends BaseRecord
{
    /**
     * The current table name.
     *
     * @var string
     */
    protected $tableName = 'sessions';
}
