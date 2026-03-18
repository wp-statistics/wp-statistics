<?php

namespace WP_Statistics\Entity;

use WP_Statistics\Abstracts\BaseEntity;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Utils\QueryParams;

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
     * Record UTM parameters for the given session.
     *
     * This method should only be called when creating a new session
     * to ensure first-touch attribution.
     *
     * @param int $sessionId The session ID to associate parameters with.
     * @return void
     */
    public function record(int $sessionId): void
    {
        if (!$this->isActive('parameters')) {
            return;
        }

        if (!$sessionId) {
            return;
        }

        $resourceUri = $this->context->getRequest()->getResourceUri();

        if (empty($resourceUri)) {
            return;
        }

        $resourceUri = substr($resourceUri, 0, 255);
        $queryParams = [];

        if (strpos($resourceUri, '?') !== false) {
            list(, $queryString) = explode('?', $resourceUri, 2);
            parse_str($queryString, $queryParams);
        }

        if (empty($queryParams) || !is_array($queryParams)) {
            return;
        }

        // Normalize query param keys to lowercase for case-insensitive matching
        $queryParams = array_change_key_case($queryParams, CASE_LOWER);

        // Consolidate source/ref into utm_source (priority: utm_source > source > ref)
        $utmSource = $queryParams['utm_source']
            ?? $queryParams['source']
            ?? $queryParams['ref']
            ?? null;

        // Core UTM params to store (source/ref consolidated into utm_source)
        $paramsToStore = [
            'utm_source'   => $utmSource,
            'utm_medium'   => $queryParams['utm_medium'] ?? null,
            'utm_campaign' => $queryParams['utm_campaign'] ?? null,
            'utm_content'  => $queryParams['utm_content'] ?? null,
            'utm_term'     => $queryParams['utm_term'] ?? null,
            'utm_id'       => $queryParams['utm_id'] ?? null,
        ];

        // Params already handled above (skip when processing custom allow-list)
        $handledParams = [
            'utm_source', 'utm_medium', 'utm_campaign',
            'utm_content', 'utm_term', 'utm_id',
            'source', 'ref',
        ];

        // Store custom allow-list params not already handled by UTM consolidation
        $allowList = QueryParams::getAllowedList('array', true);
        foreach ($allowList as $param) {
            $paramLower = strtolower($param);
            if (!in_array($paramLower, $handledParams, true) && isset($queryParams[$paramLower]) && is_string($queryParams[$paramLower]) && $queryParams[$paramLower] !== '') {
                $paramsToStore[$paramLower] = $queryParams[$paramLower];
            }
        }

        // Insert each non-null parameter
        foreach ($paramsToStore as $key => $value) {
            if ($value !== null && $value !== '' && is_string($value)) {
                RecordFactory::parameter()->insert([
                    'session_id' => $sessionId,
                    'parameter'  => strtolower($key),
                    'value'      => sanitize_text_field(mb_substr($value, 0, 255)),
                ]);
            }
        }
    }
}
