<?php

namespace WP_Statistics\Service\Analytics;

use WP_STATISTICS\DB;
use WP_STATISTICS\Option;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Service\Analytics\DeviceDetection\UserAgentService;
use WP_Statistics\Service\Database\Managers\TableHandler;
use WP_Statistics\Service\Database\Schema\Manager;

class BotActivity
{
    /**
     * Exclusion reasons that represent bot or automated traffic.
     *
     * @var string[]
     */
    private static $botReasons = ['robot', 'headless', 'robot_threshold'];

    /**
     * Whether the separate bot activity log is enabled.
     *
     * @return bool
     */
    public static function isEnabled()
    {
        return (bool) Option::get('bot_activity', false);
    }

    /**
     * Determine whether an exclusion reason represents bot activity.
     *
     * @param string $reason Exclusion reason.
     * @return bool
     */
    public static function isBotReason($reason)
    {
        return in_array($reason, self::$botReasons, true);
    }

    /**
     * Create the activity table when the feature is first enabled.
     *
     * @return bool
     */
    public static function ensureTable()
    {
        $tableName = DB::table('bot_activity');

        if (DB::ExistTable($tableName)) {
            return true;
        }

        try {
            TableHandler::createTable('bot_activity', Manager::getSchemaForTable('bot_activity'));
        } catch (\Exception $e) {
            \WP_Statistics::log($e->getMessage(), 'warning');
            return false;
        }

        return DB::ExistTable($tableName);
    }

    /**
     * Store a recent bot hit separately from normal visitor statistics.
     *
     * @param array          $exclusion     Exclusion result.
     * @param VisitorProfile $visitorProfile Visitor profile for the request.
     * @return bool
     */
    public static function record($exclusion, VisitorProfile $visitorProfile)
    {
        global $wpdb;

        $reason = isset($exclusion['exclusion_reason']) ? sanitize_key($exclusion['exclusion_reason']) : '';

        if (!self::isEnabled() || !self::isBotReason($reason)) {
            return false;
        }

        $requestUri  = $visitorProfile->getRequestUri();
        $requestPath = wp_parse_url($requestUri, PHP_URL_PATH);
        $userAgent   = self::limit(sanitize_text_field($visitorProfile->getHttpUserAgent()), 190);
        $ip          = self::limit(sanitize_text_field($visitorProfile->getProcessedIPForStorage()), 60);
        $uri         = self::limit(sanitize_text_field($requestPath ?: '/'), 190);
        $botName     = self::limit(self::getBotName($visitorProfile, $reason), 180);
        $activityKey = md5($ip . '|' . $userAgent . '|' . $uri);
        $date        = DateTime::get();
        $lastView    = DateTime::get('now', 'Y-m-d H:i:s');
        $tableName   = DB::table('bot_activity');

        $recorded = $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO `{$tableName}` (`last_counter`, `activity_key`, `ip`, `user_agent`, `bot_name`, `reason`, `uri`, `hits`, `last_view`) VALUES (%s, %s, %s, %s, %s, %s, %s, 1, %s) ON DUPLICATE KEY UPDATE `hits` = `hits` + 1, `last_view` = VALUES(`last_view`), `reason` = VALUES(`reason`), `bot_name` = VALUES(`bot_name`)",
                $date,
                $activityKey,
                $ip,
                $userAgent,
                $botName,
                $reason,
                $uri,
                $lastView
            )
        );

        if ($recorded === false) {
            \WP_Statistics::log($wpdb->last_error, 'warning');
            return false;
        }

        do_action('wp_statistics_save_bot_activity', $exclusion, $visitorProfile);

        return true;
    }

    /**
     * Resolve a human-readable name when Device Detector knows the bot.
     *
     * @param VisitorProfile $visitorProfile Visitor profile for the request.
     * @param string         $reason         Exclusion reason.
     * @return string
     */
    private static function getBotName(VisitorProfile $visitorProfile, $reason)
    {
        $userAgent = $visitorProfile->getUserAgent();

        if (empty($userAgent)) {
            return '';
        }

        $detector = $userAgent->getDeviceDetector();

        if ($detector && $detector->isBot()) {
            $bot = $detector->getBot();

            if (!empty($bot['name'])) {
                return UserAgentService::sanitizeDetectorValue($bot['name']);
            }
        }

        if ($reason === 'headless') {
            return UserAgentService::sanitizeDetectorValue($userAgent->getBrowser());
        }

        return '';
    }

    /**
     * Limit values to their database column length.
     *
     * @param string $value  Value to limit.
     * @param int    $length Maximum length.
     * @return string
     */
    private static function limit($value, $length)
    {
        if (function_exists('mb_substr')) {
            return mb_substr((string) $value, 0, $length);
        }

        return substr((string) $value, 0, $length);
    }
}
