<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\Helper;
use WP_Statistics\Service\Posts\WordCount;
use WP_Statistics\Utils\Query;

class PostsModel extends DataProvider
{

    public function count($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => '',
        ]);

        $totalPosts = Query::select('COUNT(ID)')
            ->fromTable('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->bypassCache($bypassCache)
            ->getVar();

        return $totalPosts ? $totalPosts : 0;
    }

    public function countTotalWords($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => ''
        ]);

        $wordsCountMetaKey = WordCount::WORDS_COUNT_META_KEY;

        $totalWords = Query::select("SUM(meta_value)")
            ->fromTable('posts')
            ->join('postmeta', ['ID', 'post_id'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->where('meta_key', '=', $wordsCountMetaKey)
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->bypassCache($bypassCache)
            ->getVar();

        return $totalWords ? $totalWords : 0;
    }

    public function averageWordsPerPost($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => ''
        ]);

        $totalWords = $this->countTotalWords($args);
        $totalPosts = $this->count($args);

        return $totalWords ? ($totalWords / $totalPosts) : 0;
    }

    public function countTotalComments($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => ''
        ]);

        $totalWords = Query::select('COUNT(comment_ID)')
            ->fromTable('posts')
            ->join('comments', ['ID', 'comment_post_ID'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->bypassCache($bypassCache)
            ->getVar();

        return $totalWords ? $totalWords : 0;
    }

    public function averageCommentsPerPost($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => ''
        ]);

        $totalComments  = $this->countTotalComments($args);
        $totalPosts     = $this->count($args);

        return $totalComments ? ($totalComments / $totalPosts) : 0;
    }

    public function publishOverview($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => date('Y-m-d', strtotime('-365 days')),
            'to'        => date('Y-m-d', time()),
            'post_type' => Helper::get_list_post_type()
        ]);

        $overview = Query::select(['DATE(post_date) as date', 'COUNT(ID) as posts'])
            ->fromTable('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->groupBy('Date(post_date)')
            ->bypassCache($bypassCache)
            ->getAll();

        return $overview;
    }
}