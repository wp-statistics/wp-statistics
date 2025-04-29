<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Utils\Query;

/**
 * Model class for performing database operations related to views.
 *
 * Provides methods to query and retrieve view records.
 */
class ViewModel extends BaseModel
{
     /**
     * Retrieve the most recent view record for a given session ID.
     *
     * @param array $args {
     *     @type int $session_id Required. The session ID to fetch the latest view for.
     * }
     *
     * @return object|null
     */
    public function getLastViewBySessionId($args = [])
    {
        $args = $this->parseArgs($args, [
            'session_id' => 0
        ]);

        if (empty($args['session_id'])) {
            return null;
        }
    
        $query = Query::select('*')
            ->from('views')
            ->where('session_id', '=', $args['session_id'])
            ->orderBy('ID', 'DESC')
            ->perPage(1);
    
        return $query->getRow();
    }
}
