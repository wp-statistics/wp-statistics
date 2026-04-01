<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Entity\EntityFactory;
use WP_Statistics\Models\SessionModel;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Utils\Query;

/**
 * Entity to record session-related information.
 *
 * @since 15.0.0
 */
class Session extends BaseEntity
{
    /**
     * Record a session: reuse active or create new.
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

        if ($activeSession) {
            return $this->reuseSession($activeSession);
        }

        return $this->createSession($visitorId, $deviceIds, $geoIds, $localeIds, $referrerId);
    }

    /**
     * Reuse an existing active session by incrementing its view count.
     *
     * When dimension arrays are provided (last-touch mode), the session's
     * device, geo, locale, and referrer FKs are updated to reflect the
     * visitor's current state. Otherwise only the view count is incremented.
     *
     * @param object     $activeSession The active session record.
     * @param array|null $deviceIds     Device IDs to update (last-touch), or null to skip.
     * @param array|null $geoIds        Geo IDs to update (last-touch), or null to skip.
     * @param array|null $localeIds     Locale IDs to update (last-touch), or null to skip.
     * @param int|null   $referrerId    Referrer ID to update (last-touch), or null to skip.
     * @return int The session ID.
     */
    public function reuseSession(
        object $activeSession,
        ?array $deviceIds = null,
        ?array $geoIds = null,
        ?array $localeIds = null,
        ?int $referrerId = null
    ): int {
        $newViews = ((int)$activeSession->total_views) + 1;
        $userId   = empty($activeSession->user_id) ? $this->visitor->getUserId() : $activeSession->user_id;

        $data = [
            'total_views' => $newViews,
            'user_id'     => $userId,
        ];

        // Last-touch attribution: update dimension FKs if provided
        if ($deviceIds !== null) {
            $data['device_type_id']            = $deviceIds['type_id'];
            $data['device_os_id']              = $deviceIds['os_id'];
            $data['device_browser_id']         = $deviceIds['browser_id'];
            $data['device_browser_version_id'] = $deviceIds['browser_version_id'];
            $data['resolution_id']             = $deviceIds['resolution_id'];
        }

        if ($geoIds !== null) {
            $data['country_id'] = $geoIds['country_id'];
            $data['city_id']    = $geoIds['city_id'];
        }

        if ($localeIds !== null) {
            $data['language_id'] = $localeIds['language_id'];
            $data['timezone_id'] = $localeIds['timezone_id'];
        }

        if ($referrerId !== null) {
            $data['referrer_id'] = $referrerId;
        }

        RecordFactory::session($activeSession)->update($data);

        return (int)$activeSession->ID;
    }

    /**
     * Create a new session with all dimension references.
     *
     * @param int   $visitorId  The visitor ID.
     * @param array $deviceIds  Device-related IDs.
     * @param array $geoIds     Geographic IDs (country_id, city_id).
     * @param array $localeIds  Locale IDs (language_id, timezone_id).
     * @param int   $referrerId The referrer ID.
     * @return int The new session ID.
     */
    public function createSession(int $visitorId, array $deviceIds, array $geoIds, array $localeIds, int $referrerId): int
    {
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
            'user_id'                   => $this->visitor->getUserId(),
            'started_at'                => DateTime::getUtc(),
            'duration'                  => 0,
        ]);

        // Record UTM parameters for this new session (first-touch attribution)
        EntityFactory::parameter($this->visitor)->record($sessionId);

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
     * Atomically increment the engagement duration for the visitor's active session.
     *
     * Uses the visitor's hashed IP to find the active session, then increments
     * the duration with SQL COALESCE + addition (atomic, no race conditions).
     *
     * @param int $engagementTimeMs Engagement time in milliseconds.
     * @return bool True if a session was found and updated.
     * @since 15.0.0
     */
    public function updateEngagement(int $engagementTimeMs): bool
    {
        $engagementTimeSec = (int) round($engagementTimeMs / 1000);

        if ($engagementTimeSec < 1) {
            return false;
        }

        $session = (new SessionModel())->getActiveSessionByHash($this->visitor->getHashedIp());

        if (!$session || empty($session->ID)) {
            return false;
        }

        Query::update('sessions')
            ->set(['ended_at' => DateTime::getUtc()])
            ->setRaw('duration', 'COALESCE(`duration`, 0) + ' . intval($engagementTimeSec))
            ->where('ID', '=', $session->ID)
            ->execute();

        return true;
    }

    /**
     * Update the current session's view tracking information.
     *
     * Sets `last_view_id` and `ended_at` on every call. Conditionally sets
     * `initial_view_id` only when it is still 0 (new session's first view),
     * using a single UPDATE with a CASE expression to avoid a separate SELECT.
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

        Query::update('sessions')
            ->set([
                'last_view_id' => $viewId,
                'ended_at'     => $endAt,
            ])
            ->setRaw('initial_view_id', sprintf(
                'CASE WHEN `initial_view_id` = 0 OR `initial_view_id` IS NULL THEN %d ELSE `initial_view_id` END',
                $viewId
            ))
            ->where('ID', '=', $sessionId)
            ->execute();
    }

}
