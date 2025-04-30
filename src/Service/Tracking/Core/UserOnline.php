<?php

namespace WP_STATISTICS\Service\Tracking\Core;

use WP_STATISTICS\Abstracts\BaseTracking;
use WP_STATISTICS\Option;
use WP_Statistics\Records\SessionRecord;
use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_STATISTICS\TimeZone;
use WP_Statistics\Traits\ErrorLoggerTrait;

/**
 * Tracks and manages real-time user sessions.
 *
 * This class handles detecting, recording, updating, and cleaning up records
 * for users currently online on the website.
 */
class UserOnline extends BaseTracking
{
    use ErrorLoggerTrait;

    /**
     * Option key used to store the last reset timestamp in the database.
     *
     * @var string
     */
    protected $resetOptionKey = 'wp_statistics_check_user_online';

    /**
     * Number of seconds after which a user is considered offline.
     *
     * @var int
     */
    protected $resetUserTime = 65;

    /**
     * Check if user online tracking is currently enabled.
     *
     * @return bool True if tracking is active, false otherwise.
     */
    public static function isActive()
    {
        if (has_filter('wp_statistics_active_user_online')) {
            return apply_filters('wp_statistics_active_user_online', true);
        }

        return Option::get('useronline', true);
    }

    /**
     * Record or update a user as online.
     *
     * @param VisitorProfile|null $profile Visitor profile instance (optional).
     * @return void
     */
    public function record($profile = null)
    {
        $profile   = $this->resolveProfile($profile);
        $sessionId = $profile->getSessionId();

        if (!$sessionId) {
            $userIp = $profile->getProcessedIPForStorage();

            $session = (new SessionRecord())->get(['ip' => $userIp]);
        } else {
            $session = (new SessionRecord())->get(['ID' => $sessionId]);
        }

        if (empty($session)) {
            return;
        }

        $sessionRecord = new SessionRecord($session);

        $sessionRecord->update([
            'ended_at' => TimeZone::getCurrentDate('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Check exclusion and record user as online if allowed.
     *
     * @param VisitorProfile|null $profile The visitor profile instance (optional).
     * @return void
     * @throws \Exception If user is excluded from tracking.
     */
    public function recordIfAllowed($profile = null)
    {
        if (!self::isActive()) {
            return;
        }

        $profile = $this->resolveProfile($profile);
        $this->checkAndThrowIfExcluded($profile);

        $this->record($profile);
        $this->errorListener();
    }
}
