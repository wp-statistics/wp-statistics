<?php

namespace WP_Statistics\Service\Resources;

use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Service\Multilang\Adapters\AdapterInterface;
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
 *     `language` on the existing row whenever it's still NULL.
 *   - per-request adapters (TranslatePress, qTranslate, WeGlot): language is
 *     part of the resource identity, so each (resource_id, resource_type, language)
 *     combination gets its own row.
 */
class ResourceResolver
{
    /**
     * Find or create a resource_uri record from raw parameters.
     *
     * Looks up the resource by (resource_id, resource_type [, language]),
     * creating it if needed, then looks up the URI record by (resource.ID, uri),
     * creating it if needed.
     *
     * @param int|null $resourceId   The logical resource ID (e.g. post ID).
     * @param string   $resourceType The resource type (e.g. 'post', 'page').
     * @param string   $uri          The URI path (e.g. '/hello-world').
     * @return int The resource_uri record ID, or 0 on failure.
     */
    public static function resolveUriId(?int $resourceId, string $resourceType, string $uri): int
    {
        $multilang = MultilangService::getInstance();
        $language  = $multilang->detectLanguage($resourceType, (int) $resourceId, $uri);
        $mode      = $multilang->getMode();

        $resource = self::findResource($resourceId, $resourceType, $language, $mode);

        if (!empty($resource)) {
            $resourceRowId = (int) $resource->ID;

            // Per-post forward-fill: existing rows that pre-date plugin install
            // get their language filled on the next hit, no migration job needed.
            if ($mode === AdapterInterface::MODE_PER_POST && $language !== null && empty($resource->language)) {
                RecordFactory::resource($resource)->update(['language' => $language]);
            }
        } else {
            $insertData = [
                'resource_id'   => $resourceId,
                'resource_type' => $resourceType,
            ];
            if ($language !== null) {
                $insertData['language'] = $language;
            }

            $resourceRowId = (int) RecordFactory::resource()->insert($insertData);
        }

        if ($resourceRowId < 1) {
            return 0;
        }

        $uriRecord = RecordFactory::resourceUri()->get([
            'resource_id' => $resourceRowId,
            'uri'         => $uri,
        ]);

        if (!empty($uriRecord)) {
            return (int) $uriRecord->ID;
        }

        return (int) RecordFactory::resourceUri()->insert([
            'resource_id' => $resourceRowId,
            'uri'         => $uri,
        ]);
    }

    /**
     * Look up the existing resource row.
     *
     * Language is part of identity in per-request mode (TRP, WeGlot, qTranslate)
     * AND for resources with no underlying post (home, search, archives) — see
     * MultilangService::languageIsIdentity().
     */
    private static function findResource(?int $resourceId, string $resourceType, ?string $language, ?string $mode)
    {
        $args = [
            'resource_id'   => $resourceId,
            'resource_type' => $resourceType,
        ];

        if ($language !== null && MultilangService::getInstance()->languageIsIdentity((int) $resourceId)) {
            $args['language'] = $language;
        }

        return RecordFactory::resource()->get($args);
    }
}
