<?php

namespace WP_Statistics\Service\Cron\Events;

use WP_Statistics\Components\Event;
use WP_Statistics\Service\Admin\LicenseManagement\ApiCommunicator;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseMigration;

/**
 * License Management Cron Events.
 *
 * Handles license migration and status checks.
 *
 * @since 15.0.0
 */
class LicenseEvent extends AbstractCronEvent
{
    /**
     * @var string Migration hook name.
     */
    protected $hook = 'wp_statistics_licenses_hook';

    /**
     * @var string
     */
    protected $recurrence = 'daily';

    /**
     * @var string
     */
    protected $description = 'License Migration';

    /**
     * Status check hook name.
     *
     * @var string
     */
    private $statusCheckHook = 'wp_statistics_check_licenses_status';

    /**
     * Check if license migration should be scheduled.
     *
     * @return bool
     */
    public function shouldSchedule(): bool
    {
        return !LicenseMigration::hasLicensesAlreadyMigrated();
    }

    /**
     * Schedule or unschedule the events.
     *
     * @return void
     */
    public function maybeSchedule(): void
    {
        // Handle migration event
        parent::maybeSchedule();

        // Handle status check event (always scheduled weekly)
        Event::schedule($this->statusCheckHook, time(), 'weekly', [$this, 'checkLicensesStatus']);
    }

    /**
     * Register callbacks for both hooks.
     *
     * @return void
     */
    public function registerCallback(): void
    {
        add_action($this->hook, [$this, 'execute']);
        // Status check is registered via Event::schedule
    }

    /**
     * Execute license migration.
     *
     * @return void
     */
    public function execute(): void
    {
        $apiCommunicator  = new ApiCommunicator();
        $licenseMigration = new LicenseMigration($apiCommunicator);
        $licenseMigration->migrateOldLicenses();
    }

    /**
     * Check licenses status.
     *
     * @return void
     */
    public function checkLicensesStatus(): void
    {
        LicenseHelper::checkLicensesStatus();
    }
}
