<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Models\SessionModel;
use WP_Statistics\Records\SessionRecord;
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
        if (! $this->isActive('sessions')) {
            return $this;
        }

        $visitorId = $this->profile->getVisitorIdMeta();

        if (!$visitorId) {
            return $this;
        }

        $cacheKey = 'session_' . $visitorId;

        $sessionId = $this->getCachedData($cacheKey, function () use ($visitorId) {
            $model = new SessionRecord();

            // Try to find an existing "open" session for this visitor.
            $existing = $this->existToday();

            if ($existing && isset($existing->ID)) {
                $model = new SessionRecord($existing);
                // increment existing session views
                $newViews      = ((int)$existing->total_views) + 1;
                $lastResouceId = $this->profile->getResourceId();

                $model->update(
                    [
                        'total_views'     => $newViews,
                        'last_resouce_id' => $lastResouceId,
                    ],
                );
                return (int)$existing->ID;
            }

            $initialViewId = $this->profile->getResourceId();

            // Otherwise, insert a brand-new session row
            return (int)$model->insert([
                'visitor_id'                => $visitorId,
                'ip'                        => $this->profile->getProcessedIPForStorage(),
                'referrer_id'               => $this->profile->getReferrerId(),
                'country_id'                => $this->profile->getCountryId(),
                'city_id'                   => $this->profile->getCityId(),
                'initial_resource_id'       => $initialViewId,
                'last_resouce_id'           => 0,
                'total_views'               => 1,
                'device_type_id'            => $this->profile->getDeviceTypeId(),
                'device_os_id'              => $this->profile->getDeviceOsId(),
                'device_browser_id'         => $this->profile->getDeviceBrowserId(),
                'device_browser_version_id' => $this->profile->getDeviceBrowserVersionId(),
                'resolution_id'             => $this->profile->getResolutionId(),
                'language_id'               => $this->profile->getLanguageId(),
                'timezone_id'               => $this->profile->getTimezoneId(),
                'user_id'                   => $this->profile->getUserId(),
                'started_at'                => TimeZone::getCurrentDate('Y-m-d H:i:s'),
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
     * @todo: update existing functionality.
     */
    public function existToday()
    {
        $visitorId = $this->profile->getVisitorIdMeta();

        if (!$visitorId) {
            return false;
        }

        $model = new SessionRecord();

        $existing = (new SessionModel())->getTodaySession([
            'visitor_id' => $visitorId
        ]);

        return ($existing && isset($existing->ID)) ? $existing : false;
    }
}
