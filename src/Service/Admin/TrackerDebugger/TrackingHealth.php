<?php

namespace WP_Statistics\Service\Admin\TrackerDebugger;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Debugger\Provider\VisitorProvider;

/**
 * Daily check that tells the site owner when visits stop being recorded.
 *
 * It reads the same status as the Tracker Debugger's "Visit Recording" card and uses the
 * same rule (VisitorProvider::getRecordingState()), so the alert and the card never disagree.
 * A site that never recorded a visit, or a quiet site that can go a day without one, is not
 * an outage. The email also needs steady traffic before the last visit and a silence longer
 * than the site's usual gap (see shouldAlert()), and is never sent before the check has once
 * seen a recent visit (see BASELINE_OPTION). When the check could not read the visitor table,
 * it does nothing.
 *
 * One email when visits stop, one when they come back, never more per outage. While visits
 * are stopped, WP Statistics screens show a notice that can be dismissed for this outage.
 *
 * The outage is kept in one option. It is created with add_option(), which only one of two
 * overlapping checks can win, and it records whether the "stopped" email really went out, so
 * a failed email is tried again on the next daily run and a sent one is never sent twice.
 *
 * Turning the setting off only stops the checking and the emails. It keeps an open outage on
 * purpose: turning it on again while visits are still stopped must not send a second email
 * for the same outage, and when visits are back the next check sends the "recording again" email.
 */
class TrackingHealth
{
    /**
     * Daily cron hook.
     */
    const HOOK = 'wp_statistics_tracking_health_hook';

    /**
     * Option that keeps the current outage, empty while visits are recorded.
     */
    const STATE_OPTION = 'wp_statistics_tracking_health';

    /**
     * Setting key in the main WP Statistics options (Settings → Email Reports).
     */
    const SETTING = 'tracking_stopped_alert';

    /**
     * Prefix of the dismissible notice id; the outage start makes it unique per outage.
     */
    const NOTICE_PREFIX = 'tracking_stopped_';

    /**
     * Short-lived option that keeps two overlapping checks from both sending the email.
     */
    const LOCK_OPTION = 'wp_statistics_tracking_health_lock';

    /**
     * Set the first time a check sees a visit in the baseline window. Until then nothing is sent,
     * so the first check after an update or activation never alerts about a site that has been
     * quiet for a long time. Removed on deactivation.
     */
    const BASELINE_OPTION = 'wp_statistics_tracking_health_baseline';

    /**
     * Days looked at to decide whether the site has steady traffic.
     */
    const BASELINE_DAYS = 14;

    /**
     * Steady traffic: visits on at least this many of the BASELINE_DAYS days...
     */
    const STEADY_ACTIVE_DAYS = 10;

    /**
     * ...or at least this many visitors a day on average in the week before the last visit.
     */
    const STEADY_VISITORS_PER_DAY = 5;

    /**
     * Whether the alert is on. On by default, also for sites that never saved the setting.
     *
     * @return bool
     */
    public static function isEnabled()
    {
        return (bool)Option::get(self::SETTING, true);
    }

    /**
     * The current outage, or an empty array while visits are recorded.
     *
     * The option is read defensively: anything that is not the shape written by check()
     * (a manual edit, an old or broken value) counts as no outage, so it can never break
     * a WP Statistics screen.
     *
     * @return array{stopped_since?: string, detected_at?: int, visitors_week_before?: int|null, notified?: bool}
     */
    public static function getState()
    {
        $state = get_option(self::STATE_OPTION, []);

        if (!is_array($state) || empty($state['stopped_since']) || !is_string($state['stopped_since'])) {
            return [];
        }

        return [
            'stopped_since'        => $state['stopped_since'],
            'detected_at'          => isset($state['detected_at']) && is_numeric($state['detected_at']) ? (int)$state['detected_at'] : 0,
            'visitors_week_before' => isset($state['visitors_week_before']) && is_numeric($state['visitors_week_before']) ? (int)$state['visitors_week_before'] : null,
            'notified'             => !empty($state['notified']),
        ];
    }

    /**
     * Whether visits are currently known to be stopped.
     *
     * @return bool
     */
    public static function isStopped()
    {
        $state = self::getState();

        return !empty($state['stopped_since']);
    }

    /**
     * Id of the notice for the current outage. Dismissing it hides it until the next outage.
     *
     * @return string
     */
    public static function getNoticeId()
    {
        $state = self::getState();

        return self::NOTICE_PREFIX . (isset($state['detected_at']) ? (int)$state['detected_at'] : 0);
    }

    /**
     * Run the check. Called daily by cron.
     *
     * @param array|mixed $status A getRecordingStatus() result. Cron passes nothing (or an
     *                            empty string), and then the status is read from the database.
     * @return string|null The recording state that was acted on, null when the check is off.
     */
    public static function check($status = null)
    {
        // Setting off: stop checking and emailing, but keep an open outage (see the class comment).
        if (!self::isEnabled()) {
            return null;
        }

        if (!is_array($status)) {
            $status = (new VisitorProvider())->getRecordingStatus();
        }

        /**
         * Filters the recording status the daily "visits stopped" check acts on.
         *
         * @param array $status Result of VisitorProvider::getRecordingStatus().
         */
        $status = apply_filters('wp_statistics_tracking_health_status', $status);

        $recordingState = VisitorProvider::getRecordingState($status);

        // A failed query is not "no visits": try again tomorrow.
        if ($recordingState === 'error') {
            return $recordingState;
        }

        // A stored value that is not a valid outage is dropped, so a new outage can be recorded.
        if (!self::isStopped() && get_option(self::STATE_OPTION, false) !== false) {
            delete_option(self::STATE_OPTION);
        }

        $hasBaseline = self::updateBaseline($status);

        if ($recordingState === 'stopped') {
            // Only a site with steady traffic, silent for longer than its usual gap, is an outage.
            // An outage that is already open stays open (and is emailed once) until visits come back.
            if (self::isStopped() || ($hasBaseline && self::shouldAlert($status))) {
                // add_option() only succeeds for one of two overlapping checks, so only one of them
                // records the outage; the other one leaves the email to it.
                self::claimOutage($status);
                self::sendStoppedEmail();
            }

            return $recordingState;
        }

        $state = self::getState();

        // delete_option() only succeeds for one of two overlapping checks: only that one emails.
        if ($state && delete_option(self::STATE_OPTION)) {
            self::forgetDismissedNotice();

            // Nobody was told about an outage whose email never went out, so nobody needs to hear it ended.
            if ($recordingState === 'working' && $state['notified']) {
                self::sendRecoveredEmail($state, $status);
            }
        }

        return $recordingState;
    }

    /**
     * Whether a "stopped" status is worth an email: the site had steady traffic before the last
     * visit, and the silence is longer than its usual gap between visits.
     *
     * - Steady: visits on most of the last BASELINE_DAYS days, or STEADY_VISITORS_PER_DAY a day on
     *   average. A small blog with 3 visits a week never gets the alert.
     * - Usual gap: BASELINE_DAYS / active days. The alert waits twice that, less one day, and at
     *   least one day (a site with visits every day: 1 day; on 7 of 14 days: 3 days).
     *
     * @param array $status Result of VisitorProvider::getRecordingStatus().
     * @return bool
     */
    public static function shouldAlert($status)
    {
        if (VisitorProvider::getRecordingState($status) !== 'stopped') {
            return false;
        }

        $activeDays = isset($status['active_days']) && is_numeric($status['active_days']) ? (int)$status['active_days'] : null;
        $perDay     = isset($status['visitors_week_before']) && is_numeric($status['visitors_week_before']) ? (float)$status['visitors_week_before'] / 7 : 0;

        $steady = ($activeDays !== null && $activeDays >= self::STEADY_ACTIVE_DAYS) || $perDay >= self::STEADY_VISITORS_PER_DAY;

        if (!$steady) {
            return false;
        }

        $gapDays = $activeDays ? self::BASELINE_DAYS / max(1, min(self::BASELINE_DAYS, $activeDays)) : 1;
        $window  = max(1, (int)ceil(2 * $gapDays - 1)) * DAY_IN_SECONDS;

        return (int)$status['seconds_since'] > $window;
    }

    /**
     * Record that the check has seen the site with a visit in the last BASELINE_DAYS days.
     *
     * @param array $status
     * @return bool Whether the baseline is (now) established.
     */
    private static function updateBaseline($status)
    {
        if (get_option(self::BASELINE_OPTION, false)) {
            return true;
        }

        $recent = !empty($status['last_visit'])
            && isset($status['seconds_since'])
            && (int)$status['seconds_since'] <= self::BASELINE_DAYS * DAY_IN_SECONDS;

        if (!$recent) {
            return false;
        }

        update_option(self::BASELINE_OPTION, time(), false);

        return true;
    }

    /**
     * Forget the baseline, so the next check after activation starts fresh.
     *
     * @return void
     */
    public static function resetBaseline()
    {
        delete_option(self::BASELINE_OPTION);
    }

    /**
     * Record a new outage, unless one is already recorded.
     *
     * @param array $status
     * @return bool True when this call recorded it.
     */
    private static function claimOutage($status)
    {
        if (self::isStopped()) {
            return false;
        }

        return (bool)add_option(self::STATE_OPTION, [
            'stopped_since'        => (string)$status['last_visit'],
            'detected_at'          => time(),
            'visitors_week_before' => isset($status['visitors_week_before']) ? (int)$status['visitors_week_before'] : null,
            'notified'             => false,
        ], '', false);
    }

    /**
     * Forget the current outage and its dismissed notice.
     *
     * @return void
     */
    public static function clear()
    {
        if (get_option(self::STATE_OPTION, false) !== false) {
            delete_option(self::STATE_OPTION);
        }

        self::forgetDismissedNotice();
    }

    /**
     * Remove the dismissed "visits stopped" notice ids, so the next outage shows its notice again.
     *
     * @return void
     */
    private static function forgetDismissedNotice()
    {
        $dismissed = get_option('wp_statistics_dismissed_notices', []);

        if (is_array($dismissed) && !empty($dismissed)) {
            $kept = array_values(array_filter($dismissed, function ($id) {
                return strpos((string)$id, self::NOTICE_PREFIX) !== 0;
            }));

            if (count($kept) !== count($dismissed)) {
                update_option('wp_statistics_dismissed_notices', $kept);
            }
        }
    }

    /**
     * Format a stored (site timezone) date for people.
     *
     * @param string $date
     * @return string
     */
    public static function formatDate($date)
    {
        return date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($date));
    }

    /**
     * Format a stored (site timezone) date as a day only, for the email subject.
     *
     * The trailing space keeps a date format that ends in a backslash from breaking date_i18n().
     *
     * @param string $date
     * @return string
     */
    public static function formatDay($date)
    {
        return trim(date_i18n(get_option('date_format') . ' ', strtotime($date)));
    }

    /**
     * "about 120 visitors a day" or, for small sites, "5 visitors in the week before".
     *
     * @param int|null $visitorsWeekBefore
     * @return string
     */
    public static function getUsualTrafficText($visitorsWeekBefore)
    {
        $visitorsWeekBefore = (int)$visitorsWeekBefore;
        $perDay             = (int)round($visitorsWeekBefore / 7);

        if ($perDay >= 1) {
            /* translators: %s: average number of visitors per day. */
            return sprintf(_n('about %s visitor a day', 'about %s visitors a day', $perDay, 'wp-statistics'), number_format_i18n($perDay));
        }

        /* translators: %s: number of visitors. */
        return sprintf(_n('%s visitor in the week before', '%s visitors in the week before', $visitorsWeekBefore, 'wp-statistics'), number_format_i18n($visitorsWeekBefore));
    }

    /**
     * Link to Statistics → Tracker Debugger.
     *
     * @return string
     */
    public static function getDebuggerUrl()
    {
        // The debugger page only exists with client-side tracking (see TrackerDebuggerManager).
        if (empty(Option::get('use_cache_plugin'))) {
            return Menus::admin_url('settings', ['row' => 'tracking_method_tr']);
        }

        return Menus::admin_url('wps_tracker-debugger_page');
    }

    /**
     * Text for the link returned by getDebuggerUrl().
     *
     * @return string
     */
    public static function getDebuggerLinkText()
    {
        if (empty(Option::get('use_cache_plugin'))) {
            return __('Open the tracking settings', 'wp-statistics');
        }

        return __('Open the Tracker Debugger', 'wp-statistics');
    }

    /**
     * Send the "stopped" email for the recorded outage, once.
     *
     * The outage is marked as notified only after the email was accepted for sending. If it
     * was not (no valid recipient, the mail server refused it), the outage stays open and not
     * notified, and the next daily run tries again: at most one attempt per day, so no spam.
     * A short lock keeps two overlapping checks from both sending it.
     *
     * @return bool True when the email was sent now.
     */
    private static function sendStoppedEmail()
    {
        // add_option() is the lock: it fails while another check is sending.
        if (!add_option(self::LOCK_OPTION, time(), '', false)) {
            // A lock left behind by a crashed check must not block the alert for good.
            if ((int)get_option(self::LOCK_OPTION, 0) > time() - 10 * MINUTE_IN_SECONDS) {
                return false;
            }

            delete_option(self::LOCK_OPTION);

            if (!add_option(self::LOCK_OPTION, time(), '', false)) {
                return false;
            }
        }

        $sent  = false;
        $state = self::getState();

        if ($state && !$state['notified']) {
            $lastVisit = self::formatDate($state['stopped_since']);

            /* translators: %1$s: site name, %2$s: date of the last recorded visit. */
            $subject = sprintf(__('%1$s: No visits recorded since %2$s', 'wp-statistics'), wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES), self::formatDay($state['stopped_since']));

            $rows = [
                __('Last visit recorded', 'wp-statistics') => $lastVisit,
                __('Usual traffic', 'wp-statistics')       => self::getUsualTrafficText($state['visitors_week_before'] ?? 0),
            ];

            $title = __('Visits have stopped being recorded', 'wp-statistics');
            $body  = self::renderEmail(
                __('WP Statistics has not recorded a visit for a while, but your site usually gets visitors. Something may be stopping visits from being recorded, for example a cookie banner, a cache plugin or a blocked script.', 'wp-statistics'),
                $rows,
                self::getDebuggerLinkText()
            );

            $sent = self::send($subject, $title, $body);

            if ($sent) {
                $state['notified'] = true;
                update_option(self::STATE_OPTION, $state, false);
            }
        }

        delete_option(self::LOCK_OPTION);

        return $sent;
    }

    /**
     * @param array $state  The outage that ended.
     * @param array $status
     * @return bool
     */
    private static function sendRecoveredEmail($state, $status)
    {
        /* translators: %s: site name. */
        $subject = sprintf(__('%s: Visits are being recorded again', 'wp-statistics'), wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES));

        $rows = [
            __('No visits recorded from', 'wp-statistics') => self::formatDate($state['stopped_since']),
            __('Visits recorded again', 'wp-statistics')   => self::formatDate($status['last_visit']),
        ];

        $title = __('Visits are being recorded again', 'wp-statistics');
        $body  = self::renderEmail(
            __('Good news: WP Statistics is recording visits on your site again. Visits during the gap were not recorded.', 'wp-statistics'),
            $rows,
            self::getDebuggerLinkText()
        );

        return self::send($subject, $title, $body);
    }

    /**
     * Send one alert email in the plugin's email layout, without the report sections.
     *
     * @param string $subject
     * @param string $title Heading under the logo.
     * @param string $body  Email content (HTML).
     * @return bool True when wp_mail() accepted it.
     */
    private static function send($subject, $title, $body)
    {
        /**
         * Filters who gets the "visits stopped" and "visits recorded again" emails.
         *
         * @param string $receivers Comma-separated email addresses. Default: the report email list.
         */
        $receivers = apply_filters('wp_statistics_tracking_health_email_receivers', Option::getEmailNotification());

        if (empty($receivers)) {
            return false;
        }

        $textAlign  = is_rtl() ? 'right' : 'left';
        $emailTitle = sprintf(
            '<table style="font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, Oxygen-Sans, Ubuntu, Cantarell, Helvetica Neue, sans-serif; width: 100%%; text-align: %1$s; font-size: 21px; font-weight: 500; line-height: 24.61px; color: #0C0C0D; padding: 0;"><tbody><tr><td>%2$s</td></tr></tbody></table>' .
            '<p style="margin: 4px 0 0;"><a href="%3$s" style="color: #56585A; font-size: 16px; font-weight: 500; line-height: 18.75px; text-decoration: none;">%4$s</a></p>',
            esc_attr($textAlign),
            esc_html($title),
            esc_url(home_url()),
            esc_html(get_bloginfo('name'))
        );

        return Helper::send_mail($receivers, $subject, $body, true, [
            'email_title'  => $emailTitle,
            // The custom header and footer text belong to the report, not to alerts.
            'email_header' => '',
            'email_footer' => '',
            'is_report'    => false,
        ]);
    }

    /**
     * Alert content: a short intro, a few label and value rows, a button and why it was sent.
     * Kept on one line because the layout shows the content with white-space: pre-wrap.
     *
     * @param string $intro
     * @param array  $rows  label => value
     * @param string $buttonText
     * @return string
     */
    private static function renderEmail($intro, $rows, $buttonText)
    {
        $rowsHtml = '';
        foreach ($rows as $label => $value) {
            $rowsHtml .= sprintf(
                '<tr><td style="padding: 6px 16px 6px 0; color: #56585A; font-size: 14px;">%1$s</td><td style="padding: 6px 0; color: #0C0C0D; font-size: 14px; font-weight: 500;">%2$s</td></tr>',
                esc_html($label),
                esc_html($value)
            );
        }

        return sprintf(
            '<p style="margin: 0 0 20px; color: #3D3D44; font-size: 16px; line-height: 1.5;">%1$s</p>' .
            '<table style="border-collapse: collapse; margin: 0 0 24px;"><tbody>%2$s</tbody></table>' .
            '<a href="%3$s" style="display: inline-block; background: #404BF2; color: #fff; font-size: 15px; font-weight: 600; text-decoration: none; padding: 12px 20px; border-radius: 4px;">%4$s</a>' .
            '<p style="margin: 24px 0 0; color: #56585A; font-size: 13px; line-height: 1.5;">%5$s</p>',
            esc_html($intro),
            $rowsHtml,
            esc_url(self::getDebuggerUrl()),
            esc_html($buttonText),
            esc_html__('You get this email because "Email me when visits stop being recorded" is on in Statistics → Settings → Email Reports.', 'wp-statistics')
        );
    }
}
