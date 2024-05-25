<?php

namespace WP_Statistics\Models;

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

    public function averageWordsPerPost($args, $bypassCache = false)
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
}