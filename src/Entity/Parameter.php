<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Utils\Uri;

/**
 * Entity for recording UTM parameters at the session level.
 *
 * Parameters are recorded once per session (first-touch attribution).
 * Consolidates source/ref into utm_source for unified campaign tracking.
 *
 * @since 15.0.0
 */
class Parameter extends BaseEntity
{
    /**
     * Record UTM parameters for the current session.
     *
     * This method should only be called when creating a new session
     * to ensure first-touch attribution.
     *
     * @return $this
     */
    public function record()
    {
        if (!$this->isActive('parameters')) {
            return $this;
        }

        $sessionId = $this->profile->getSessionId();

        if (!$sessionId) {
            return $this;
        }

        $resourceUri = Uri::getByVisitor($this->profile);
        $queryParams = [];

        if (strpos($resourceUri, '?') !== false) {
            list(, $queryString) = explode('?', $resourceUri, 2);
            parse_str($queryString, $queryParams);
        }

        if (empty($queryParams) || !is_array($queryParams)) {
            return $this;
        }

        // Consolidate source/ref into utm_source (priority: utm_source > source > ref)
        $utmSource = $queryParams['utm_source']
            ?? $queryParams['source']
            ?? $queryParams['ref']
            ?? null;

        // Build final params to store
        $paramsToStore = [
            'utm_source'   => $utmSource,
            'utm_medium'   => $queryParams['utm_medium'] ?? null,
            'utm_campaign' => $queryParams['utm_campaign'] ?? null,
            'utm_content'  => $queryParams['utm_content'] ?? null,
            'utm_term'     => $queryParams['utm_term'] ?? null,
            'utm_id'       => $queryParams['utm_id'] ?? null,
        ];

        // Insert each non-null parameter
        foreach ($paramsToStore as $key => $value) {
            if ($value !== null && $value !== '') {
                RecordFactory::parameter()->insert([
                    'session_id' => $sessionId,
                    'parameter'  => $key,
                    'value'      => sanitize_text_field($value),
                ]);
            }
        }

        return $this;
    }
}
