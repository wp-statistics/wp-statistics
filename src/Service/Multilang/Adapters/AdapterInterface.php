<?php

namespace WP_Statistics\Service\Multilang\Adapters;

/**
 * Contract implemented by every multi-language plugin adapter.
 *
 * Adapters bridge a third-party multi-language plugin (Polylang, WPML, …) into
 * a normalized API the rest of WP Statistics can rely on regardless of which
 * plugin is in use.
 *
 * @since 15.x
 */
interface AdapterInterface
{
    /**
     * Per-post mode — each translation has its own post_id (Polylang, WPML).
     * The (resource_id, resource_type) pair stays the unique identity for a
     * resource; language is a post-attribute we forward-fill on existing rows.
     */
    public const MODE_PER_POST = 'per-post';

    /**
     * Per-request mode — one post_id, language switched at request time
     * (TranslatePress, qTranslate-X, WeGlot). Language is part of the resource
     * identity, so each translation gets its own row.
     */
    public const MODE_PER_REQUEST = 'per-request';

    /**
     * Stable identifier for this adapter (e.g. 'polylang', 'wpml').
     *
     * Used for storage, filters, and the wp_statistics_multilang_adapter override.
     */
    public function getSlug(): string;

    /**
     * Human-readable name for this adapter (e.g. 'Polylang').
     */
    public function getName(): string;

    /**
     * How the underlying plugin assigns language to a resource.
     *
     * Returns one of self::MODE_PER_POST or self::MODE_PER_REQUEST.
     */
    public function getMode(): string;

    /**
     * Whether the underlying plugin is currently loaded and usable.
     */
    public function isActive(): bool;

    /**
     * Resolve the language code for a hit.
     *
     * @param string $resourceType The resource type (e.g. 'post', 'page', 'home', 'category').
     * @param int    $resourceId   The logical resource ID, or 0 for non-post contexts.
     * @param string $uri          The URI path (some adapters parse a language prefix from it).
     *
     * @return string|null The language code (typically 'en', 'fr', or 'pt-br'), or null
     *                     when no language could be determined.
     */
    public function detectLanguage(string $resourceType, int $resourceId, string $uri): ?string;

    /**
     * Site default language code, or null if the plugin doesn't expose one.
     */
    public function getDefaultLanguage(): ?string;

    /**
     * Map of code => human label for every language configured in the plugin.
     *
     * Example: ['en' => 'English', 'fr' => 'Français'].
     *
     * @return array<string, string>
     */
    public function getAvailableLanguages(): array;
}
