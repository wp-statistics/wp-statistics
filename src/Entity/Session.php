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
     * Record a session: reuse an active one or create a new one.
     *
     * This is the standard entity entry point following the convention
     * used by Visitor, Device, Geo, etc. The Tracker calls getActive()
     * and create() directly for finer control over dimension
     * resolution, but external callers can use this.
     *
     * @param int   $visitorId  The visitor ID.
     * @param array $deviceIds  Device-related IDs.
     * @param array $geoIds     Geographic IDs (country_id, city_id).
     * @param array $localeIds  Locale IDs (language_id, timezone_id).
     * @param int   $referrerId The referrer ID.
     * @return int The session ID, or 0 if tracking is inactive.
     */
    public function record(int $visitorId, array $deviceIds, array $geoIds, array $localeIds, int $referrerId): int
    {
        if (!$this->isActive('sessions') || !$visitorId) {
            return 0;
        }

        $activeSession = $this->getActive($visitorId);

        return $activeSession
            ? (int) $activeSession->ID
            : $this->create($visitorId, $deviceIds, $geoIds, $localeIds, $referrerId);
    }

    /**
     * Check if an active session exists for the given visitor.
     *
     * Returns a session that started today and is either still active
     * or ended within the last 30 minutes.
     *
     * @param int $visitorId The visitor ID to check.
     * @return object|false The session record, or false if none is active.
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
     * Create a new session with all dimension references.
     *
     * @param int   $visitorId  The visitor ID.
     * @param array $deviceIds  Device-related IDs (type_id, os_id, browser_id, browser_version_id, resolution_id).
     * @param array $geoIds     Geographic IDs (country_id, city_id).
     * @param array $localeIds  Locale IDs (language_id, timezone_id).
     * @param int   $referrerId The referrer ID.
     * @return int The new session ID.
     */
    public function create(int $visitorId, array $deviceIds, array $geoIds, array $localeIds, int $referrerId): int
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
     * Update the session after a view has been recorded.
     *
     * Always sets last_view_id, ended_at, and conditionally initial_view_id.
     *
     * For reused sessions ($activeSession provided): also increments total_views,
     * sets user_id, and optionally updates dimension FKs (last-touch attribution).
     *
     * For new sessions ($activeSession is null): only updates view tracking fields
     * since dimensions were already set during create().
     *
     * @param int         $sessionId     The session ID.
     * @param int         $viewId        The new view's ID.
     * @param object|null $activeSession The reused session record, or null for new sessions.
     * @param array|null  $deviceIds     Device IDs for last-touch, or null to skip.
     * @param array|null  $geoIds        Geo IDs for last-touch, or null to skip.
     * @param array|null  $localeIds     Locale IDs for last-touch, or null to skip.
     * @param int|null    $referrerId    Referrer ID for last-touch, or null to skip.
     * @return void
     */
    public function update(
        int $sessionId,
        int $viewId,
        ?object $activeSession = null,
        ?array $deviceIds = null,
        ?array $geoIds = null,
        ?array $localeIds = null,
        ?int $referrerId = null
    ): void {
        if (!$sessionId || $viewId < 1) {
            return;
        }

        // Build all SET values before where() — the Query builder shares a
        // single $valuesToPrepare array, so ordering matters for alignment.
        $data = [
            'last_view_id' => $viewId,
            'ended_at'     => DateTime::getUtc(),
        ];

        if ($activeSession) {
            $data['total_views'] = ((int) $activeSession->total_views) + 1;
            $data['user_id']     = empty($activeSession->user_id) ? $this->visitor->getUserId() : $activeSession->user_id;

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
        }

        Query::update('sessions')
            ->set($data)
            ->setRaw('initial_view_id', sprintf(
                'CASE WHEN `initial_view_id` = 0 OR `initial_view_id` IS NULL THEN %d ELSE `initial_view_id` END',
                $viewId
            ))
            ->where('ID', '=', $sessionId)
            ->execute();
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
}
