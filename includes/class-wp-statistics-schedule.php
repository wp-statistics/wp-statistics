<?php

namespace WP_STATISTICS;

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

        //Run This Method Only Admin Area
        if (is_admin()) {

            //Disable Run to Ajax
            if (!Helper::is_request('ajax')) {

                // Add the GeoIP update schedule if it doesn't exist and it should be.
                if (!wp_next_scheduled('wp_statistics_geoip_hook') && Option::get('schedule_geoip')) {
                    wp_schedule_event(time(), 'daily', 'wp_statistics_geoip_hook');
                }

                // Remove the GeoIP update schedule if it does exist and it should shouldn't.
                if (wp_next_scheduled('wp_statistics_geoip_hook') && (!Option::get('schedule_geoip'))) {
                    wp_unschedule_event(wp_next_scheduled('wp_statistics_geoip_hook'), 'wp_statistics_geoip_hook');
                }

                //Construct Event
                add_action('wp_statistics_geoip_hook', array($this, 'geoip_event'));
            }

        } else {

            // Add the referrerspam update schedule if it doesn't exist and it should be.
            if (!wp_next_scheduled('wp_statistics_referrerspam_hook') && Option::get('schedule_referrerspam')) {
                wp_schedule_event(time(), 'weekly', 'wp_statistics_referrerspam_hook');
            }

            // Remove the referrerspam update schedule if it does exist and it should shouldn't.
            if (wp_next_scheduled('wp_statistics_referrerspam_hook') && !Option::get('schedule_referrerspam')) {
                wp_unschedule_event(wp_next_scheduled('wp_statistics_referrerspam_hook'), 'wp_statistics_referrerspam_hook');
            }

            // Add the database maintenance schedule if it doesn't exist and it should be.
            if (!wp_next_scheduled('wp_statistics_dbmaint_hook') && Option::get('schedule_dbmaint')) {
                wp_schedule_event(time(), 'daily', 'wp_statistics_dbmaint_hook');
            }

            // Remove the database maintenance schedule if it does exist and it shouldn't.
            if (wp_next_scheduled('wp_statistics_dbmaint_hook') && (!Option::get('schedule_dbmaint'))) {
                wp_unschedule_event(wp_next_scheduled('wp_statistics_dbmaint_hook'), 'wp_statistics_dbmaint_hook');
            }

            // Add the add visit table row schedule if it does exist and it should.
            if (!wp_next_scheduled('wp_statistics_add_visit_hook')) {
                wp_schedule_event(time(), 'daily', 'wp_statistics_add_visit_hook');
            }

            //After construct
            add_action('wp_statistics_add_visit_hook', array($this, 'add_visit_event'));
            add_action('wp_statistics_dbmaint_hook', array($this, 'dbmaint_event'));
        }

        // Add the report schedule if it doesn't exist and is enabled.
        if (!wp_next_scheduled('wp_statistics_report_hook') && Option::get('time_report') != '0') {
            $timeReports       = Option::get('time_report');
            $schedulesInterval = self::getSchedules();

            if (isset($schedulesInterval[$timeReports], $schedulesInterval[$timeReports]['next_schedule'])) {
                $scheduleTime = $schedulesInterval[$timeReports]['next_schedule'];
                wp_schedule_event($scheduleTime, $timeReports, 'wp_statistics_report_hook');
            }
        }

        // Remove the report schedule if it does exist and is disabled.
        if (wp_next_scheduled('wp_statistics_report_hook') && Option::get('time_report') == '0') {
            wp_unschedule_event(wp_next_scheduled('wp_statistics_report_hook'), 'wp_statistics_report_hook');
        }

        add_action('wp_statistics_report_hook', array($this, 'send_report'));
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
        $start_day_name = Helper::getStartOfWeek();;

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
            ]
        ];

        return apply_filters('wp_statistics_cron_schedules', $schedules);
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
     * adds a record for tomorrow to the visit table to avoid a race condition.
     */
    public function add_visit_event()
    {
        global $wpdb;

        $date = TimeZone::getCurrentDate('Y-m-d', '+1');

        // check if the record already exists
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `" . DB::table('visit') . "` WHERE `last_counter` = %s", $date));
        if ($exists > 0) {
            return;
        }

        //Insert
        $insert = $wpdb->insert(
            DB::table('visit'),
            array(
                'last_visit'   => TimeZone::getCurrentDate('Y-m-d H:i:s', '+1'),
                'last_counter' => TimeZone::getCurrentDate('Y-m-d', '+1'),
                'visit'        => 0,
            )
        );
        if (!$insert) {
            if (!empty($wpdb->last_error)) {
                \WP_Statistics::log($wpdb->last_error, 'warning');
            }
        }
    }

    /**
     * Updates the GeoIP database from MaxMind.
     */
    public function geoip_event()
    {
        // Max-mind updates the geo-ip database on the first Tuesday of the month, to make sure we don't update before they post
        $this_update = strtotime('first Tuesday of this month') + (86400 * 2);
        $last_update = Option::get('last_geoip_dl');
        $file_path   = GeoIP::get_geo_ip_path();

        if (file_exists($file_path)) {
            if ($last_update < $this_update) {
                GeoIP::download('update');
            }
        }

        // Update the last update time
        Option::update('last_geoip_dl', time());
    }

    /**
     * Purges old records on a schedule based on age.
     */
    public function dbmaint_event()
    {
        $purge_days = intval(Option::get('schedule_dbmaint_days', false));
        Purge::purge_data($purge_days);
    }

    public function getEmailSubject()
    {
        $schedule = Option::get('time_report', false);
        $subject  = __('Your WP Statistics Report', 'wp-statistics');

        if ($schedule && array_key_exists($schedule, self::getSchedules())) {
            $schedule = self::getSchedules()[$schedule];

            if ($schedule['start'] === $schedule['end']) {
                $subject .= sprintf(__(' for %s', 'wp-statistics'), $schedule['start']);
            } else {
                $subject .= sprintf(__(' for %s to %s', 'wp-statistics'), $schedule['start'], $schedule['end']);
            }
        }

        return $subject;
    }

    /**
     * Send WP Statistics Report
     */
    public function send_report()
    {
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

    public static function rescheduleEvent($event, $newTime, $prevTime)
    {
        if ($newTime === $prevTime) return;

        if (wp_next_scheduled($event)) {
            wp_unschedule_event(wp_next_scheduled($event), $event);
        }

        $time               = sanitize_text_field($newTime);
        $schedulesInterval  = self::getSchedules();

        if (isset($schedulesInterval[$time], $schedulesInterval[$time]['next_schedule'])) {
            $scheduleTime = $schedulesInterval[$time]['next_schedule'];
            wp_schedule_event($scheduleTime, $time, $event);
        }
    }
}

new Schedule;
