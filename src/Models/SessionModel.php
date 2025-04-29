<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Utils\Query;
use WP_STATISTICS\TimeZone;

/**
 * Model class for performing database operations related to visitor sessions.
 *
 * Provides methods to query and interact with the sessions table.
 */
class SessionModel extends BaseModel
{
    /**
     * Find an open session for a visitor that was started today.
     *
     * @param array $args {
     *     @type int $visitor_id Visitor ID to search for.
     * }
     * @return object|null
     */
    public function getTodaySession($args = [])
    {
        $args = $this->parseArgs($args, [
            'visitor_id' => 0
        ]);

        if (empty($args['visitor_id'])) {
            return null;
        }

        $today = TimeZone::getCurrentDate('Y-m-d');

        return Query::select('*')
            ->from('sessions')
            ->where('visitor_id', '=', $args['visitor_id'])
            ->where('started_at', '>=', $today . ' 00:00:00')
            ->where('started_at', '<=', $today . ' 23:59:59')
            ->perPage(1)
            ->getRow();
    }
}
