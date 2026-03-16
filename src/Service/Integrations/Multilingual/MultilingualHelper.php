<?php

namespace WP_Statistics\Service\Integrations\Multilingual;

/**
 * Static helper/facade for multilingual plugin integrations.
 *
 * Provides a unified API to interact with supported multilingual plugins.
 * Automatically detects which multilingual plugin is active and delegates
 * method calls to the appropriate integration class.
 *
 * Usage:
 * ```php
 * // Check if any multilingual plugin is active
 * if (MultilingualHelper::isMultilingualActive()) {
 *     $language = MultilingualHelper::getCurrentLanguage();
 *     $originalId = MultilingualHelper::getOriginalPostId($translatedPostId);
 * }
 * ```
 *
 * @package WP_Statistics\Service\Integrations\Multilingual
 * @since 15.0.0
 */
class MultilingualHelper
{
    /**
     * List of supported multilingual integration classes.
     *
     * Order matters: first active integration found will be used.
     * To add support for a new plugin, add its class here.
     *
     * @var array<class-string<AbstractMultilingual>>
     */
    private static $integrations = [
        WPML::class,
        Polylang::class,
    ];

    /**
     * Cached active integration instance.
     *
     * - `false`: Not yet checked
     * - `null`: Checked, no active integration found
     * - `AbstractMultilingual`: Active integration instance
     *
     * @var AbstractMultilingual|null|false
     */
    private static $activeIntegration = false;

    /**
     * Get all registered integrations.
     *
     * @return AbstractMultilingual[]
     */
    public static function getAllIntegrations()
    {
        return array_map(fn($class) => new $class(), self::$integrations);
    }

    /**
     * Get the currently active multilingual integration.
     *
     * @return AbstractMultilingual|null
     */
    public static function getActiveIntegration()
    {
        if (self::$activeIntegration === false) {
            self::$activeIntegration = null;
            foreach (self::$integrations as $class) {
                $integration = new $class();
                if ($integration->isActive()) {
                    self::$activeIntegration = $integration;
                    break;
                }
            }
        }
        return self::$activeIntegration;
    }

    /**
     * Check if any multilingual plugin is active.
     *
     * @return bool
     */
    public static function isMultilingualActive()
    {
        return self::getActiveIntegration() !== null;
    }

    /**
     * Get the current language code.
     *
     * @return string|null
     */
    public static function getCurrentLanguage()
    {
        $integration = self::getActiveIntegration();
        return $integration ? $integration->getCurrentLanguage() : null;
    }

    /**
     * Get the default language code.
     *
     * @return string|null
     */
    public static function getDefaultLanguage()
    {
        $integration = self::getActiveIntegration();
        return $integration ? $integration->getDefaultLanguage() : null;
    }

    /**
     * Get the original post ID (default language version).
     *
     * @param int         $postId   Post ID.
     * @param string|null $postType Post type.
     *
     * @return int Original post ID or same ID if no multilingual plugin active.
     */
    public static function getOriginalPostId($postId, $postType = null)
    {
        $integration = self::getActiveIntegration();
        return $integration ? $integration->getOriginalPostId($postId, $postType) : $postId;
    }

    /**
     * Get the original term ID (default language version).
     *
     * @param int    $termId   Term ID.
     * @param string $taxonomy Taxonomy slug.
     *
     * @return int Original term ID or same ID if no multilingual plugin active.
     */
    public static function getOriginalTermId($termId, $taxonomy)
    {
        $integration = self::getActiveIntegration();
        return $integration ? $integration->getOriginalTermId($termId, $taxonomy) : $termId;
    }

    /**
     * Get the translated post ID for the current language.
     *
     * @param int         $postId   Post ID.
     * @param string|null $postType Post type.
     *
     * @return int Translated post ID or same ID if no multilingual plugin active.
     */
    public static function getTranslatedPostId($postId, $postType = null)
    {
        $integration = self::getActiveIntegration();
        return $integration ? $integration->getTranslatedPostId($postId, $postType) : $postId;
    }

    /**
     * Get the translated term ID for the current language.
     *
     * @param int    $termId   Term ID.
     * @param string $taxonomy Taxonomy slug.
     *
     * @return int Translated term ID or same ID if no multilingual plugin active.
     */
    public static function getTranslatedTermId($termId, $taxonomy)
    {
        $integration = self::getActiveIntegration();
        return $integration ? $integration->getTranslatedTermId($termId, $taxonomy) : $termId;
    }

    /**
     * Get all translation IDs for a post.
     *
     * @param int         $postId   Post ID.
     * @param string|null $postType Post type.
     *
     * @return array Array of post IDs.
     */
    public static function getPostTranslationIds($postId, $postType = null)
    {
        $integration = self::getActiveIntegration();
        return $integration ? $integration->getPostTranslationIds($postId, $postType) : [$postId];
    }

    /**
     * Get all translation IDs for a term.
     *
     * @param int    $termId   Term ID.
     * @param string $taxonomy Taxonomy slug.
     *
     * @return array Array of term IDs.
     */
    public static function getTermTranslationIds($termId, $taxonomy)
    {
        $integration = self::getActiveIntegration();
        return $integration ? $integration->getTermTranslationIds($termId, $taxonomy) : [$termId];
    }

    /**
     * Get the language code for a post.
     *
     * @param int $postId Post ID.
     *
     * @return string|null
     */
    public static function getPostLanguage($postId)
    {
        $integration = self::getActiveIntegration();
        return $integration ? $integration->getPostLanguage($postId) : null;
    }

    /**
     * Get the language code for a term.
     *
     * @param int    $termId   Term ID.
     * @param string $taxonomy Taxonomy slug.
     *
     * @return string|null
     */
    public static function getTermLanguage($termId, $taxonomy)
    {
        $integration = self::getActiveIntegration();
        return $integration ? $integration->getTermLanguage($termId, $taxonomy) : null;
    }

    /**
     * Check if a post is the original (default language) version.
     *
     * @param int         $postId   Post ID.
     * @param string|null $postType Post type.
     *
     * @return bool
     */
    public static function isOriginalPost($postId, $postType = null)
    {
        $integration = self::getActiveIntegration();
        return $integration ? $integration->isOriginalPost($postId, $postType) : true;
    }

    /**
     * Clear cached integration (useful for testing).
     *
     * @return void
     */
    public static function clearCache()
    {
        self::$activeIntegration = false;
    }
}
