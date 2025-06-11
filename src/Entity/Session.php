<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Models\SessionModel;
use WP_Statistics\Records\RecordFactory;
use WP_STATISTICS\TimeZone;

/**
 * Entity to record session-related information.
 */
class Session extends BaseEntity
{
    /**
     * Entity for creating and managing visitor sessions.
     *
     * This handles the logic for either opening a new session or
     * updating an existing open session, and tracking visitor activity
     * such as total views, resource changes, and session metadata.
     */
    public function record()
    {
        if (!$this->isActive('sessions')) {
            return $this;
        }

        $visitorId = $this->profile->getVisitorIdMeta();

        if (!$visitorId) {
            return $this;
        }

        $cacheKey = 'session_' . $visitorId;

        $sessionId = $this->getCachedData($cacheKey, function () use ($visitorId) {
            $existing = $this->existToday();

            if ($existing && isset($existing->ID)) {
                $newViews = ((int)$existing->total_views) + 1;
                $userId   = empty($existing->user_id) ? $this->profile->getUserId() : $existing->user_id;

                RecordFactory::session($existing)->update(
                    [
                        'total_views' => $newViews,
                        'user_id'     => $userId,
                    ],
                );
                return (int)$existing->ID;
            }

            return (int)RecordFactory::session()->insert([
                'visitor_id'                => $visitorId,
                'ip'                        => $this->profile->getProcessedIPForStorage(),
                'referrer_id'               => $this->profile->getReferrerId(),
                'country_id'                => $this->profile->getCountryId(),
                'city_id'                   => $this->profile->getCityId(),
                'initial_view_id'           => $this->profile->getViewId(),
                'last_view_id'              => $this->profile->getViewId(),
                'total_views'               => 1,
                'device_type_id'            => $this->profile->getDeviceTypeId(),
                'device_os_id'              => $this->profile->getDeviceOsId(),
                'device_browser_id'         => $this->profile->getDeviceBrowserId(),
                'device_browser_version_id' => $this->profile->getDeviceBrowserVersionId(),
                'resolution_id'             => $this->profile->getResolutionId(),
                'language_id'               => $this->profile->getLanguageId(),
                'timezone_id'               => $this->profile->getTimezoneId(),
                'user_id'                   => $this->profile->getUserId(),
                'started_at'                => TimeZone::getCurrentDateByUTC('Y-m-d H:i:s'),
            ]);
        });

        $this->profile->setSessionId($sessionId);
        return $this;
    }

    /**
     * Check if a session exists today for the current visitor.
     *
     * If a session exists where started_at is today, it is reused.
     *
     * @return object|false
     */
    public function existToday()
    {
        $visitorId = $this->profile->getVisitorIdMeta();

        if (!$visitorId) {
            return false;
        }

        $existing = (new SessionModel())->getTodaySession([
            'visitor_id' => $visitorId
        ]);

        return ($existing && isset($existing->ID)) ? $existing : false;
    }

    /**
     * Update the current session's view tracking information.
     *
     * This method sets the `last_view_id` and `ended_at` timestamp for the session.
     * If the session does not yet have an `initial_view_id`, it will be set to the provided `$viewId`.
     *
     * @param int $viewId The ID of the most recent view in the session.
     * @param string $endAt The datetime string (Y-m-d H:i:s) when the session is considered ended.
     *
     * @return void
     */
    public function updateInitialView($viewId, $endAt)
    {
        $sessionId = $this->profile->getSessionId();

        if (!$sessionId || $viewId < 1) {
            return;
        }

        $session = RecordFactory::session()->get(['ID' => $sessionId]);

        if (!$session || !isset($session->ID)) {
            return;
        }

        $updates = [
            'last_view_id' => $viewId,
            'ended_at'     => $endAt,
            'duration'     => $this->calculateDuration($endAt, $session->started_at)
        ];

        if (empty($session->initial_view_id)) {
            $updates['initial_view_id'] = $viewId;
        }

        RecordFactory::session($session)->update($updates);
    }

    /**
     * Calculate session duration in seconds.
     *
     * @param string $endAt The end timestamp.
     * @param string $startedAt The start timestamp.
     * @return int Duration in seconds, or 0 if invalid.
     */
    public function calculateDuration($endAt, $startedAt)
    {
        $start = strtotime($startedAt);
        $end   = strtotime($endAt);

        if ($start === false || $end === false || $end <= $start) {
            return 0;
        }

        return $end - $start;
    }
}
