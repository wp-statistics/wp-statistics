<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\DB;
use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Utils\Query;

class BotActivityModel extends BaseModel
{
    protected $timeframe;

    /**
     * @param array $args {
     *     @type int $timeframe Timeframe in minutes. Default 5.
     * }
     */
    public function __construct($args = [])
    {
        $args = wp_parse_args($args, [
            'timeframe' => 5,
        ]);

        $this->timeframe = [
            'from' => (int) DateTime::get('-' . absint($args['timeframe']) . ' min', 'U'),
            'to'   => (int) DateTime::get('now', 'U'),
        ];
    }

    /**
     * Count recent bot activity records.
     *
     * @param array $args Query arguments.
     * @return int
     */
    public function countActivities($args = [])
    {
        if (!DB::ExistTable(DB::table('bot_activity'))) {
            return 0;
        }

        $args = $this->parseArgs($args, [
            'ip'     => '',
            'reason' => '',
        ]);

        $result = Query::select('COUNT(*)')
            ->from('bot_activity')
            ->where('ip', '=', $args['ip'])
            ->where('reason', '=', $args['reason'])
            ->where('last_view', '>=', $this->timeframe['from'])
            ->where('last_view', '<=', $this->timeframe['to'])
            ->getVar();

        return $result ? (int) $result : 0;
    }

    /**
     * Get recent bot activity records.
     *
     * @param array $args Query arguments.
     * @return array
     */
    public function getActivities($args = [])
    {
        if (!DB::ExistTable(DB::table('bot_activity'))) {
            return [];
        }

        $args = $this->parseArgs($args, [
            'ip'       => '',
            'reason'   => '',
            'page'     => 1,
            'per_page' => '',
            'order_by' => 'last_view',
            'order'    => 'DESC',
        ]);

        $result = Query::select('*')
            ->from('bot_activity')
            ->where('ip', '=', $args['ip'])
            ->where('reason', '=', $args['reason'])
            ->where('last_view', '>=', $this->timeframe['from'])
            ->where('last_view', '<=', $this->timeframe['to'])
            ->perPage($args['page'], $args['per_page'])
            ->orderBy($args['order_by'], $args['order'])
            ->getAll();

        return $result ?: [];
    }
}
