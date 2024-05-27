<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\Helper;
use WP_Statistics\Service\Posts\WordCount;
use WP_Statistics\Utils\Query;

class PostsModel extends DataProvider
{

    public function countPosts($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => '',
            'author_id' => ''
        ]);

        $totalPosts = Query::select('COUNT(ID)')
            ->from('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->where('post_author', '=', $args['author_id'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->bypassCache($bypassCache)
            ->getVar();

        return $totalPosts ? $totalPosts : 0;
    }

    public function countWords($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => ''
        ]);

        $wordsCountMetaKey = WordCount::WORDS_COUNT_META_KEY;

        $totalWords = Query::select("SUM(meta_value)")
            ->from('posts')
            ->join('postmeta', ['posts.ID', 'postmeta.post_id'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->where('meta_key', '=', $wordsCountMetaKey)
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->bypassCache($bypassCache)
            ->getVar();

        return $totalWords ? $totalWords : 0;
    }

    public function countComments($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => ''
        ]);

        $totalWords = Query::select('COUNT(comment_ID)')
            ->from('posts')
            ->join('comments', ['posts.ID', 'comments.comment_post_ID'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->bypassCache($bypassCache)
            ->getVar();

        return $totalWords ? $totalWords : 0;
    }

    public function publishOverview($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => date('Y-m-d', strtotime('-365 days')),
            'to'        => date('Y-m-d', time()),
            'post_type' => Helper::get_list_post_type()
        ]);

        $overview = Query::select(['DATE(post_date) as date', 'COUNT(ID) as posts'])
            ->from('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->groupBy('Date(post_date)')
            ->bypassCache($bypassCache)
            ->getAll();

        return $overview;
    }

    public function getPostsReportData($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => Helper::get_list_post_type(),
            'order_by'  => 'title',
            'order'     => 'DESC',
            'page'     => 1,
            'per_page' => 5,
            'author_id' => ''
        ]);

        $commentsQuery = Query::select(['comment_post_ID', 'COUNT(comment_ID) AS total_comments'])
            ->from('comments')
            ->groupBy('comment_post_ID')
            ->getQuery();

        $viewsQuery = Query::select(['id', 'SUM(count) AS views'])
            ->from('pages')
            ->groupBy('id')
            ->getQuery();

        $result = Query::select([
                'posts.ID AS post_id',
                'posts.post_author AS author_id',
                'posts.post_title AS title',
                'pages.views AS views',
                'comments.total_comments AS comments',
                "MAX(CASE WHEN postmeta.meta_key = 'wp_statistics_words_count' THEN postmeta.meta_value ELSE 0 END) AS words"
            ])
            ->from('posts')
            ->joinQuery($commentsQuery, ['posts.ID', 'comments.comment_post_ID'], 'comments', 'LEFT')
            ->joinQuery($viewsQuery, ['posts.ID', 'pages.id'], 'pages', 'LEFT')
            ->join('postmeta', ['posts.ID', 'postmeta.post_id'], [], 'LEFT')
            ->where('post_type', 'IN', $args['post_type'])
            ->where('post_status', '=', 'publish')
            ->where('posts.post_author', '=', $args['author_id'])
            ->groupBy('posts.ID')
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->bypassCache($bypassCache)
            ->getAll();

        return $result;
    }
}