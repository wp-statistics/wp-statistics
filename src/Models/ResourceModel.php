<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Utils\Query;

/**
 * Model class for performing database operations on tracked resources
 * (posts, pages, custom-post-types and other URLs).
 *
 * Provides convenience methods that query the `resources`
 * table and its related data (views, comments, meta, taxonomy, …).
 *
 * @deprecated 15.0.0 Use AnalyticsQueryHandler with page groupBy instead.
 * @see \WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler
 * @since 15.0.0
 */
class ResourceModel extends BaseModel
{
    /**
     * Count resources that match the supplied filters.
     *
     * @param array $args {
     * @type string|array $date Y-m-d string or ['from'=>Y-m-d,'to'=>Y-m-d].
     * @type string[] $post_type Resource types (post, page …).
     * @type int|string $author_id Author ID.
     * @type string[] $taxonomy Taxonomy slugs.
     * @type int|string $term Term ID.
     * @type bool $filter_by_view_date Count only if viewed on $date.
     * @type string $url Sub-string of resource URL.
     * }
     * @return int
     */
    public function count($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'                => '',
            'post_type'           => [],
            'author_id'           => '',
            'taxonomy'            => '',
            'term'                => '',
            'filter_by_view_date' => false,
            'url'                 => '',
        ]);

        // Core SELECT
        $query = Query::select('COUNT(*)')
            ->from('resources')
            ->where('resource_type', 'IN', $args['post_type'])
            ->where('is_deleted', '=', 0)
            ->where('cached_author_id', '=', $args['author_id']);

        // URL filter (slug/URI part)
        if (!empty($args['url'])) {
            $query->where('resource_url', 'LIKE', "%{$args['url']}%");
        }

        // Filter by the date the post was *viewed*
        if ($args['filter_by_view_date']) {
            $views = Query::select(['resource_id'])
                ->from('views')
                ->whereDate('viewed_at', $args['date'])
                ->getQuery();

            $query->joinQuery($views, ['resources.ID', 'v.resource_id'], 'v');
        } elseif (!empty($args['date'])) {
            // Otherwise use the post’s own cached date
            $query->whereDate('cached_date', $args['date']);
        }

        // Taxonomy / term filter requires the classic WP tables
        if (!empty($args['taxonomy']) || !empty($args['term'])) {
            $taxQuery = Query::select(['DISTINCT object_id'])
                ->from('term_relationships')
                ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
                ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
                ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
                ->where('terms.term_id', '=', $args['term'])
                ->getQuery();

            $query->joinQuery($taxQuery, ['resources.resource_id', 'tax.object_id'], 'tax');
        }

        $count = $query->getVar();

        return $count ? (int)$count : 0;
    }

    /**
     * Return daily totals of published resources.
     *
     * @param array $args {
     * @type string|array $date Day or range.
     * @type string[] $post_type
     * @type int|string $author_id
     * @type string[] $taxonomy
     * @type int|string $term
     * }
     * @return array[] Each row has keys `date` and `posts`.
     * @since 15.0.0
     */
    public function countDaily($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => [],
            'author_id' => '',
            'taxonomy'  => '',
            'term'      => '',
        ]);

        $query = Query::select([
            'DATE(cached_date) AS date',
            'COUNT(*)          AS posts',
        ])
            ->from('resources')
            ->where('resource_type', 'IN', $args['post_type'])
            ->where('is_deleted', '=', 0)
            ->where('cached_author_id', '=', $args['author_id'])
            ->whereDate('cached_date', $args['date'])
            ->groupBy('DATE(cached_date)');

        if (!empty($args['taxonomy']) || !empty($args['term'])) {
            $taxQuery = Query::select(['DISTINCT object_id'])
                ->from('term_relationships')
                ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
                ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
                ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
                ->where('terms.term_id', '=', $args['term'])
                ->getQuery();

            $query->joinQuery($taxQuery, ['resources.resource_id', 'tax.object_id'], 'tax');
        }

        return $query->getAll() ?: [];
    }
}