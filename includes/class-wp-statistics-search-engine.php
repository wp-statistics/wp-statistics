<?php

namespace WP_STATISTICS;

use WP_Statistics\Service\Analytics\Referrals\ReferralsDatabase;

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
     * @deprecated This function is deprecated. use ReferralsDatabase::getList()
     * @param bool $all
     * @return array
     */
    public static function getList($all = false)
    {
        _deprecated_function('getList', '14.11', 'ReferralsDatabase::getList()');

        $referralsDatabase  = new ReferralsDatabase();
        $referralsDatabase  = $referralsDatabase->getList();
        $searchEngines      = $referralsDatabase['source_channels']['search']['channels'];

        $engines = [];
        foreach ($searchEngines as $searchEngine) {
            $engines[$searchEngine['identifier']] = [
                'name'          => $searchEngine['name'],
                'translated'    => $searchEngine['name'],
                'tag'           => $searchEngine['identifier']
            ];
        }

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