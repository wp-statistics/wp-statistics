<?php

namespace WP_Statistics\Service\Integrations\Multilingual;

/**
 * Polylang integration.
 *
 * Integrates with Polylang to provide multilingual support for WP Statistics.
 * Uses Polylang's function-based API (pll_get_post, pll_current_language, etc.)
 * to resolve original content IDs and detect current language.
 *
 * @link https://polylang.pro/doc/function-reference/
 * @package WP_Statistics\Service\Integrations\Multilingual
 * @since 15.0.0
 */
class Polylang extends AbstractMultilingual
{
    /**
     * Unique identifier for this integration.
     *
     * @var string
     */
    protected $key = 'polylang';

    /**
     * Plugin file path for activation check.
     *
     * @var string
     */
    protected $path = 'polylang/polylang.php';

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'Polylang';
    }

    /**
     * Check if Polylang is active by looking for its core function.
     *
     * {@inheritDoc}
     */
    public function isActive()
    {
        return function_exists('pll_current_language');
    }

    /**
     * Get current language using pll_current_language().
     *
     * {@inheritDoc}
     */
    public function getCurrentLanguage()
    {
        return $this->isActive() ? pll_current_language('slug') : null;
    }

    /**
     * Get default language using pll_default_language().
     *
     * {@inheritDoc}
     */
    public function getDefaultLanguage()
    {
        return $this->isActive() ? pll_default_language('slug') : null;
    }

    /**
     * Get all active languages from Polylang.
     *
     * {@inheritDoc}
     */
    public function getActiveLanguages()
    {
        if (!$this->isActive() || !function_exists('pll_languages_list')) {
            return [];
        }

        return pll_languages_list(['fields' => 'slug']);
    }

    /**
     * Get original post ID.
     *
     * Each translation is tracked as a separate resource, so we return
     * the post ID as-is without converting to the default language version.
     *
     * {@inheritDoc}
     */
    public function getOriginalPostId($postId, $postType = null)
    {
        return (int) $postId;
    }

    /**
     * Get original term ID.
     *
     * Each translation is tracked as a separate resource, so we return
     * the term ID as-is without converting to the default language version.
     *
     * {@inheritDoc}
     */
    public function getOriginalTermId($termId, $taxonomy)
    {
        return (int) $termId;
    }

    /**
     * Get the translated post ID for the current language using pll_get_post().
     *
     * {@inheritDoc}
     */
    public function getTranslatedPostId($postId, $postType = null)
    {
        if (!$this->isActive() || empty($postId) || !function_exists('pll_get_post')) {
            return (int) $postId;
        }

        $translatedId = pll_get_post($postId);

        return !empty($translatedId) ? (int) $translatedId : (int) $postId;
    }

    /**
     * Get the translated term ID for the current language using pll_get_term().
     *
     * {@inheritDoc}
     */
    public function getTranslatedTermId($termId, $taxonomy)
    {
        if (!$this->isActive() || empty($termId) || !function_exists('pll_get_term')) {
            return (int) $termId;
        }

        $translatedId = pll_get_term($termId);

        return !empty($translatedId) ? (int) $translatedId : (int) $termId;
    }

    /**
     * Get all translation IDs for a post using pll_get_post_translations().
     *
     * Returns an array of post IDs keyed by language slug.
     *
     * {@inheritDoc}
     */
    public function getPostTranslationIds($postId, $postType = null)
    {
        if (!$this->isActive() || empty($postId) || !function_exists('pll_get_post_translations')) {
            return [$postId];
        }

        // Returns array like ['en' => 10, 'fr' => 15, 'de' => 20]
        $translations = pll_get_post_translations($postId);

        return !empty($translations) ? array_values(array_map('intval', $translations)) : [$postId];
    }

    /**
     * Get all translation IDs for a term using pll_get_term_translations().
     *
     * {@inheritDoc}
     */
    public function getTermTranslationIds($termId, $taxonomy)
    {
        if (!$this->isActive() || empty($termId) || !function_exists('pll_get_term_translations')) {
            return [$termId];
        }

        $translations = pll_get_term_translations($termId);

        return !empty($translations) ? array_values(array_map('intval', $translations)) : [$termId];
    }

    /**
     * Get language code for a post using pll_get_post_language().
     *
     * {@inheritDoc}
     */
    public function getPostLanguage($postId)
    {
        if (!$this->isActive() || empty($postId) || !function_exists('pll_get_post_language')) {
            return null;
        }

        return pll_get_post_language($postId, 'slug');
    }

    /**
     * Get language code for a term using pll_get_term_language().
     *
     * {@inheritDoc}
     */
    public function getTermLanguage($termId, $taxonomy)
    {
        if (!$this->isActive() || empty($termId) || !function_exists('pll_get_term_language')) {
            return null;
        }

        return pll_get_term_language($termId, 'slug');
    }
}
