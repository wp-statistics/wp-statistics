<?php

namespace WP_Statistics\Service\Integrations\Multilingual;

/**
 * Abstract base class for multilingual plugin integrations.
 *
 * Provides a common interface for integrating with various multilingual
 * plugins like WPML, Polylang, TranslatePress, etc.
 *
 * This abstraction allows WP Statistics to:
 * - Normalize translated content IDs to their original (default language) versions
 * - Track which language visitors are viewing content in
 * - Aggregate statistics across all translations of the same content
 *
 * To add support for a new multilingual plugin:
 * 1. Create a new class extending AbstractMultilingual
 * 2. Implement all abstract methods
 * 3. Register the class in MultilingualHelper::$integrations
 *
 * @package WP_Statistics\Service\Integrations\Multilingual
 * @since 15.0.0
 */
abstract class AbstractMultilingual
{
    /**
     * Unique key identifier for this integration.
     *
     * @var string
     */
    protected $key;

    /**
     * Plugin file path relative to plugins directory.
     *
     * @var string
     */
    protected $path;

    /**
     * Get the integration key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get the plugin file path.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Check if the multilingual plugin is active.
     *
     * @return bool
     */
    public function isActive()
    {
        return is_plugin_active($this->getPath());
    }

    /**
     * Get the display name of the multilingual plugin.
     *
     * @return string
     */
    abstract public function getName();

    /**
     * Get the current language code.
     *
     * @return string|null Language code (e.g., 'en', 'fr', 'de') or null if not available.
     */
    abstract public function getCurrentLanguage();

    /**
     * Get the default/primary language code.
     *
     * @return string|null Default language code or null if not available.
     */
    abstract public function getDefaultLanguage();

    /**
     * Get all active languages.
     *
     * @return array Array of language codes.
     */
    abstract public function getActiveLanguages();

    /**
     * Get the original/default language post ID for a translated post.
     *
     * This is useful for aggregating statistics across all translations
     * of the same content.
     *
     * @param int         $postId   The post ID to get the original for.
     * @param string|null $postType The post type. If null, it will be detected.
     *
     * @return int The original post ID, or the same ID if it's already the original.
     */
    abstract public function getOriginalPostId($postId, $postType = null);

    /**
     * Get the original/default language term ID for a translated term.
     *
     * @param int    $termId   The term ID to get the original for.
     * @param string $taxonomy The taxonomy slug.
     *
     * @return int The original term ID, or the same ID if it's already the original.
     */
    abstract public function getOriginalTermId($termId, $taxonomy);

    /**
     * Get all translation IDs for a given post.
     *
     * @param int         $postId   The post ID.
     * @param string|null $postType The post type. If null, it will be detected.
     *
     * @return array Array of post IDs (including the original).
     */
    abstract public function getPostTranslationIds($postId, $postType = null);

    /**
     * Get all translation IDs for a given term.
     *
     * @param int    $termId   The term ID.
     * @param string $taxonomy The taxonomy slug.
     *
     * @return array Array of term IDs (including the original).
     */
    abstract public function getTermTranslationIds($termId, $taxonomy);

    /**
     * Get the language code for a specific post.
     *
     * @param int $postId The post ID.
     *
     * @return string|null Language code or null if not available.
     */
    abstract public function getPostLanguage($postId);

    /**
     * Get the language code for a specific term.
     *
     * @param int    $termId   The term ID.
     * @param string $taxonomy The taxonomy slug.
     *
     * @return string|null Language code or null if not available.
     */
    abstract public function getTermLanguage($termId, $taxonomy);

    /**
     * Get the translated post ID for the current language.
     *
     * Given a post ID, returns the ID of its translation in the current
     * front-end language. If no translation exists, returns the original ID.
     *
     * @param int         $postId   The post ID.
     * @param string|null $postType The post type.
     *
     * @return int The translated post ID, or the same ID if no translation exists.
     */
    abstract public function getTranslatedPostId($postId, $postType = null);

    /**
     * Get the translated term ID for the current language.
     *
     * Given a term ID, returns the ID of its translation in the current
     * front-end language. If no translation exists, returns the original ID.
     *
     * @param int    $termId   The term ID.
     * @param string $taxonomy The taxonomy slug.
     *
     * @return int The translated term ID, or the same ID if no translation exists.
     */
    abstract public function getTranslatedTermId($termId, $taxonomy);

    /**
     * Check if a post is the original (default language) version.
     *
     * @param int         $postId   The post ID to check.
     * @param string|null $postType The post type. If null, it will be detected.
     *
     * @return bool True if it's the original, false if it's a translation.
     */
    public function isOriginalPost($postId, $postType = null)
    {
        return $postId === $this->getOriginalPostId($postId, $postType);
    }

    /**
     * Check if a term is the original (default language) version.
     *
     * @param int    $termId   The term ID to check.
     * @param string $taxonomy The taxonomy slug.
     *
     * @return bool True if it's the original, false if it's a translation.
     */
    public function isOriginalTerm($termId, $taxonomy)
    {
        return $termId === $this->getOriginalTermId($termId, $taxonomy);
    }

    /**
     * Register any hooks or filters needed for this integration.
     *
     * @return void
     */
    public function register()
    {
        // Override in child classes if needed
    }
}
