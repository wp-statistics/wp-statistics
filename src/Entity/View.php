<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Models\ViewModel;
use WP_Statistics\Records\ViewRecord;
use WP_STATISTICS\TimeZone;

/**
 * Entity for recording individual page views by visitors.
 *
 * This handles tracking each view during a session,
 * linking them with next view IDs and calculating duration between views.
 */
class View extends BaseEntity
{
    /**
     * Record a new view for the current visitor session and resource.
     *
     * - If there is a previous view, update its `next_view_id` and calculate its `duration`.
     * - Insert a new view with the current timestamp.
     *
     * @return $this
     */
    public function record()
    {
        if (! $this->isActive('views')) {
            return $this;
        }

        $sessionId  = $this->profile->getSessionId();
        $resourceId = $this->profile->getResourceId();

        if ($sessionId < 1 || $resourceId < 1) {
            return $this;
        }

        $viewModel = new ViewRecord();

        $previousView = (new ViewModel())->getLastViewBySessionId([
            'session_id' => $sessionId
        ]);

        $now = TimeZone::getCurrentDate('Y-m-d H:i:s');

        $data = [
            'session_id'   => $sessionId,
            'resource_id'  => $resourceId,
            'viewed_at'    => $now,
            'next_view_id' => null,
            'duration'     => $this->profile->getDuration(),
        ];

        $newViewId = (int)$viewModel->insert($data);

        if ($previousView && isset($previousView->ID)) {
            $previousViewedAt = strtotime($previousView->viewed_at);
            $currentViewedAt  = strtotime($now);

            $durationSeconds = max(0, $currentViewedAt - $previousViewedAt);
            $durationMillis  = $durationSeconds * 1000;

            // Update previous view
            $viewModel = new ViewRecord($previousView);
            $viewModel->update([
                'next_view_id' => $newViewId,
                'duration'     => $durationMillis,
            ]);
        }

        $this->profile->setViewId($newViewId);
        return $this;
    }
}
