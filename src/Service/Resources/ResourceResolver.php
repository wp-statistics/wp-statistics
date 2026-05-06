<?php

namespace WP_Statistics\Service\Resources;

use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Service\Multilang\MultilangService;

/**
 * Resolves resource and resource_uri records from raw parameters.
 *
 * Unlike ResourceManager which relies on WordPress page context (query loop,
 * conditional tags), this class works with explicit parameters — making it
 * suitable for headless clients and any context where the resource data
 * is provided directly rather than detected from the current request.
 *
 * Multi-language behavior (when a Multilang plugin is active):
 *   - per-post adapters (Polylang, WPML): language is a post-attribute. The
 *     (resource_id, resource_type) pair stays the unique key; we forward-fill
 *     `language` on the existing row whenever it's still empty.
 *   - per-request adapters (TranslatePress, qTranslate, WeGlot): language is
 *     part of the resource identity, so each (resource_id, resource_type, language)
 *     combination gets its own row.
 *
 * Atomicity: both the resource and the resource_uri insertions go through
 * upsert primitives that rely on UNIQUE constraints — concurrent calls cannot
 * create duplicate rows.
 */
class ResourceResolver
{
    /**
     * Find or create a resource_uri record from raw parameters.
     *
     * @param int|null $resourceId   The logical resource ID (e.g. post ID).
     * @param string   $resourceType The resource type (e.g. 'post', 'page').
     * @param string   $uri          The URI path (e.g. '/hello-world').
     * @return int The resource_uri record ID, or 0 on failure.
     */
    public static function resolveUriId(?int $resourceId, string $resourceType, string $uri): int
    {
        $multilang  = MultilangService::getInstance();
        $detected   = $multilang->detectLanguage($resourceType, (int) $resourceId, $uri);
        $isIdentity = $multilang->languageIsIdentity((int) $resourceId);

        // In per-post mode (non-identity), language is a stable post attribute, not
        // part of the identity tuple. A pre-existing row with any non-empty language
        // is authoritative — we must not create a second row for a different language.
        // Short-circuit here so the upsert step doesn't produce a duplicate row.
        if (!$isIdentity && $detected !== null) {
            $existing = RecordFactory::resource()->get([
                'resource_id'   => (int) $resourceId,
                'resource_type' => $resourceType,
            ]);
            if (!empty($existing) && $existing->language !== '') {
                return RecordFactory::resourceUri()->findOrCreate((int) $existing->ID, $uri);
            }
        }

        // Identity tuple shape: per-request mode and resource_id=0 use language as
        // part of identity; per-post mode forward-fills language onto the
        // (resource_id, resource_type) row.
        $rowLanguage = $detected ?? '';

        // Atomic upsert: insert if missing, or reuse the row and (in per-post mode)
        // fill an empty language without overwriting a real value.
        $resourceRowId = RecordFactory::resource()->upsertWithLanguageFill(
            (int) $resourceId,
            $resourceType,
            $rowLanguage
        );

        if ($resourceRowId < 1) {
            return 0;
        }

        return RecordFactory::resourceUri()->findOrCreate($resourceRowId, $uri);
    }
}
