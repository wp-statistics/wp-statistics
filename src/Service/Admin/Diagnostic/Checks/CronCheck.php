<?php

namespace WP_Statistics\Service\Admin\Diagnostic\Checks;

use WP_Statistics\Service\Admin\Diagnostic\DiagnosticResult;
use WP_Statistics\Service\Cron\CronManager;

/**
 * WP-Cron Status Check.
 *
 * Verifies that WordPress cron is enabled and WP Statistics events are scheduled.
 *
 * @since 15.0.0
 */
class CronCheck extends AbstractCheck
{
    /**
     * {@inheritDoc}
     */
    public function getKey(): string
    {
        return 'cron';
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel(): string
    {
        return __('WP-Cron Status', 'wp-statistics');
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription(): string
    {
        return __('Checks if scheduled tasks can run properly.', 'wp-statistics');
    }

    /**
     * {@inheritDoc}
     */
    public function getHelpUrl(): ?string
    {
        return 'https://developer.wordpress.org/plugins/cron/';
    }

    /**
     * {@inheritDoc}
     */
    public function isLightweight(): bool
    {
        return true; // Checking options is lightweight
    }

    /**
     * {@inheritDoc}
     */
    public function run(): DiagnosticResult
    {
        $details  = [];
        $warnings = [];

        // Check if WP-Cron is disabled
        $cronDisabled = defined('DISABLE_WP_CRON') && DISABLE_WP_CRON;
        $details['wp_cron_disabled'] = $cronDisabled;

        if ($cronDisabled) {
            $warnings[] = __('WP-Cron is disabled (DISABLE_WP_CRON is true).', 'wp-statistics');
        }

        // Check if alternate cron is enabled
        $alternateCron = defined('ALTERNATE_WP_CRON') && ALTERNATE_WP_CRON;
        $details['alternate_cron'] = $alternateCron;

        // Get scheduled WP Statistics events
        $scheduledEvents = CronManager::getScheduledEvents();
        $scheduledCount  = 0;
        $enabledCount    = 0;
        $eventDetails    = [];

        foreach ($scheduledEvents as $hook => $event) {
            if (!empty($event['scheduled'])) {
                $scheduledCount++;
            }
            if (!empty($event['enabled'])) {
                $enabledCount++;
            }
            $eventDetails[$hook] = [
                'scheduled' => $event['scheduled'] ?? false,
                'enabled'   => $event['enabled'] ?? false,
                'next_run'  => $event['next_run'] ?? null,
            ];
        }

        $details['total_events']     = count($scheduledEvents);
        $details['scheduled_events'] = $scheduledCount;
        $details['enabled_events']   = $enabledCount;

        // Check if any events are enabled but not scheduled
        $unscheduledEnabled = $enabledCount - $scheduledCount;
        if ($unscheduledEnabled > 0) {
            $warnings[] = sprintf(
                __('%d enabled event(s) are not scheduled.', 'wp-statistics'),
                $unscheduledEnabled
            );
        }

        // Return result
        if ($cronDisabled && empty($alternateCron)) {
            return $this->warning(
                __('WP-Cron is disabled. Ensure you have a server-side cron job configured.', 'wp-statistics'),
                $details
            );
        }

        if (!empty($warnings)) {
            return $this->warning(
                implode(' ', $warnings),
                $details
            );
        }

        return $this->pass(
            sprintf(
                __('Cron is enabled, %d events scheduled.', 'wp-statistics'),
                $scheduledCount
            ),
            $details
        );
    }
}
