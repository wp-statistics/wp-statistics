<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Records\RecordFactory;

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
     * @param int $sessionId The session ID to associate the view with.
     * @return int The new view ID, or 0 if tracking is inactive or inputs are invalid.
     */
    public function record(int $sessionId): int
    {
        if (!$this->isActive('views')) {
            return 0;
        }

        $resourceUriId = $this->visitor->getRequest()->getResourceUriId();

        if ($sessionId < 1 || $resourceUriId < 1) {
            return 0;
        }

        $previousView = (new ViewsModel())->getLastViewBySessionId([
            'session_id' => $sessionId
        ]);

        $now = DateTime::getUtc();

        $data = [
            'session_id'      => $sessionId,
            'resource_uri_id' => $resourceUriId,
            'viewed_at'       => $now,
            'next_view_id'    => null,
            'duration'        => 0,
            'resource_id'     => $this->visitor->getRequest()->getResourceId(),
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

        EntityFactory::session($this->visitor)->updateInitialView($sessionId, $newViewId, $now);
        return $newViewId;
    }
}
