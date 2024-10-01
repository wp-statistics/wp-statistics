<?php

namespace WP_STATISTICS;

class SearchEngine
{
    /**
     * Default error not founding search engine
     *
     * @var string
     */
    public static $error_found = 'No search query found!';

    /**
     * Get base assets url search engine logo
     *
     * @return string
     */
    public static function Asset()
    {
        return WP_STATISTICS_URL . 'assets/images/search-engine/';
    }

    /**
     * Get List Of Search engine in WP Statistics
     *
     * @param bool $all
     * @return array
     */
    public static function getList($all = false)
    {

        // List OF Search engine
        $default = $engines = array(
            'ask'        => array(
                'name'         => 'Ask.com',
                'translated'   => __('Ask.com', 'wp-statistics'),
                'tag'          => 'ask',
                'sqlpattern'   => '%ask.com%',
                'regexpattern' => 'ask\.com',
                'querykey'     => 'q',
                'image'        => 'ask.png',
                'logo_url'     => self::Asset() . 'ask.png'
            ),
            'baidu'      => array(
                'name'         => 'Baidu',
                'translated'   => __('Baidu', 'wp-statistics'),
                'tag'          => 'baidu',
                'sqlpattern'   => '%baidu.com%',
                'regexpattern' => 'baidu\.com',
                'querykey'     => 'wd',
                'image'        => 'baidu.png',
                'logo_url'     => self::Asset() . 'baidu.png'
            ),
            'bing'       => array(
                'name'         => 'Bing',
                'translated'   => __('Bing', 'wp-statistics'),
                'tag'          => 'bing',
                'sqlpattern'   => '%bing.com%',
                'regexpattern' => 'bing\.com',
                'querykey'     => 'q',
                'image'        => 'bing.png',
                'logo_url'     => self::Asset() . 'bing.png'
            ),
            'clearch'    => array(
                'name'         => 'clearch.org',
                'translated'   => __('clearch.org', 'wp-statistics'),
                'tag'          => 'clearch',
                'sqlpattern'   => '%clearch.org%',
                'regexpattern' => 'clearch\.org',
                'querykey'     => 'q',
                'image'        => 'clearch.png',
                'logo_url'     => self::Asset() . 'clearch.png'
            ),
            'duckduckgo' => array(
                'name'         => 'DuckDuckGo',
                'translated'   => __('DuckDuckGo', 'wp-statistics'),
                'tag'          => 'duckduckgo',
                'sqlpattern'   => array('%duckduckgo.com%', '%ddg.gg%'),
                'regexpattern' => array('duckduckgo\.com', 'ddg\.gg'),
                'querykey'     => 'q',
                'image'        => 'duckduckgo.png',
                'logo_url'     => self::Asset() . 'duckduckgo.png'
            ),
            'google'     => array(
                'name'         => 'Google',
                'translated'   => __('Google', 'wp-statistics'),
                'tag'          => 'google',
                'sqlpattern'   => '%google.%',
                'regexpattern' => 'google\.',
                'querykey'     => 'q',
                'image'        => 'google.png',
                'logo_url'     => self::Asset() . 'google.png'
            ),
            'yahoo'      => array(
                'name'         => 'Yahoo!',
                'translated'   => __('Yahoo!', 'wp-statistics'),
                'tag'          => 'yahoo',
                'sqlpattern'   => '%yahoo.com%',
                'regexpattern' => 'yahoo\.com',
                'querykey'     => 'p',
                'image'        => 'yahoo.png',
                'logo_url'     => self::Asset() . 'yahoo.png'
            ),
            'yandex'     => array(
                'name'         => 'Yandex',
                'translated'   => __('Yandex', 'wp-statistics'),
                'tag'          => 'yandex',
                'sqlpattern'   => '%yandex.ru%',
                'regexpattern' => 'yandex\.ru',
                'querykey'     => 'text',
                'image'        => 'yandex.png',
                'logo_url'     => self::Asset() . 'yandex.png'
            ),
            'qwant'      => array(
                'name'         => 'Qwant',
                'translated'   => __('Qwant', 'wp-statistics'),
                'tag'          => 'qwant',
                'sqlpattern'   => '%qwant.com%',
                'regexpattern' => 'qwant\.com',
                'querykey'     => 'q',
                'image'        => 'qwant.png',
                'logo_url'     => self::Asset() . 'qwant.png'
            )
        );

        return apply_filters('wp_statistics_search_engine_list', $engines);
    }

    /**
     * Return Default Value if Search Engine Not Exist
     *
     * @return array
     */
    public static function default_engine()
    {
        return array(
            'name'         => _x('Unknown', 'Search Engine', 'wp-statistics'),
            'tag'          => '',
            'sqlpattern'   => '',
            'regexpattern' => '',
            'querykey'     => 'q',
            'image'        => 'unknown.png',
            'logo_url'     => self::Asset() . 'unknown.png'
        );
    }

    /**
     * Get Information About Custom Search Engine
     *
     * @param bool|false $engine
     * @return array|bool
     */
    public static function get($engine = false)
    {

        // If there is no URL and no referrer, always return false.
        if ($engine == false) {
            return false;
        }

        // Get the list of search engines
        $search_engines = self::getList();

        // Search Key in List
        if (array_key_exists($engine, $search_engines)) {
            return $search_engines[$engine];
        }

        // If no SE matched, return some defaults.
        return self::default_engine();
    }

}