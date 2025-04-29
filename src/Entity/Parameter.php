<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Pages;
use WP_Statistics\Records\ParameterRecord;

/**
 * Entity for detecting and recording view parameters from the request URI.
 *
 * This includes parameters attached to the current page URL, excluding known tracking parameters.
 */
class Parameter extends BaseEntity
{
    /**
     * Detect and record URL query parameters related to the current view.
     *
     * Removes known tracking parameters such as utm_source, ref, etc.
     * and saves the clean parameters associated with session, resource, and view.
     *
     * @return $this
     */
    public function record()
    {
        if (! $this->isActive('parameters')) {
            return $this;
        }

        $sessionId  = $this->profile->getSessionId();
        $resourceId = $this->profile->getResourceId();
        $viewId     = $this->profile->getViewId();

        if (!$sessionId || !$resourceId || !$viewId) {
            return $this;
        }

        $allowedParams = Helper::get_query_params_allow_list();
        $pageUri       = Pages::sanitize_page_uri($this->profile);

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

        $model = new ParameterRecord();

        foreach ($filteredParams as $key => $value) {
            $model->insert([
                'session_id'  => $sessionId,
                'resource_id' => $resourceId,
                'view_id'     => $viewId,
                'parameter'   => sanitize_text_field($key),
                'value'       => sanitize_textarea_field($value),
            ]);
        }

        return $this;
    }
}
