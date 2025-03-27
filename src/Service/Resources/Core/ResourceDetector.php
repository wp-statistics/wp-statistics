<?php

namespace WP_Statistics\Service\Resources\Core;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Url;

/**
 * Class ResourceDetector
 *
 * Determines the current resource context (e.g., singular, home, archive) and gathers
 * relevant details (title, taxonomies/terms, publish date, author info, etc.) when appropriate.
 */
class ResourceDetector
{
    /**
     * The numeric ID for the resource (e.g., post or term ID).
     *
     * @var int|null
     */
    private $resourceId = null;

    /**
     * The context type of the resource (e.g., 'singular', 'home', 'category', 'search').
     *
     * @var string|null
     */
    private $resourceType = null;

    /**
     * The title of the resource.
     *
     * @var string|null
     */
    private $cachedTitle = null;

    /**
     * A JSON string representing the taxonomies and terms linked to the resource.
     *
     * @var string|null
     */
    private $cachedTerms = null;

    /**
     * The numeric identifier for the resource's author.
     *
     * @var int|null
     */
    private $cachedAuthorId = null;

    /**
     * The display name of the resource's author.
     *
     * @var string|null
     */
    private $cachedAuthorName = null;

    /**
     * The resource's publish date.
     *
     * @var string|null
     */
    private $cachedDate = null;

    /**
     * Any additional metadata related to the resource.
     *
     * @var string|null
     */
    private $resourceMeta = null;

    /**
     * Constructor.
     *
     * If explicit resource ID and/or resource type values are provided, they override
     * the auto-detected values based on the current page context.
     * Then, if the resource type is not one of the excluded types, additional details
     * (such as taxonomies, publish date, title, and author details) are gathered.
     *
     * @param int $resourceId Optional. Specific resource ID to use.
     * @param string $resourceType Optional. Specific resource type (e.g., 'post', 'page') to use.
     */
    public function __construct($resourceId = 0, $resourceType = '')
    {
        $resourceData = $this->getResourceData();

        $this->resourceId   = !empty($resourceData['id']) ? (int)$resourceData['id'] : 0;
        $this->resourceType = !empty($resourceData['type']) ? (string)$resourceData['type'] : null;

        if (!empty($resourceId)) {
            $this->resourceId = $resourceId;
        }

        if (!empty($resourceType)) {
            $this->resourceType = $resourceType;
        }

        $excludedTypes = [
            'home',
            'search',
            '404',
            'archive',
            'post_type_archive',
            'author_archive',
            'date_archive',
        ];

        $publicTaxonomies = array_keys(get_taxonomies(['public' => true]));
        $excludedTypes    = array_merge($excludedTypes, $publicTaxonomies);

        if (!in_array($this->resourceType, $excludedTypes, true)) {
            $this->setPostTerms();
            $this->setPublishDate();
            $this->setResourceMetaData();
            $this->setTitle();
            $this->setAuthorDetails();
        }
    }

    /**
     * Gathers high-level data about the resource (type, ID, etc.), then applies a filter.
     *
     * @return array
     */
    private function getResourceData()
    {
        $data = $this->buildResourceData();
        return apply_filters('wp_statistics_resource_data', $data);
    }

    /**
     * Detects the resource context (e.g., singular, search, archive) and assigns basic keys:
     *   - 'type': A string like 'home', 'singular', 'search'
     *   - 'id':   An integer ID if applicable (e.g., post or term ID)
     *
     * @return array
     */
    private function buildResourceData()
    {
        $data = [
            'type'         => 'unknown',
            'id'           => 0,
            'search_query' => ''
        ];

        if (class_exists('WooCommerce') && is_product()) {
            $data['type'] = 'product';
            $data['id']   = get_queried_object_id();
            return $data;
        }

        if (class_exists('WooCommerce') && is_shop()) {
            $data['type'] = 'page';
            $data['id']   = wc_get_page_id('shop');
            return $data;
        }

        if (is_front_page() || is_home()) {
            $data['type'] = 'home';
            $data['id']   = get_queried_object_id();
            return $data;
        }

        $search_query = sanitize_text_field(get_search_query(false));
        if (trim($search_query) !== '') {
            $data['type']         = 'search';
            $data['search_query'] = $search_query;
            return $data;
        }

        if (is_singular()) {
            $data['type'] = get_post_type(get_queried_object_id());
            $data['id']   = get_queried_object_id();
            return $data;
        }

        if (is_author()) {
            $data['type'] = 'author_archive';
            $data['id']   = get_queried_object_id();
            return $data;
        }

        if (is_date()) {
            $data['type'] = 'date_archive';
            $data['id']   = $this->getDateArchiveDate();
            return $data;
        }

        if (is_post_type_archive()) {
            $data['type'] = 'post_type_archive';
            $queried      = get_queried_object();
            $data['id']   = (is_object($queried) && isset($queried->name)) ? $queried->name : '';
            return $data;
        }

        if (is_category() || is_tag() || is_tax()) {
            $term         = get_queried_object();
            $data['type'] = isset($term->taxonomy) ? $term->taxonomy : 'term_archive';
            $data['id']   = isset($term->term_id) ? (int)$term->term_id : 0;
            return $data;
        }

        if (is_search()) {
            $data['type']         = 'search';
            $data['search_query'] = get_search_query();
            return $data;
        }

        if (is_404()) {
            $path = Url::getRelativePathToSiteUrl();
            if (is_null($path)) {
                return $data;
            }
            $data['type'] = '404';
            $data['id']   = $path;
            return $data;
        }

        if (is_attachment()) {
            $data['type'] = 'attachment';
            $data['id']   = get_queried_object_id();
            return $data;
        }

        if (is_archive()) {
            $data['type'] = 'archive';
            $data['id']   = get_queried_object_id();
            return $data;
        }

        if (is_page()) {
            $data['type'] = 'page';
            $data['id']   = get_queried_object_id();
            return $data;
        }

        if (is_feed()) {
            $data['type'] = 'feed';
            return $data;
        }

        if (Helper::is_login_page()) {
            $data['type'] = 'loginpage';
            return $data;
        }

        return $data;
    }

    /**
     * Builds a "year-month-day" string for date archives.
     *
     * @return string
     */
    private function getDateArchiveDate()
    {
        $str = get_query_var('year');
        if (is_month() || is_day()) {
            $month = get_query_var('monthnum');
            $str   = $str . '-' . str_pad($month, 2, '0', \STR_PAD_LEFT);
        }
        if (is_day()) {
            $day = get_query_var('day');
            $str = $str . '-' . str_pad($day, 2, '0', \STR_PAD_LEFT);
        }
        return $str;
    }

    /**
     * Retrieves author info from post_author and sets the ID and display name.
     *
     * @return void
     */
    private function setAuthorDetails()
    {
        if (!$this->resourceId) {
            return;
        }

        $authorId = get_post_field('post_author', $this->resourceId);

        if (!$authorId) {
            return;
        }

        $authorName = get_the_author_meta('display_name', $authorId);

        if (empty($authorName)) {
            $authorName = null;
        }

        $this->cachedAuthorId   = (int)$authorId;
        $this->cachedAuthorName = $authorName;
    }

    /**
     * Retrieves the post_date for this resource.
     *
     * @return void
     */
    private function setPublishDate()
    {
        if (!$this->resourceId) {
            return;
        }
        $publishDate = get_post_field('post_date', $this->resourceId);

        $this->cachedDate = !empty($publishDate) ? $publishDate : null;
    }

    /**
     * Sets resource meta (currently null).
     *
     * @return void
     */
    private function setResourceMetaData()
    {
        if (!$this->resourceId) {
            return;
        }

        $this->resourceMeta = null;
    }

    /**
     * Sets resource title from get_the_title().
     *
     * @return void
     */
    private function setTitle()
    {
        if (!$this->resourceId) {
            return;
        }

        $title = get_the_title($this->resourceId);

        $this->cachedTitle = $title ?? null;
    }

    /**
     * Gathers taxonomies/terms for this post_type and encodes them as JSON.
     *
     * @return void
     */
    private function setPostTerms()
    {
        if (!$this->resourceId) {
            return;
        }
        $postType = get_post_type($this->resourceId);

        if (!$postType) {
            return;
        }

        $taxonomies = get_object_taxonomies($postType, 'names');

        if (empty($taxonomies)) {
            return;
        }

        $formattedTerms = [];
        foreach ($taxonomies as $taxonomy) {
            $termList = get_the_terms($this->resourceId, $taxonomy);

            if (empty($termList) || is_wp_error($termList)) {
                continue;
            }

            foreach ($termList as $termObj) {
                $formattedTerms[] = $termObj->term_id;
            }
        }

        $this->cachedTerms = !empty($formattedTerms) ? implode(', ', $formattedTerms) : null;
    }

    /**
     * Gets the associated resource ID.
     *
     * @return int|null
     */
    public function getResourceId()
    {
        return $this->resourceId;
    }

    /**
     * Returns the resource type (e.g. 'singular', 'home', 'category').
     *
     * @return string|null
     */
    public function getResourceType()
    {
        return $this->resourceType;
    }

    /**
     * Returns the resource's title.
     *
     * @return string|null
     */
    public function getCachedTitle()
    {
        return $this->cachedTitle;
    }

    /**
     * Returns the resource's terms.
     *
     * @return string|null
     */
    public function getCachedTerms()
    {
        return $this->cachedTerms;
    }

    /**
     * Returns the resource's author id.
     *
     * @return int|null
     */
    public function getCachedAuthorId()
    {
        return $this->cachedAuthorId;
    }

    /**
     * Returns the resource's author name.
     *
     * @return string|null
     */
    public function getCachedAuthorName()
    {
        return $this->cachedAuthorName;
    }

    /**
     * Returns the publish date (post_date) for this resource.
     *
     * @return string|null
     */
    public function getCachedDate()
    {
        return $this->cachedDate;
    }

    /**
     * Returns any custom resource metadata.
     *
     * @return string|null
     */
    public function getResourceMeta()
    {
        return $this->resourceMeta;
    }
}
