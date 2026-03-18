<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Entity\EntityFactory;
use WP_Statistics\Models\SessionModel;
use WP_Statistics\Records\RecordFactory;

/**
 * Entity to record session-related information.
 *
 * @since 15.0.0
 */
class Session extends BaseEntity
{
    /**
     * Entity for creating and managing visitor sessions.
     *
     * This handles the logic for either opening a new session or
     * updating an existing open session, and tracking visitor activity
     * such as total views, resource changes, and session metadata.
     *
     * @param int   $visitorId  The visitor ID.
     * @param array $deviceIds  Device-related IDs (type_id, os_id, browser_id, browser_version_id, resolution_id).
     * @param array $geoIds     Geographic IDs (country_id, city_id).
     * @param array $localeIds  Locale IDs (language_id, timezone_id).
     * @param int   $referrerId The referrer ID.
     * @return int The session ID, or 0 if tracking is inactive or visitor ID is missing.
     */
    public function record(int $visitorId, array $deviceIds, array $geoIds, array $localeIds, int $referrerId): int
    {
        if (!$this->isActive('sessions')) {
            return 0;
        }

        if (!$visitorId) {
            return 0;
        }

        $activeSession = $this->getActive($visitorId);

        if ($activeSession && isset($activeSession->ID)) {
            $newViews = ((int)$activeSession->total_views) + 1;
            $userId   = empty($activeSession->user_id) ? $this->context->getUserId() : $activeSession->user_id;

            $newData = [
                'total_views' => $newViews,
                'user_id'     => $userId,
            ];

            RecordFactory::session($activeSession)->update($newData);

            return (int)$activeSession->ID;
        }

        $sessionId = (int)RecordFactory::session()->insert([
            'visitor_id'                => $visitorId,
            'referrer_id'               => $referrerId,
            'country_id'                => $geoIds['country_id'],
            'city_id'                   => $geoIds['city_id'],
            'initial_view_id'           => 0,
            'last_view_id'              => 0,
            'total_views'               => 1,
            'device_type_id'            => $deviceIds['type_id'],
            'device_os_id'              => $deviceIds['os_id'],
            'device_browser_id'         => $deviceIds['browser_id'],
            'device_browser_version_id' => $deviceIds['browser_version_id'],
            'resolution_id'             => $deviceIds['resolution_id'],
            'language_id'               => $localeIds['language_id'],
            'timezone_id'               => $localeIds['timezone_id'],
            'user_id'                   => $this->context->getUserId(),
            'started_at'                => DateTime::getUtc(),
        ]);

        // Record UTM parameters for this new session (first-touch attribution)
        EntityFactory::parameter($this->context)->record($sessionId);

        return $sessionId;
    }

    /**
     * Check if an active session exists for the given visitor.
     *
     * Returns a session that started today and is either still active
     * or ended within the last 30 minutes.
     *
     * @param int $visitorId The visitor ID to check.
     * @return object|false
     */
    public function getActive(int $visitorId)
    {
        if (!$visitorId) {
            return false;
        }

        $activeSession = (new SessionModel())->getActiveSession([
            'visitor_id' => $visitorId
        ]);

        return ($activeSession && isset($activeSession->ID)) ? $activeSession : false;
    }

    /**
     * Update the current session's view tracking information.
     *
     * This method sets the `last_view_id` and `ended_at` timestamp for the session.
     * If the session does not yet have an `initial_view_id`, it will be set to the provided `$viewId`.
     *
     * @param int    $sessionId The session ID to update.
     * @param int    $viewId    The ID of the most recent view in the session.
     * @param string $endAt     The datetime string (Y-m-d H:i:s) when the session is considered ended.
     *
     * @return void
     */
    public function updateInitialView(int $sessionId, int $viewId, string $endAt)
    {
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
