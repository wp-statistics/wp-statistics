<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Utils\QueryParams;
use WP_Statistics\Utils\Uri;

/**
 * Entity for detecting and recording view parameters from the request URI.
 *
 * This includes parameters attached to the current page URL, excluding known tracking parameters.
 *
 * @since 15.0.0
 */
class Parameter extends BaseEntity
{
    /**
     * Detect and record URL query parameters related to the current view.
     *
     * Removes known tracking parameters such as utm_source, ref, etc.
     * and saves the clean parameters associated with session, resourceUri, and view.
     *
     * @return $this
     */
    public function record()
    {
        if (!$this->isActive('parameters')) {
            return $this;
        }

        $sessionId     = $this->profile->getSessionId();
        $resourceUriId = $this->profile->getResourceUriId();
        $viewId        = $this->profile->getViewId();

        if (!$sessionId || !$resourceUriId || !$viewId) {
            return $this;
        }

        $allowedParams = QueryParams::getAllowedList('array', true);
        $pageUri       = Uri::getByVisitor($this->profile);

        $queryParams = [];

        if (strpos($pageUri, '?') !== false) {
            list(, $queryString) = explode('?', $pageUri, 2);
            parse_str($queryString, $queryParams);
        }

        if (empty($queryParams) || !is_array($queryParams)) {
            return $this;
        }

        $filteredParams = array_intersect_key($queryParams, array_flip($allowedParams));

        if (empty($filteredParams)) {
            return $this;
        }

        foreach ($filteredParams as $key => $value) {
            $existingRecord = RecordFactory::parameter()->get([
                'session_id'      => $sessionId,
                'view_id'         => $viewId,
                'resource_uri_id' => $resourceUriId,
                'parameter'       => $key
            ]);

            if (!empty($existingRecord)) {
                continue;
            }

            RecordFactory::parameter()->insert([
                'session_id'      => $sessionId,
                'resource_uri_id' => $resourceUriId,
                'view_id'         => $viewId,
                'parameter'       => sanitize_text_field($key),
                'value'           => sanitize_textarea_field($value),
            ]);
        }

        return $this;
    }
}
