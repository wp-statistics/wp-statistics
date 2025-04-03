<?php

namespace WP_Statistics\BackgroundProcess\AjaxBackgroundProcess\Jobs;

use WP_Statistics\BackgroundProcess\AjaxBackgroundProcess\AbstractAjaxBackgroundProcess;
use WP_Statistics\Service\Database\DatabaseFactory;

/**
 * Handles the migration of resource data from the pages table into the resources table.
 *
 * This background process job migrates unique page records based on normalized URI values.
 * For pages with type 'home', the record is identified solely by a literal 'home', while for other
 * types (including 404, search, and archive) the URI is normalized by trimming slashes and, if needed,
 * stripping out query parameters when a utm parameter is detected. The unique count is determined by
 * grouping based on this normalized value (or a composite key for non-home pages).
 */
class ResourceMigrator extends AbstractAjaxBackgroundProcess
{
    /**
     * Total number of batches required for migration.
     *
     * @var int
     */
    protected $batches = 0;

    /**
     * Offset for batch processing.
     *
     * @var int
     */
    protected $offset = 0;

    /**
     * Calculates the total number of unique pages that need to be migrated.
     *
     * This method retrieves page records from the pages table and normalizes the URI by:
     * - Removing leading and trailing slashes.
     * - Stripping query parameters if a "utm_" parameter is present.
     *
     * For pages with type 'home', the grouping is done by the literal value 'home', ensuring that
     * all home pages are treated as one unique record regardless of URI differences.
     * For all other pages, grouping is performed by the normalized URI.
     *
     * The total unique count is determined by counting the number of groups.
     *
     * @return void
     */
    protected function getTotal()
    {
        $pages = DatabaseFactory::table('select')
            ->setName('pages')
            ->setArgs([
                'columns'  => [
                    'page_id',
                    'id',
                    'type',
                    'uri',
                    "CONCAT('/', TRIM(BOTH '/' FROM (CASE WHEN LOCATE('utm_', uri) > 0 THEN LEFT(uri, LOCATE('?', uri) - 1) ELSE uri END))) AS uri"
                ],
                'group_by' => "CASE WHEN type = 'home' THEN 'home' ELSE CONCAT('/', TRIM(BOTH '/' FROM (CASE WHEN LOCATE('utm_', uri) > 0 THEN LEFT(uri, LOCATE('?', uri) - 1) ELSE uri END))) END",
            ])
            ->execute()
            ->getResult();

        $this->total   = count($pages);
        $this->batches = ceil($this->total / $this->batchSize);
    }

    /**
     * Calculates the current offset for batch processing based on already migrated resource records.
     *
     * This method queries the resources table to count how many records have been migrated,
     * and calculates the offset as the number of completed records. This ensures that the migration
     * continues from where it left off.
     *
     * @return void
     */
    protected function calculateOffset()
    {
        $completedResources = DatabaseFactory::table('select')
            ->setName('resources')
            ->setArgs([
                'columns'   => ['resource_id'],
                'raw_where' => [
                    "(resource_id IS NOT NULL AND resource_id != '' AND resource_type IS NOT NULL AND resource_type != '') OR (resource_url IS NOT NULL AND resource_url != '')"
                ]
            ])
            ->execute()
            ->getResult();

        $this->done   = count($completedResources);
        $currentBatch = ceil($this->done / $this->batchSize);
        $this->offset = $currentBatch * $this->batchSize > $this->total ? $this->total - 1 : $currentBatch * $this->batchSize;
    }

    /**
     * Migrates resource data from the pages table into the resources table.
     *
     * This method retrieves a batch of unique page records based on the following logic:
     * - For pages with types 'home', '404', 'search', or 'archive', uniqueness is determined by the normalized URI.
     * - For all other pages, uniqueness is determined by a composite key (id and type).
     *
     * For each unique page record, the method resolves the final resource type, permalink, and author
     * information, then inserts or updates the corresponding record in the resources table.
     *
     * @return void
     */
    protected function migrate()
    {
        $this->getTotal();
        $this->calculateOffset();

        if ($this->isCompleted()) {
            return;
        }

        $pages = DatabaseFactory::table('select')
            ->setName('pages')
            ->setArgs([
                'columns'  => [
                    'page_id',
                    'id',
                    'type',
                    'uri',
                    "CONCAT('/', TRIM(BOTH '/' FROM (CASE WHEN LOCATE('utm_', uri) > 0 THEN LEFT(uri, LOCATE('?', uri) - 1) ELSE uri END))) AS uri"
                ],
                'group_by' => "CASE WHEN type = 'home' THEN 'home' ELSE CONCAT('/', TRIM(BOTH '/' FROM (CASE WHEN LOCATE('utm_', uri) > 0 THEN LEFT(uri, LOCATE('?', uri) - 1) ELSE uri END))) END",
                'limit'    => [
                    $this->batchSize,
                    $this->offset
                ],
            ])
            ->execute()
            ->getResult();

        if (empty($pages)) {
            return;
        }

        foreach ($pages as $page) {
            $resourceType   = $this->resolveResourceType($page['id'], $page['type'], $page['uri']);
            $resourceUrl    = $this->resolvePermalink($page['id'], $page['type'], $page['uri']);
            $resourceAuthor = $this->getResourceAuthor($page['id'], $resourceType);
            $resourceDate   = $this->getResourceDate($page['id'], $resourceType);
            $resourceTerms  = $this->getResourceTerms($page['id'], $resourceType);

            $conditions['resource_url'] = $resourceUrl;

            DatabaseFactory::table('insert')
                ->setName('resources')
                ->setArgs([
                    'conditions' => $conditions,
                    'mapping'    => [
                        'resource_id'        => $page['id'],
                        'resource_type'      => $resourceType,
                        'resource_url'       => $resourceUrl,
                        'cached_author_id'   => $resourceAuthor['id'],
                        'cached_author_name' => $resourceAuthor['name'],
                        'cached_date'        => $resourceDate,
                        'cached_terms'       => $resourceTerms,
                    ],
                ])
                ->execute();
        }
    }

    /**
     * Resolves the final permalink for a resource.
     *
     * For pages with types like 'home', '404', 'search', 'archive', or 'author', this method returns
     * a fallback URL using home_url and the given URI. For post types, it uses get_the_permalink(), and for
     * taxonomies, it retrieves the term link.
     *
     * @param int $id The resource ID.
     * @param string $type The resource type.
     * @param string $uri The URI associated with the resource.
     * @return string The resolved permalink.
     */
    private function resolvePermalink($id, $type, $uri)
    {
        $fallbackUrl = home_url($uri);

        if (in_array($type, ['home', '404', 'search', 'archive', 'author'], true)) {
            return $fallbackUrl;
        }

        if (strpos($type, 'post_type_') === 0) {
            $type = substr($type, strlen('post_type_'));
        }

        if (in_array($type, ['post', 'page', 'product', 'attachment'], true) || post_type_exists($type)) {
            $url = get_the_permalink($id);
            return !empty($url) ? $url : $fallbackUrl;
        }

        if (in_array($type, ['category', 'post_tag', 'tax'], true)) {
            $term = get_term($id);
            if (!is_wp_error($term) && $term && isset($term->taxonomy)) {
                $url = get_term_link($term);
                return !is_wp_error($url) ? $url : $fallbackUrl;
            }
        }

        return $fallbackUrl;
    }

    /**
     * Determines the proper resource type for indexing.
     *
     * For types starting with 'post_type_', the prefix is removed.
     * For taxonomy types, if a valid term exists for the given ID, the term's taxonomy is returned.
     * For 'archive' types, additional logic determines if it is a date archive or post type archive.
     *
     * @param int $id The resource ID.
     * @param string $type The original resource type.
     * @param string $uri The URI associated with the resource.
     * @return string The resolved resource type.
     */
    private function resolveResourceType($id, $type, $uri)
    {
        if (strpos($type, 'post_type_') === 0) {
            return substr($type, strlen('post_type_'));
        }

        if (in_array($type, ['post', 'page', 'product', 'attachment'], true)) {
            return $type;
        }

        if (in_array($type, ['category', 'post_tag', 'tax'], true)) {
            $term = get_term($id);
            if (!is_wp_error($term) && $term && isset($term->taxonomy)) {
                return $term->taxonomy;
            }
            return $type;
        }

        if ($type === 'archive') {
            $path = trim(parse_url($uri, PHP_URL_PATH), '/');

            if (preg_match('#^\d{4}(?:/\d{2})?(?:/\d{2})?$#', $path)) {
                return 'date_archive';
            }

            foreach (get_post_types(['public' => true], 'objects') as $postType) {
                if ($postType->has_archive && strpos($uri, $postType->rewrite['slug']) !== false) {
                    return 'post_type_archive';
                }
            }

            return 'archive';
        }

        return $type;
    }

    /**
     * Retrieves cached author information for a resource (only applicable for valid post types).
     *
     * If the resource type exists as a post type and the post has an associated author, this method
     * returns an array with 'id' and 'name' of the author. Otherwise, it returns empty values.
     *
     * @param int $id The resource ID (post ID).
     * @param string $type The resource type.
     * @return array An associative array with keys 'id' and 'name' for the author.
     */
    private function getResourceAuthor($id, $type)
    {
        $data = [
            'id'   => '',
            'name' => '',
        ];

        if (!post_type_exists($type)) {
            return $data;
        }

        $authorId = get_post_field('post_author', $id);

        if (empty($authorId)) {
            return $data;
        }

        $author     = get_userdata($authorId);
        $authorName = $author ? $author->display_name : '';

        return [
            'id'   => $authorId,
            'name' => $authorName,
        ];
    }

    /**
     * Retrieves the publication date for a resource.
     *
     * For valid post types, this method fetches the 'post_date' field from the post.
     *
     * @param int $id The resource ID (post ID).
     * @param string $type The resource type.
     * @return mixed The publication date, or null if not applicable.
     */
    private function getResourceDate($id, $type)
    {
        $date = null;

        if (!post_type_exists($type)) {
            return $date;
        }

        return get_post_field('post_date', $id);
    }

    /**
     * Retrieves the terms associated with a resource.
     *
     * For valid post types, this method retrieves the taxonomies associated with the post type,
     * then obtains the term IDs for each taxonomy linked to the post. The term IDs are returned as
     * a comma-separated string. If no terms are found or if the resource type is not a post type, null is returned.
     *
     * @param int $id The resource ID (post ID).
     * @param string $type The resource type.
     * @return mixed A comma-separated string of term IDs or null if not applicable.
     */
    private function getResourceTerms($id, $type)
    {
        if (!post_type_exists($type)) {
            return null;
        }

        $postType = get_post_type($id);

        if (!$postType) {
            return null;
        }

        $taxonomies = get_object_taxonomies($postType, 'names');

        if (empty($taxonomies)) {
            return null;
        }

        $formattedTerms = [];
        foreach ($taxonomies as $taxonomy) {
            $termList = get_the_terms($id, $taxonomy);

            if (empty($termList) || is_wp_error($termList)) {
                continue;
            }

            foreach ($termList as $termObj) {
                $formattedTerms[] = $termObj->term_id;
            }
        }

        return !empty($formattedTerms) ? implode(', ', $formattedTerms) : null;
    }
}
