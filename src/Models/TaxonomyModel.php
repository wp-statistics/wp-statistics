<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\Helper;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Utils\Query;
use WP_Statistics\Abstracts\BaseModel;

class TaxonomyModel extends BaseModel
{
    public function countTerms($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'     => '',
            'taxonomy' => '',
        ]);

        $result = Query::select([
            'COUNT(*)',
        ])
            ->from('term_taxonomy')
            ->where('taxonomy', 'IN', $args['taxonomy'])
            ->getVar();

        return $result;
    }

    public function getTaxonomiesData($args = [])
    {
        $args = $this->parseArgs($args, [
            'post_type'         => Helper::get_list_post_type(),
            'order_by'          => ['term_taxonomy.taxonomy', 'post_count'],
            'taxonomy'          => array_keys(Helper::get_list_taxonomy(true)),
            'resource_type'     => array_keys(Helper::get_list_taxonomy(true)),
            'page'              => 1,
            'per_page'          => 5,
            'date'              => '',
            'author_id'         => '',
            'order'             => '',
            'count_total_posts' => false
        ]);

        $categoryViewsQuery = Query::select(['id', 'date', 'SUM(count) AS views'])
            ->from('pages')
            ->where('pages.type', 'IN', $args['resource_type'])
            ->whereDate('date', $args['date'])
            ->groupBy('id')
            ->getQuery();

        $query = Query::select([
            'taxonomy',
            'terms.term_id',
            'terms.name',
            'COUNT(DISTINCT posts.ID) as post_count',
            'COALESCE(category.views, 0) as term_views'
        ])
            ->from('term_taxonomy')
            ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
            ->join('term_relationships', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'], [], 'LEFT')
            ->join('posts', ['posts.ID', 'term_relationships.object_id'], [['posts.post_type', 'IN', $args['post_type']], ['posts.post_status', '=', 'publish']], 'LEFT')
            ->joinQuery($categoryViewsQuery, ['category.id', 'term_taxonomy.term_id'], 'category', 'LEFT')
            ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
            ->where('posts.post_author', '=', $args['author_id'])
            ->groupBy(['taxonomy', 'terms.term_id', 'terms.name'])
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page']);

        // If total posts is not requested, filter by date
        if ($args['count_total_posts'] == false) {
            $query->whereDate('posts.post_date', $args['date']);
        }

        $result = $query->getAll();

        if (!empty($result)) {
            $taxonomies = [];

            foreach ($result as $item) {
                $taxonomies[$item->taxonomy][] = [
                    'term_id'     => $item->term_id,
                    'term_name'   => $item->name,
                    'posts_count' => $item->post_count,
                    'views'       => $item->term_views
                ];
            }

            $result = $taxonomies;
        }

        return $result ? $result : [];
    }

    public function getTermsData($args = [])
    {
        $args = $this->parseArgs($args, [
            'order_by'   => 'views',
            'order'      => '',
            'page'       => 1,
            'per_page'   => 5,
            'date'       => '',
            'taxonomy'   => '',
            'author_id'  => '',
            'post_type'  => Helper::getPostTypes(),
            'date_field' => 'pages.date'
        ]);

        // Publishing ranking: count published posts by post date, without the views table
        if ($args['date_field'] === 'posts.post_date') {
            $result = Query::select([
                'terms.term_id',
                'terms.name as term_name',
                '0 AS views',
                'COUNT(DISTINCT posts.ID) AS posts'
            ])
                ->from('posts')
                ->join('term_relationships', ['posts.ID', 'term_relationships.object_id'])
                ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
                ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
                ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
                ->where('posts.post_type', 'IN', $args['post_type'])
                ->where('posts.post_status', '=', 'publish')
                ->where('posts.post_author', '=', $args['author_id'])
                ->whereDate('posts.post_date', $args['date'])
                ->groupBy(['term_id'])
                ->orderBy($args['order_by'], $args['order'])
                ->perPage($args['page'], $args['per_page'])
                ->getAll();

            return $result;
        }

        $result = Query::select([
            'terms.term_id',
            'terms.name as term_name',
            'SUM(pages.count) AS views',
            'COUNT(DISTINCT posts.ID) AS posts'
        ])
            ->from('posts')
            ->join('term_relationships', ['posts.ID', 'term_relationships.object_id'])
            ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
            ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
            ->join('pages', ['pages.id', 'posts.ID'], [['pages.type', 'IN', $this->getResourceTypes($args['post_type'], false)]])
            ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
            ->where('posts.post_type', 'IN', $args['post_type'])
            ->where('posts.post_author', '=', $args['author_id'])
            ->whereDate($args['date_field'], $args['date'])
            ->groupBy(['term_id'])
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $result;
    }

    /**
     * Builds the Category Report query. Each term gets:
     * - views: views in the date range of posts in the term (any publish date),
     * - posts/words: published posts in the term with a publish date in the range.
     * Terms with neither views nor published posts are left out.
     *
     * @param array $args Parsed arguments.
     * @param array $fields Fields to select.
     * @return Query
     */
    private function buildTermsReportQuery($args, $fields)
    {
        $viewsQuery = Query::select([
            'term_taxonomy.term_id',
            'SUM(pages.count) AS views'
        ])
            ->from('posts')
            ->join('term_relationships', ['posts.ID', 'term_relationships.object_id'])
            ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
            ->join('pages', ['pages.id', 'posts.ID'], [['pages.type', 'IN', $this->getResourceTypes($args['post_type'], false)]])
            ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
            ->where('posts.post_type', 'IN', $args['post_type'])
            ->where('posts.post_author', '=', $args['author_id'])
            ->whereDate('pages.date', $args['date'])
            ->groupBy(['term_taxonomy.term_id'])
            ->getQuery();

        $publishedQuery = Query::select([
            'term_taxonomy.term_id',
            'COUNT(DISTINCT posts.ID) AS posts',
            'SUM(postmeta.meta_value) AS words'
        ])
            ->from('posts')
            ->join('term_relationships', ['posts.ID', 'term_relationships.object_id'])
            ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
            ->join('postmeta', ['posts.ID', 'postmeta.post_id'], [['postmeta.meta_key', '=', 'wp_statistics_words_count']], 'LEFT')
            ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
            ->where('posts.post_type', 'IN', $args['post_type'])
            ->where('posts.post_status', '=', 'publish')
            ->where('posts.post_author', '=', $args['author_id'])
            ->whereDate('posts.post_date', $args['date'])
            ->groupBy(['term_taxonomy.term_id'])
            ->getQuery();

        $query = Query::select($fields)
            ->from('term_taxonomy')
            ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
            ->joinQuery($viewsQuery, ['term_views.term_id', 'terms.term_id'], 'term_views', 'LEFT')
            ->joinQuery($publishedQuery, ['published.term_id', 'terms.term_id'], 'published', 'LEFT')
            ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy']);

        $hasData = '(term_views.views IS NOT NULL OR published.posts IS NOT NULL)';
        $query->whereRaw(empty(array_filter((array)$args['taxonomy'])) ? $hasData : "AND {$hasData}");

        // The date filters sit in the subqueries, so cache past ranges here the way whereDate() does
        if (!empty($args['date'])) {
            $range = is_array($args['date']) ? $args['date'] : DateRange::get($args['date']);

            if (!empty($range['from']) && !empty($range['to']) && $range['to'] < DateTime::get()) {
                $query->allowCaching();
            }
        }

        return $query;
    }

    public function getTermsReportData($args = [])
    {
        $args = $this->parseArgs($args, [
            'order_by'   => 'views',
            'order'      => '',
            'page'       => 1,
            'per_page'   => 5,
            'date'       => '',
            'taxonomy'   => '',
            'author_id'  => '',
            'post_type'  => Helper::getDefaultPostTypes()
        ]);

        $result = $this->buildTermsReportQuery($args, [
            'terms.term_id',
            'terms.name as term_name',
            'COALESCE(term_views.views, 0) AS views',
            'COALESCE(published.posts, 0) AS posts',
            'COALESCE(published.words, 0) AS words',
            'COALESCE(term_views.views / published.posts, 0) AS avg_views',
            'COALESCE(published.words / published.posts, 0) AS avg_words'
        ])
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $result;
    }

    public function getTermsReportDataCount($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'       => '',
            'taxonomy'   => '',
            'author_id'  => '',
            'post_type'  => Helper::getDefaultPostTypes()
        ]);

        return $this->buildTermsReportQuery($args, ['COUNT(DISTINCT terms.term_id)'])->getVar();
    }

    /**
     * Top terms for the leaderboard widgets, ranked on the server by one metric. Same numbers as the
     * categories report:
     *  - views:          views in the range of the published posts in the term (any publish date);
     *  - published:      posts in the term published in the range ("Posts");
     *  - views_per_post: views / posts published in the range (NULL when nothing was published).
     *  - comments:       approved comments written in the range on the term's published posts.
     * A term is listed when it has views or a published post in the range, in both directions, so
     * ascending ("lowest first") ranks the active terms and never fills up with unused ones. Ties fall
     * back to views, in the same direction. A missing Per post (nothing published) counts as the lowest
     * value, the "0" the table shows.
     * Slug, parent and term_taxonomy_id are returned so same-name terms can be told apart.
     *
     * @param array $args date, taxonomy, post_type, author_id, order_by (views|published|views_per_post|comments),
     *                    order (DESC|ASC), page, per_page.
     * @return object[] term_id, term_taxonomy_id, taxonomy, term_name, slug, parent, views, posts_with_views, published,
     *                  views_per_post (+ comments when ranked by it).
     */
    public function getTermsLeaderboard($args = [])
    {
        $args = $this->parseArgs($args, [
            'order_by'  => 'views',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 5,
            'date'      => '',
            'taxonomy'  => '',
            'author_id' => '',
            'post_type' => Helper::getPostTypes()
        ]);

        $orderBy = in_array($args['order_by'], ['views', 'published', 'views_per_post', 'comments'], true) ? $args['order_by'] : 'views';
        $order   = strtoupper((string) $args['order']) === 'ASC' ? 'ASC' : 'DESC';

        $viewsQuery = Query::select([
            'term_relationships.term_taxonomy_id AS tt_id',
            'SUM(pages.count) AS views',
            'COUNT(DISTINCT posts.ID) AS posts_with_views'
        ])
            ->from('posts')
            ->join('term_relationships', ['posts.ID', 'term_relationships.object_id'])
            ->join('pages', ['pages.id', 'posts.ID'], [['pages.type', 'IN', $this->getResourceTypes($args['post_type'], false)]])
            ->where('posts.post_type', 'IN', $args['post_type'])
            ->where('posts.post_status', '=', 'publish')
            ->where('posts.post_author', '=', $args['author_id'])
            ->whereDate('pages.date', $args['date'])
            ->groupBy('term_relationships.term_taxonomy_id')
            ->getQuery();

        $publishedQuery = Query::select([
            'term_relationships.term_taxonomy_id AS tt_id',
            'COUNT(DISTINCT posts.ID) AS published'
        ])
            ->from('posts')
            ->join('term_relationships', ['posts.ID', 'term_relationships.object_id'])
            ->where('posts.post_type', 'IN', $args['post_type'])
            ->where('posts.post_status', '=', 'publish')
            ->where('posts.post_author', '=', $args['author_id'])
            ->whereDate('posts.post_date', $args['date'])
            ->groupBy('term_relationships.term_taxonomy_id')
            ->getQuery();

        $fields = [
            'terms.term_id',
            'term_taxonomy.term_taxonomy_id',
            'term_taxonomy.taxonomy',
            'term_taxonomy.parent',
            'terms.name AS term_name',
            'terms.slug',
            'COALESCE(term_views.views, 0) AS views',
            'COALESCE(term_views.posts_with_views, 0) AS posts_with_views',
            'COALESCE(term_published.published, 0) AS published',
            'COALESCE(term_views.views, 0) / NULLIF(term_published.published, 0) AS views_per_post'
        ];

        // Comments are only joined when they are the ranking; other rankings get them from countCommentsByTerms().
        $commentsQuery = null;
        if ($orderBy === 'comments') {
            $fields[]      = 'COALESCE(term_comments.comments, 0) AS comments';
            $commentsQuery = Query::select([
                'term_relationships.term_taxonomy_id AS tt_id',
                'COUNT(DISTINCT comments.comment_ID) AS comments'
            ])
                ->from('comments')
                ->join('posts', ['comments.comment_post_ID', 'posts.ID'])
                ->join('term_relationships', ['posts.ID', 'term_relationships.object_id'])
                ->where('posts.post_type', 'IN', $args['post_type'])
                ->where('posts.post_status', '=', 'publish')
                ->where('posts.post_author', '=', $args['author_id'])
                ->where('comments.comment_approved', '=', '1')
                ->where('comments.comment_type', '=', 'comment')
                ->whereDate('comments.comment_date', $args['date'])
                ->groupBy('term_relationships.term_taxonomy_id')
                ->getQuery();
        }

        $query = Query::select($fields)
            ->from('term_taxonomy')
            ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
            ->joinQuery($viewsQuery, ['term_taxonomy.term_taxonomy_id', 'term_views.tt_id'], 'term_views', 'LEFT')
            ->joinQuery($publishedQuery, ['term_taxonomy.term_taxonomy_id', 'term_published.tt_id'], 'term_published', 'LEFT');

        if ($commentsQuery) {
            $query->joinQuery($commentsQuery, ['term_taxonomy.term_taxonomy_id', 'term_comments.tt_id'], 'term_comments', 'LEFT');
        }

        $query->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy']);

        $hasData = '(term_views.tt_id IS NOT NULL OR term_published.tt_id IS NOT NULL)';
        $query->whereRaw(empty(array_filter((array) $args['taxonomy'])) ? $hasData : "AND {$hasData}");

        $query->orderBy($orderBy === 'views' ? ['views', 'published'] : [$orderBy, 'views'], $order)
            ->perPage($args['page'], $args['per_page']);

        $this->allowCachingForPastRange($query, $args['date']);

        $result = $query->getAll();

        return $result ? $result : [];
    }

    /**
     * Published posts per term with a publish date in the range.
     *
     * @param int[] $termTaxonomyIds
     * @param array $args date, post_type, author_id.
     * @return int[] term_taxonomy_id => count.
     */
    public function countPublishedByTerms($termTaxonomyIds, $args = [])
    {
        $termTaxonomyIds = array_values(array_filter(array_map('intval', (array) $termTaxonomyIds)));

        // An empty IN list would drop the filter and count every term
        if (empty($termTaxonomyIds)) {
            return [];
        }

        $args = $this->parseArgs($args, [
            'date'      => '',
            'author_id' => '',
            'post_type' => Helper::getPostTypes()
        ]);

        $rows = Query::select(['term_relationships.term_taxonomy_id AS id', 'COUNT(DISTINCT posts.ID) AS total'])
            ->from('posts')
            ->join('term_relationships', ['posts.ID', 'term_relationships.object_id'])
            ->where('term_relationships.term_taxonomy_id', 'IN', $termTaxonomyIds)
            ->where('posts.post_type', 'IN', $args['post_type'])
            ->where('posts.post_status', '=', 'publish')
            ->where('posts.post_author', '=', $args['author_id'])
            ->whereDate('posts.post_date', $args['date'])
            ->groupBy('term_relationships.term_taxonomy_id')
            ->getAll();

        return $this->pluckCounts($rows);
    }

    /**
     * Approved comments written in the date range on the published posts of each term.
     *
     * @param int[] $termTaxonomyIds
     * @param array $args date, post_type, author_id.
     * @return int[] term_taxonomy_id => count.
     */
    public function countCommentsByTerms($termTaxonomyIds, $args = [])
    {
        $termTaxonomyIds = array_values(array_filter(array_map('intval', (array) $termTaxonomyIds)));

        if (empty($termTaxonomyIds)) {
            return [];
        }

        $args = $this->parseArgs($args, [
            'date'      => '',
            'author_id' => '',
            'post_type' => Helper::getPostTypes()
        ]);

        $rows = Query::select(['term_relationships.term_taxonomy_id AS id', 'COUNT(DISTINCT comments.comment_ID) AS total'])
            ->from('comments')
            ->join('posts', ['comments.comment_post_ID', 'posts.ID'])
            ->join('term_relationships', ['posts.ID', 'term_relationships.object_id'])
            ->where('term_relationships.term_taxonomy_id', 'IN', $termTaxonomyIds)
            ->where('posts.post_type', 'IN', $args['post_type'])
            ->where('posts.post_status', '=', 'publish')
            ->where('posts.post_author', '=', $args['author_id'])
            ->where('comments.comment_approved', '=', '1')
            ->where('comments.comment_type', '=', 'comment')
            ->whereDate('comments.comment_date', $args['date'])
            ->groupBy('term_relationships.term_taxonomy_id')
            ->getAll();

        return $this->pluckCounts($rows);
    }
}
