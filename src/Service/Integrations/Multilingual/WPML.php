<?php

namespace WP_Statistics\Service\Integrations\Multilingual;

/**
 * WPML (WordPress Multilingual Plugin) integration.
 *
 * Integrates with WPML to provide multilingual support for WP Statistics.
 * Uses WPML's filter-based API (wpml_object_id, wpml_current_language, etc.)
 * to resolve original content IDs and detect current language.
 *
 * @link https://wpml.org/documentation/support/wpml-coding-api/
 * @package WP_Statistics\Service\Integrations\Multilingual
 * @since 15.0.0
 */
class WPML extends AbstractMultilingual
{
    /**
     * Unique identifier for this integration.
     *
     * @var string
     */
    protected $key = 'wpml';

    /**
     * Plugin file path for activation check.
     *
     * @var string
     */
    protected $path = 'sitepress-multilingual-cms/sitepress.php';

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'WPML';
    }

    /**
     * Check if WPML is active by looking for its version constant.
     *
     * {@inheritDoc}
     */
    public function isActive()
    {
        return defined('ICL_SITEPRESS_VERSION');
    }

    /**
     * Get current language using wpml_current_language filter.
     *
     * {@inheritDoc}
     */
    public function getCurrentLanguage()
    {
        return $this->isActive() ? apply_filters('wpml_current_language', null) : null;
    }

    /**
     * Get default language using wpml_default_language filter.
     *
     * {@inheritDoc}
     */
    public function getDefaultLanguage()
    {
        return $this->isActive() ? apply_filters('wpml_default_language', null) : null;
    }

    /**
     * Get all active languages from WPML.
     *
     * {@inheritDoc}
     */
    public function getActiveLanguages()
    {
        if (!$this->isActive()) {
            return [];
        }

        $languages = apply_filters('wpml_active_languages', null, ['skip_missing' => 0]);

        return is_array($languages) ? array_keys($languages) : [];
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
     * Get the translated post ID for the current language using wpml_object_id filter.
     *
     * {@inheritDoc}
     */
    public function getTranslatedPostId($postId, $postType = null)
    {
        if (!$this->isActive() || empty($postId)) {
            return (int) $postId;
        }

        $postType     = $postType ?: get_post_type($postId);
        $translatedId = apply_filters('wpml_object_id', $postId, $postType, false);

        return !empty($translatedId) ? (int) $translatedId : (int) $postId;
    }

    /**
     * Get the translated term ID for the current language using wpml_object_id filter.
     *
     * {@inheritDoc}
     */
    public function getTranslatedTermId($termId, $taxonomy)
    {
        if (!$this->isActive() || empty($termId)) {
            return (int) $termId;
        }

        $translatedId = apply_filters('wpml_object_id', $termId, $taxonomy, false);

        return !empty($translatedId) ? (int) $translatedId : (int) $termId;
    }

    /**
     * Get all translation IDs for a post using WPML's trid system.
     *
     * WPML groups translations by "trid" (translation ID). This method
     * retrieves all posts sharing the same trid.
     *
     * {@inheritDoc}
     */
    public function getPostTranslationIds($postId, $postType = null)
    {
        if (!$this->isActive() || empty($postId)) {
            return [$postId];
        }

        $postType = $postType ?: get_post_type($postId);

        // Get the translation group ID
        $trid = apply_filters('wpml_element_trid', null, $postId, 'post_' . $postType);

        if (empty($trid)) {
            return [$postId];
        }

        // Get all translations in this group
        $translations = apply_filters('wpml_get_element_translations', null, $trid, 'post_' . $postType);

        if (empty($translations)) {
            return [$postId];
        }

        $ids = array_filter(array_map(fn($t) => (int) ($t->element_id ?? 0), (array) $translations));

        return $ids ?: [$postId];
    }

    /**
     * Get all translation IDs for a term using WPML's trid system.
     *
     * {@inheritDoc}
     */
    public function getTermTranslationIds($termId, $taxonomy)
    {
        if (!$this->isActive() || empty($termId)) {
            return [$termId];
        }

        // Get the translation group ID (prefix with 'tax_' for taxonomies)
        $trid = apply_filters('wpml_element_trid', null, $termId, 'tax_' . $taxonomy);

        if (empty($trid)) {
            return [$termId];
        }

        $translations = apply_filters('wpml_get_element_translations', null, $trid, 'tax_' . $taxonomy);

        if (empty($translations)) {
            return [$termId];
        }

        $ids = array_filter(array_map(fn($t) => (int) ($t->element_id ?? 0), (array) $translations));

        return $ids ?: [$termId];
    }

    /**
     * Get language code for a post using wpml_post_language_details filter.
     *
     * {@inheritDoc}
     */
    public function getPostLanguage($postId)
    {
        if (!$this->isActive() || empty($postId)) {
            return null;
        }

        $details = apply_filters('wpml_post_language_details', null, $postId);

        return $details['language_code'] ?? null;
    }

    /**
     * Get language code for a term using wpml_element_language_code filter.
     *
     * {@inheritDoc}
     */
    public function getTermLanguage($termId, $taxonomy)
    {
        if (!$this->isActive() || empty($termId)) {
            return null;
        }

        return apply_filters('wpml_element_language_code', null, [
            'element_id'   => $termId,
            'element_type' => 'tax_' . $taxonomy,
        ]);
    }
}
