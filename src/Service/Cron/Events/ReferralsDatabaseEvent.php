<?php

namespace WP_Statistics\Service\Cron\Events;

use WP_Statistics\Service\Analytics\Referrals\ReferralsDatabase;

/**
 * Referrals Database Maintenance Cron Event.
 *
 * Handles referrals database updates and maintenance.
 *
 * @since 15.0.0
 */
class ReferralsDatabaseEvent extends AbstractCronEvent
{
    /**
     * @var string
     */
    protected $hook = 'wp_statistics_referrals_db_hook';

    /**
     * @var string
     */
    protected $recurrence = 'monthly';

    /**
     * Referrals database maintenance should always be scheduled.
     *
     * @return bool
     */
    protected function shouldSchedule()
    {
        return true;
    }

    /**
     * Execute the referrals database maintenance.
     *
     * @return void
     */
    public function execute()
    {
        /**
         * Action for referrals database maintenance.
         *
         * Allows addons/extensions to hook into the maintenance process.
         */
        do_action('wp_statistics_referrals_database_maintenance');
    }
}
