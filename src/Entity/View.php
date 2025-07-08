<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Records\RecordFactory;
use WP_STATISTICS\TimeZone;

/**
 * Entity for recording individual page views by visitors.
 *
 * This handles tracking each view during a session,
 * linking them with next view IDs and calculating duration between views.
 *
 * @since 15.0.0
 */
class View extends BaseEntity
{
    /**
     * Record a new view for the current visitor session and resourceUri.
     *
     * - If there is a previous view, update its `next_view_id` and calculate its `duration`.
     * - Insert a new view with the current timestamp.
     *
     * @return $this
     */
    public function record()
    {
        if (!$this->isActive('views')) {
            return $this;
        }

        $sessionId     = $this->profile->getSessionId();
        $resourceUriId = $this->profile->getResourceUriId();

        if ($sessionId < 1 || $resourceUriId < 1) {
            return $this;
        }

        $previousView = (new ViewsModel())->getLastViewBySessionId([
            'session_id' => $sessionId
        ]);

        $now = TimeZone::getCurrentDateByUTC('Y-m-d H:i:s');

        $data = [
            'session_id'      => $sessionId,
            'resource_uri_id' => $resourceUriId,
            'viewed_at'       => $now,
            'next_view_id'    => null,
            'duration'        => $this->profile->getDuration(),
        ];

        $newViewId = (int)RecordFactory::view()->insert($data);

        if ($previousView && isset($previousView->ID)) {
            $previousViewedAt = strtotime($previousView->viewed_at);
            $currentViewedAt  = strtotime($now);

            $durationSeconds = max(0, $currentViewedAt - $previousViewedAt);
            $durationMillis  = $durationSeconds * 1000;

            // Update previous view.
            RecordFactory::view($previousView)->update([
                'next_view_id' => $newViewId,
                'duration'     => $durationMillis,
            ]);
        }

        $this->profile->setViewId($newViewId);
        EntityFactory::session($this->profile)->updateInitialView($newViewId, $now);
        return $this;
    }
}
