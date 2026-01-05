<?php

namespace WP_Statistics\Service\Cron\Events;

use WP_STATISTICS\Purge;
use WP_Statistics\Service\Options\OptionManager;

/**
 * Database Maintenance Cron Event.
 *
 * Purges old records based on configured retention period.
 *
 * @since 15.0.0
 */
class DatabaseMaintenanceEvent extends AbstractCronEvent
{
    /**
     * @var string
     */
    protected $hook = 'wp_statistics_dbmaint_hook';

    /**
     * @var string
     */
    protected $recurrence = 'daily';

    /**
     * @var string
     */
    protected $description = 'Database Maintenance';

    /**
     * Check if database maintenance should be scheduled.
     *
     * @return bool
     */
    public function shouldSchedule(): bool
    {
        return (bool) OptionManager::get('schedule_dbmaint');
    }

    /**
     * Execute the database maintenance.
     *
     * @return void
     */
    public function execute(): void
    {
        $purgeDays = intval(OptionManager::get('schedule_dbmaint_days', 180));

        if ($purgeDays > 0) {
            Purge::purge_data($purgeDays); // todo need to be implemented.
        }
    }
}
