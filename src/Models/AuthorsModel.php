<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Admin\Posts\WordCountService;
use WP_Statistics\Utils\Query;

class AuthorsModel extends BaseModel
{
    /**
     * Counts the authors based on the given arguments.
     * By default, it will return total number of authors.
     *
     * @param array $args An array of arguments to filter the count.
     * @return int The total number of distinct authors. Returns 0 if no authors are found.
     */
    public function countAuthors($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type()
        ]);

        return Query::select('COUNT(DISTINCT post_author)')
            ->from('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', $args['date'])
            ->getVar();
    }

    public function getTopViewingAuthors($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'order_by'  => 'total_views',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 5,
            'taxonomy'  => '',
            'term'      => ''
        ]);

        $query = Query::select([
                'DISTINCT posts.post_author AS id',
                'display_name AS name',
                'SUM(pages.count) AS total_views'
            ])
            ->from('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->join('pages', ['posts.ID', 'pages.id'], [['pages.type', 'IN', $this->getResourceTypes($args['post_type'], false)]], 'LEFT')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('date', $args['date'])
            ->groupBy('post_author')
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page']);

        $this->joinTermFilter($query, $args);

        $result = $query->getAll();

        return $result ? $result : [];
    }

    public function getAuthorsByPostPublishes($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'page'      => 1,
            'per_page'  => 5,
            'taxonomy'  => '',
            'term'      => ''
        ]);

        $query = Query::select(['DISTINCT post_author as id', 'display_name as name', 'COUNT(*) as post_count'])
            ->from('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', $args['date'])
            ->groupBy('posts.post_author')
            ->orderBy('post_count')
            ->perPage($args['page'], $args['per_page']);

        if (!empty($args['taxonomy']) || !empty($args['term'])) {
            $query
                ->join('term_relationships', ['posts.ID', 'term_relationships.object_id'])
                ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
                ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy']);

            if (!empty($args['term'])) {
                $query
                    ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
                    ->where('terms.term_id', '=', $args['term']);
            }
        }

        $result = $query->getAll();

        return $result ? $result : [];
    }

    public function getAuthorsByCommentsPerPost($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'page'      => 1,
            'per_page'  => 5
        ]);

        $result = Query::select([
                'DISTINCT posts.post_author AS id',
                'display_name AS name',
                'COUNT(comments.comment_ID) / COUNT(DISTINCT posts.ID) AS average_comments'
            ])
            ->from('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->join('comments', ['posts.ID', 'comments.comment_post_ID'])
            ->where('comments.comment_type', '=', 'comment')
            ->where('comments.comment_approved', '=', '1')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', $args['date'])
            ->groupBy('post_author')
            ->orderBy('average_comments')
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $result ? $result : [];
    }

    public function getAuthorsByViewsPerPost($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'order_by'  => 'average_views',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 5
        ]);

        $result = Query::select([
                'DISTINCT posts.post_author AS id',
                'display_name AS name',
                'SUM(pages.count) AS total_views',
                'COUNT(DISTINCT posts.ID) AS total_posts',
                'SUM(pages.count) / COUNT(DISTINCT posts.ID) AS average_views'
            ])
            ->from('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->join('pages', ['posts.ID', 'pages.id'], [['pages.type', 'IN', $this->getResourceTypes($args['post_type'], false)]])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', $args['date'])
            ->groupBy('post_author')
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $result ? $result : [];
    }

    public function getAuthorsByWordsPerPost($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'page'      => 1,
            'per_page'  => 5
        ]);

        $result = Query::select([
                'DISTINCT posts.post_author AS id',
                'display_name AS name',
                'SUM(postmeta.meta_value) / COUNT(DISTINCT posts.ID) AS average_words'
            ])
            ->from('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->join('postmeta', ['posts.ID', 'postmeta.post_id'])
            ->where('meta_key', '=', WordCountService::WORDS_COUNT_META_KEY)
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', $args['date'])
            ->groupBy('post_author')
            ->orderBy('average_words')
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $result ? $result : [];
    }

    public function getAuthorsReportData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'order_by'  => 'total_views',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 5
        ]);

        $commentsQuery  = Query::select(['DISTINCT post_author', 'COUNT(comment_ID) AS total_comments'])
            ->from('posts')
            ->join('comments', ['posts.ID', 'comments.comment_post_ID'])
            ->where('comments.comment_type', '=', 'comment')
            ->where('comments.comment_approved', '=', '1')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', $args['date'])
            ->groupBy('post_author')
            ->getQuery();

        $viewsQuery = Query::select(['DISTINCT post_author', 'SUM(count) AS total_views'])
            ->from('posts')
            ->join('pages', ['posts.ID', 'pages.id'], [['pages.type', 'IN', $this->getResourceTypes($args['post_type'], false)]])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('pages.date', $args['date'])
            ->groupBy('post_author')
            ->getQuery();

        $authorQuery = Query::select(['post_author'])
            ->from('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->groupBy('post_author')
            ->getQuery();

        $fields = [
            'users.ID AS id',
            'users.display_name AS name',
            'COUNT(DISTINCT posts.ID) AS total_posts',
            'comments.total_comments AS total_comments',
            'views.total_views AS total_views',
            'comments.total_comments / COUNT(DISTINCT posts.ID) AS average_comments',
            'views.total_views / COUNT(DISTINCT posts.ID) AS average_views'
        ];

        if (WordCountService::isActive()) {
            $fields[] = 'words.total_words AS total_words';
            $fields[] = 'words.total_words / COUNT(DISTINCT posts.ID) AS average_words';
        }

        $query = Query::select($fields)
            ->from('users')
            ->join(
                'posts',
                ['users.ID', 'posts.post_author'],
                [
                    ['posts.post_status', '=', 'publish'],
                    ['posts.post_type', 'IN', $args['post_type']],
                    ['DATE(posts.post_date)', 'BETWEEN', [$args['date']['from'], $args['date']['to']]]
                ],
                'LEFT'
            )
            ->joinQuery($authorQuery, ['users.ID', 'authors.post_author'], 'authors')
            ->joinQuery($commentsQuery, ['users.ID', 'comments.post_author'], 'comments', 'LEFT')
            ->joinQuery($viewsQuery, ['users.ID', 'views.post_author'], 'views', 'LEFT')
            ->groupBy(['users.ID', 'users.display_name'])
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page']);

        if (WordCountService::isActive()) {
            $wordsQuery = Query::select(['DISTINCT post_author', 'SUM(meta_value) AS total_words'])
                ->from('posts')
                ->join('postmeta', ['posts.ID', 'postmeta.post_id'])
                ->where('postmeta.meta_key', '=', 'wp_statistics_words_count')
                ->where('post_status', '=', 'publish')
                ->where('post_type', 'IN', $args['post_type'])
                ->whereDate('post_date', $args['date'])
                ->groupBy('post_author')
                ->getQuery();

            $query->joinQuery($wordsQuery, ['users.ID', 'words.post_author'], 'words', 'LEFT');
        }

        $result = $query->getAll();

        return $result ? $result : [];
    }

    /**
     * Top authors for the leaderboard widgets, ranked on the server by one metric. Same numbers as
     * the Authors report:
     *  - views:          views in the range of the author's published content (any publish date);
     *  - published:      posts published in the range ("Posts");
     *  - views_per_post: views / posts published in the range (NULL when nothing was published).
     *  - comments:       approved comments written in the range on the author's published content.
     * An author is listed when they have views or a published post in the range, in both directions,
     * so ascending ("lowest first") ranks the active authors and never fills up with authors who did
     * nothing in the range. Ties fall back to views, in the same direction. A missing Per post (nothing
     * published) counts as the lowest value, the "0" the table shows.
     *
     * @param array $args date, post_type, taxonomy, term, order_by (views|published|views_per_post|comments),
     *                    order (DESC|ASC), page, per_page.
     * @return object[] id, name, nicename, views, posts_with_views, published, views_per_post (+ comments when ranked by it).
     */
    public function getAuthorsLeaderboard($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'order_by'  => 'views',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 5,
            'taxonomy'  => '',
            'term'      => ''
        ]);

        $orderBy = in_array($args['order_by'], ['views', 'published', 'views_per_post', 'comments'], true) ? $args['order_by'] : 'views';
        $order   = strtoupper((string) $args['order']) === 'ASC' ? 'ASC' : 'DESC';

        $viewsQuery = Query::select([
                'posts.post_author AS author_id',
                'SUM(pages.count) AS views',
                'COUNT(DISTINCT posts.ID) AS posts_with_views'
            ])
            ->from('posts')
            ->join('pages', ['posts.ID', 'pages.id'], [['pages.type', 'IN', $this->getResourceTypes($args['post_type'], false)]])
            ->where('posts.post_status', '=', 'publish')
            ->where('posts.post_type', 'IN', $args['post_type'])
            ->whereDate('pages.date', $args['date'])
            ->groupBy('posts.post_author');
        $this->joinTermFilter($viewsQuery, $args);

        $publishedQuery = Query::select([
                'posts.post_author AS author_id',
                'COUNT(DISTINCT posts.ID) AS published'
            ])
            ->from('posts')
            ->where('posts.post_status', '=', 'publish')
            ->where('posts.post_type', 'IN', $args['post_type'])
            ->whereDate('posts.post_date', $args['date'])
            ->groupBy('posts.post_author');
        $this->joinTermFilter($publishedQuery, $args);

        // Everyone with a published post of these types; the two side queries pick the ones active in the range.
        $authorsQuery = Query::select(['DISTINCT posts.post_author'])
            ->from('posts')
            ->where('posts.post_status', '=', 'publish')
            ->where('posts.post_type', 'IN', $args['post_type'])
            ->getQuery();

        $fields = [
            'users.ID AS id',
            'users.display_name AS name',
            'users.user_nicename AS nicename',
            'COALESCE(author_views.views, 0) AS views',
            'COALESCE(author_views.posts_with_views, 0) AS posts_with_views',
            'COALESCE(author_published.published, 0) AS published',
            'COALESCE(author_views.views, 0) / NULLIF(author_published.published, 0) AS views_per_post'
        ];

        // Comments are only joined when they are the ranking; other rankings get them from countCommentsByAuthors().
        $commentsQuery = null;
        if ($orderBy === 'comments') {
            $fields[]      = 'COALESCE(author_comments.comments, 0) AS comments';
            $commentsQuery = Query::select([
                    'posts.post_author AS author_id',
                    'COUNT(comments.comment_ID) AS comments'
                ])
                ->from('comments')
                ->join('posts', ['comments.comment_post_ID', 'posts.ID'])
                ->where('posts.post_status', '=', 'publish')
                ->where('posts.post_type', 'IN', $args['post_type'])
                ->where('comments.comment_approved', '=', '1')
                ->where('comments.comment_type', '=', 'comment')
                ->whereDate('comments.comment_date', $args['date'])
                ->groupBy('posts.post_author');
            $this->joinTermFilter($commentsQuery, $args);
        }

        $query = Query::select($fields)
            ->fromQuery($authorsQuery, 'authors')
            ->join('users', ['authors.post_author', 'users.ID'])
            ->joinQuery($viewsQuery->getQuery(), ['users.ID', 'author_views.author_id'], 'author_views', 'LEFT')
            ->joinQuery($publishedQuery->getQuery(), ['users.ID', 'author_published.author_id'], 'author_published', 'LEFT');

        if ($commentsQuery) {
            $query->joinQuery($commentsQuery->getQuery(), ['users.ID', 'author_comments.author_id'], 'author_comments', 'LEFT');
        }

        $query->whereRaw('(author_views.author_id IS NOT NULL OR author_published.author_id IS NOT NULL)');

        $query->orderBy($orderBy === 'views' ? ['views', 'published'] : [$orderBy, 'views'], $order)
            ->perPage($args['page'], $args['per_page']);

        $this->allowCachingForPastRange($query, $args['date']);

        $result = $query->getAll();

        return $result ? $result : [];
    }

    /**
     * Published posts per author with a publish date in the range.
     *
     * @param int[] $authorIds
     * @param array $args date, post_type, taxonomy, term.
     * @return int[] author ID => count.
     */
    public function countPublishedByAuthors($authorIds, $args = [])
    {
        $authorIds = array_values(array_filter(array_map('intval', (array) $authorIds)));

        // An empty IN list would drop the filter and count every author
        if (empty($authorIds)) {
            return [];
        }

        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'taxonomy'  => '',
            'term'      => ''
        ]);

        $query = Query::select(['posts.post_author AS id', 'COUNT(DISTINCT posts.ID) AS total'])
            ->from('posts')
            ->where('posts.post_author', 'IN', $authorIds)
            ->where('posts.post_status', '=', 'publish')
            ->where('posts.post_type', 'IN', $args['post_type'])
            ->whereDate('posts.post_date', $args['date'])
            ->groupBy('posts.post_author');

        $this->joinTermFilter($query, $args);

        return $this->pluckCounts($query->getAll());
    }

    /**
     * Approved comments written in the date range on each author's published posts.
     *
     * @param int[] $authorIds
     * @param array $args date, post_type, taxonomy, term.
     * @return int[] author ID => count.
     */
    public function countCommentsByAuthors($authorIds, $args = [])
    {
        $authorIds = array_values(array_filter(array_map('intval', (array) $authorIds)));

        if (empty($authorIds)) {
            return [];
        }

        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'taxonomy'  => '',
            'term'      => ''
        ]);

        $query = Query::select(['posts.post_author AS id', 'COUNT(comments.comment_ID) AS total'])
            ->from('comments')
            ->join('posts', ['comments.comment_post_ID', 'posts.ID'])
            ->where('posts.post_author', 'IN', $authorIds)
            ->where('posts.post_status', '=', 'publish')
            ->where('posts.post_type', 'IN', $args['post_type'])
            ->where('comments.comment_approved', '=', '1')
            ->where('comments.comment_type', '=', 'comment')
            ->whereDate('comments.comment_date', $args['date'])
            ->groupBy('posts.post_author');

        $this->joinTermFilter($query, $args);

        return $this->pluckCounts($query->getAll());
    }

    public function getAuthorsPagesData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'order_by'  => 'page_views',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 5
        ]);

        $viewsQuery  = Query::select(['id AS author_id', 'SUM(count) AS total_author_views'])
            ->from('pages')
            ->where('type', '=', 'author')
            ->whereDate('date', $args['date'])
            ->groupBy('id')
            ->getQuery();

        $authorQuery = Query::select(['post_author'])
            ->from('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->groupBy('post_author')
            ->getQuery();

        $result = Query::select([
                'users.ID AS id',
                'users.display_name AS name',
                'COALESCE(COUNT(DISTINCT posts.ID), 0) AS total_posts',
                'COALESCE(views.total_author_views, 0) AS page_views'
            ])
            ->from('users')
            ->join(
                'posts',
                ['users.ID', 'posts.post_author'],
                [
                    ['posts.post_status', '=', 'publish'],
                    ['posts.post_type', 'IN', $args['post_type']],
                    ['DATE(posts.post_date)', 'BETWEEN', [$args['date']['from'], $args['date']['to']]]
                ],
                'LEFT'
            )
            ->joinQuery($authorQuery, ['users.ID', 'authors.post_author'], 'authors')
            ->joinQuery($viewsQuery, ['users.ID', 'views.author_id'], 'views', 'LEFT')
            ->groupBy(['users.ID', 'users.display_name'])
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $result ? $result : [];
    }
}