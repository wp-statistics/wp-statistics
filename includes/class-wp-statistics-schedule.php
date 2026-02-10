<?php

namespace WP_STATISTICS;

use WP_Statistics\BackgroundProcess\BackgroundProcessFactory;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\Event;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Service\Analytics\Referrals\ReferralsDatabase;
use WP_Statistics\Service\Admin\LicenseManagement\ApiCommunicator;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseMigration;

/**
 * Legacy Schedule class for backward compatibility.
 *
 * @deprecated 15.0.0 Use \WP_Statistics\Components\Event for scheduling instead.
 * @see \WP_Statistics\Components\Event
 * @see \WP_Statistics\BackgroundProcess\BackgroundProcessFactory
 *
 * This class is maintained for backward compatibility with add-ons.
 * New code should use the Event component for cron scheduling.
 *
 * Migration guide:
 * - Schedule::getSchedules()  -> Event scheduling utilities
 * - Cron hooks                -> BackgroundProcessFactory for async tasks
 * - Report scheduling         -> Advanced Reporting add-on handles this
 */
class Schedule
{

    /**
     * Class instance.
     *
     * @see get_instance()
     * @type object
     */
    static $instance = null;

    public function __construct()
    {

        // Define New Cron Schedules Time in WordPress
        add_filter('cron_schedules', array($this, 'define_schedules_time'));

        add_action('init', [$this, 'maybe_schedule_hooks']);
    }

    /**
     * Schedule or unschedule all WP-Statistics cron hooks based on current options.
     *
     * @return void
     */
    public function maybe_schedule_hooks()
    {
        if (!Request::isFrom('admin')) {

            // Add the database maintenance schedule if it doesn't exist and it should be.
            if (!wp_next_scheduled('wp_statistics_dbmaint_hook') && Option::get('schedule_dbmaint')) {
                wp_schedule_event(time(), 'daily', 'wp_statistics_dbmaint_hook');
            }

            // Remove the database maintenance schedule if it does exist and it shouldn't.
            if (wp_next_scheduled('wp_statistics_dbmaint_hook') && (!Option::get('schedule_dbmaint'))) {
                wp_unschedule_event(wp_next_scheduled('wp_statistics_dbmaint_hook'), 'wp_statistics_dbmaint_hook');
            }

            //After construct
            add_action('wp_statistics_dbmaint_hook', array($this, 'dbmaint_event'));
        }

        if (!wp_next_scheduled('wp_statistics_referrals_db_hook')) {
            wp_schedule_event(time(), 'monthly', 'wp_statistics_referrals_db_hook');
        }

        // Note: Email report scheduling is now handled by CronManager/EmailReportEvent.
        // Legacy 'wp_statistics_report_hook' handling has been removed.
        // Cleanup any existing legacy hooks.
        if (wp_next_scheduled('wp_statistics_report_hook')) {
            wp_unschedule_event(wp_next_scheduled('wp_statistics_report_hook'), 'wp_statistics_report_hook');
        }

        // Schedule license migration
        if (!wp_next_scheduled('wp_statistics_licenses_hook') && !LicenseMigration::hasLicensesAlreadyMigrated()) {
            wp_schedule_event(time(), 'daily', 'wp_statistics_licenses_hook');
        }

        // Remove license migration schedule if licenses have been migrated before
        if (wp_next_scheduled('wp_statistics_licenses_hook') && LicenseMigration::hasLicensesAlreadyMigrated()) {
            wp_unschedule_event(wp_next_scheduled('wp_statistics_licenses_hook'), 'wp_statistics_licenses_hook');
        }

        // Note: GeoIP scheduling is now handled by CronManager/GeoIPUpdateEvent.
        // Legacy scheduling removed — the v15 system always auto-updates when not using Cloudflare.
        $locationDetection = Option::get('geoip_location_detection_method', 'maxmind');

        if (in_array($locationDetection, ['maxmind', 'dbip'], true)) {
            add_action('wp_statistics_geoip_hook', array($this, 'geoip_event'));
        }

        if (! wp_next_scheduled( 'wp_statistics_queue_daily_summary')) {
            $timeZone = wp_timezone();
            $dateTime = new \DateTimeImmutable('now', $timeZone);
            $nextDate = $dateTime->modify('tomorrow')->setTime(0, 1);

            $nextDailySummary = $nextDate->getTimestamp();

            wp_schedule_event($nextDailySummary, 'daily', 'wp_statistics_queue_daily_summary');
        }

        add_action( 'wp_statistics_queue_daily_summary', [BackgroundProcessFactory::class, 'processDailySummaryTotal']);
        add_action( 'wp_statistics_queue_daily_summary', [BackgroundProcessFactory::class, 'processDailySummary']);

        // Note: Email reports are now handled by CronManager/EmailReportEvent
        // using the 'wp_statistics_email_report' hook.
        // Legacy 'wp_statistics_report_hook' is deprecated and will be removed.

        add_action('wp_statistics_licenses_hook', [$this, 'migrateOldLicenses']);

        Event::schedule('wp_statistics_check_licenses_status', time(), 'weekly', [$this, 'check_licenses_status']);
    }

    /**
     * Access the instance of this class
     *
     * @return  object of this class
     */
    public static function get_instance()
    {
        null === self::$instance and self::$instance = new self;

        return self::$instance;
    }

    /**
     * Retrieves an array of schedules with their intervals and display names.
     *
     * @return array
     */
    public static function getSchedules()
    {
        $timestamp = time();
        $timezone  = wp_timezone();
        $datetime  = new \DateTime('@' . $timestamp);
        $datetime->setTimezone($timezone);

        // Determine the day name based on the start of the week setting
        $start_day_name = DateTime::getStartOfWeek();

        // Daily schedule
        $daily = clone $datetime;
        $daily->modify('tomorrow')->setTime(8, 0);

        // Weekly schedule
        $weekly = clone $datetime;
        $weekly->modify("next {$start_day_name}")->setTime(8, 0);

        // BiWeekly schedule
        $biweekly = clone $datetime;
        $biweekly->modify("next {$start_day_name} +1 week")->setTime(8, 0);

        // Monthly schedule
        $monthly = clone $datetime;
        $monthly->modify('first day of next month')->setTime(8, 0);

        $schedules = [
            'every_minute' => [
                'interval' => 60,
                'display'  => __('Every Minute', 'wp-statistics'),
            ],
            'daily'    => [
                'interval'      => DAY_IN_SECONDS,
                'display'       => __('Daily', 'wp-statistics'),
                'start'         => wp_date('Y-m-d', strtotime("-1 day")),
                'end'           => wp_date('Y-m-d', strtotime("-1 day")),
                'next_schedule' => $daily->getTimestamp()
            ],
            'weekly'   => [
                'interval'      => WEEK_IN_SECONDS,
                'display'       => __('Weekly', 'wp-statistics'),
                'start'         => wp_date('Y-m-d', strtotime("-7 days")),
                'end'           => wp_date('Y-m-d', strtotime("-1 day")),
                'next_schedule' => $weekly->getTimestamp()
            ],
            'biweekly' => [
                'interval'      => 2 * WEEK_IN_SECONDS,
                'display'       => __('Bi-Weekly', 'wp-statistics'),
                'start'         => wp_date('Y-m-d', strtotime("-14 days")),
                'end'           => wp_date('Y-m-d', strtotime("-1 day")),
                'next_schedule' => $biweekly->getTimestamp()
            ],
            'monthly'  => [
                'interval'      => MONTH_IN_SECONDS,
                'display'       => __('Monthly', 'wp-statistics'),
                'start'         => wp_date('Y-m-d', strtotime('First day of previous month')),
                'end'           => wp_date('Y-m-d', strtotime('Last day of previous month')),
                'next_schedule' => $monthly->getTimestamp()
            ],
        ];

        return apply_filters('wp_statistics_cron_schedules', $schedules);
    }

    public static function check_licenses_status()
    {
        LicenseHelper::checkLicensesStatus();
    }

    /**
     * Define New Cron Schedules Time in WordPress
     *
     * @param array $schedules
     * @return mixed
     */
    static function define_schedules_time($schedules)
    {

        // Adds once weekly to the existing schedules.
        $wpsSchedules = self::getSchedules();

        foreach ($wpsSchedules as $key => $val) {
            if (!array_key_exists($key, $schedules)) {
                $schedules[$key] = [
                    'interval' => $val['interval'],
                    'display'  => $val['display']
                ];
            }
        }

        return $schedules;
    }

    public static function getNextScheduledTime($event)
    {
        return wp_next_scheduled($event);
    }

    /**
     * Updates the GeoIP database from MaxMind.
     */
    public function geoip_event()
    {
        GeolocationFactory::downloadDatabase();
    }

    /**
     * Purges old records on a schedule based on age.
     */
    public function dbmaint_event()
    {
        $purge_days = intval(Option::get('schedule_dbmaint_days', false));
        Purge::purge_data($purge_days);
    }

    /**
     * Get email subject.
     *
     * @deprecated 15.0.0 Use EmailReportManager::getEmailSubject() instead.
     * @return string
     */
    public function getEmailSubject()
    {
        _deprecated_function(__METHOD__, '15.0.0', 'WP_Statistics\Service\EmailReport\EmailReportManager::getEmailSubject()');

        $schedule = Option::get('time_report', false);
        $subject  = __('Your WP Statistics Report', 'wp-statistics');

        if ($schedule && array_key_exists($schedule, self::getSchedules())) {
            $schedule = self::getSchedules()[$schedule];

            if ($schedule['start'] === $schedule['end']) {
                $subject .= sprintf(__('for %s', 'wp-statistics'), $schedule['start']);
            } else {
                $subject .= sprintf(__('for %s to %s', 'wp-statistics'), $schedule['start'], $schedule['end']);
            }
        }

        return $subject;
    }

    /**
     * Send WP Statistics Report.
     *
     * @deprecated 15.0.0 Email reports are now handled by CronManager/EmailReportEvent.
     * @see \WP_Statistics\Service\Cron\Events\EmailReportEvent
     * @return void
     */
    public function send_report()
    {
        _deprecated_function(__METHOD__, '15.0.0', 'WP_Statistics\Service\Cron\Events\EmailReportEvent::execute()');

        // Legacy support: Only run if somehow the old hook is still being called
        // and the new system hasn't handled it yet.
        // This provides backward compatibility for any add-ons using the old hook.

        // apply Filter ShortCode for email content
        $email_content = Option::get('content_report');

        // Support ShortCode
        $email_content = do_shortcode($email_content);

        // Type Send Report
        $type = Option::get('send_report');

        // If Email
        if ($type == 'mail') {

            /**
             * Filter for email template content
             * @usage wp-statistics-advanced-reporting
             */
            $final_report_text = apply_filters('wp_statistics_final_text_report_email', $email_content);

            /**
             * Filter to modify email subject
             */
            $email_subject = apply_filters('wp_statistics_report_email_subject', self::getEmailSubject());

            /**
             * Filter for enable/disable sending email by template.
             */
            $email_template = apply_filters('wp_statistics_report_email_template', true);

            /**
             * Email receivers
             */
            $email_receivers = apply_filters('wp_statistics_report_email_receivers', Option::getEmailNotification());

            /**
             * Send Email
             */
            $result_email = Helper::send_mail(
                $email_receivers,
                $email_subject,
                $final_report_text,
                $email_template
            );

            /**
             * Fire actions after sending email
             */
            do_action('wp_statistics_after_report_email', $result_email, $email_receivers, $final_report_text);

        }

        // If SMS
        if ($type == 'sms' and !empty($email_content) and function_exists('wp_sms_send') and class_exists('\WP_SMS\Option')) {
            $adminMobileNumber = \WP_SMS\Option::getOption('admin_mobile_number');
            wp_sms_send($adminMobileNumber, $email_content);
        }
    }

    /**
     * @deprecated Use WP_Statistics\Components\Event::reschedule() instead
     */
    public static function rescheduleEvent($event, $newTime, $prevTime)
    {
        Event::reschedule($event, $newTime);
    }

    /**
     * Calls `LicenseMigration->migrateOldLicenses()` and migrates old licenses to the new structure.
     *
     * @return void
     */
    public function migrateOldLicenses()
    {
        $apiCommunicator  = new ApiCommunicator();
        $licenseMigration = new LicenseMigration($apiCommunicator);
        $licenseMigration->migrateOldLicenses();
    }
}

new Schedule;
