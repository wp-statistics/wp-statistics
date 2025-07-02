<?php

namespace WP_STATISTICS;

class Referred
{
    /**
     * Top Referring Transient name
     *
     * @var string
     */
    public static $top_referring_transient = 'wps_top_referring';

    /**
     * Referrer Spam List
     *
     * @var string
     */
    public static $referrer_spam_link = 'https://cdn.jsdelivr.net/gh/matomo-org/referrer-spam-list@master/spammers.txt';

    /**
     * Referred constructor.
     */
    public function __construct()
    {
        # Remove Cache When Delete Visitor Table
        add_action('wp_statistics_truncate_table', array($this, 'deleteCacheData'));
    }

    /**
     * Get referer URL
     *
     * @return string
     */
    public static function getRefererURL()
    {
        if (Helper::is_rest_request() && isset($_REQUEST['referred'])) {

            $referred = $_REQUEST['referred'];

            /**
             * Decode the url if the request type is client-side tracking
             */
            if (Option::get('use_cache_plugin')) {
                $referred = base64_decode($referred);
            }

            return sanitize_url(wp_unslash(urldecode($referred)));
        }

        return (isset($_SERVER['HTTP_REFERER']) ? sanitize_url(wp_unslash($_SERVER['HTTP_REFERER'])) : '');
    }

    /**
     * Return the referrer link for the current user.
     *
     * @return array|bool|string
     */
    public static function get()
    {

        // Get Default
        $referred = self::getRefererURL();

        // Sanitize Referer Url
        $referred = esc_url_raw(wp_strip_all_tags($referred));

        // If Referer is empty, set only ''
        if (empty($referred)) {
            $referred = '';
        }

        $referred = Helper::FilterQueryStringUrl($referred, Helper::get_query_params_allow_list());

        return apply_filters('wp_statistics_user_referer', $referred);
    }

    /**
     * Get referrer link
     *
     * @param string $referrer
     * @param string $title
     * @param bool $is_blank
     * @return string | void
     */
    public static function get_referrer_link($referrer, $title = '', $is_blank = false)
    {

        // Sanitize Link
        $html_referrer = self::html_sanitize_referrer($referrer);

        // Check Url Protocol
        if (!Helper::check_url_scheme($html_referrer)) {
            $html_referrer = '//' . $html_referrer;
        }

        $html_referrer = esc_url($html_referrer);

        // Parse Url
        $base_url = @wp_parse_url($html_referrer);

        // Get Page title
        $title = (trim($title) == "" ? $html_referrer : $title);

        // If referrer is the current site or empty, return empty string
        if (empty($base_url['host']) || strpos($referrer, site_url()) !== false) {
            return \WP_STATISTICS\Admin_Template::UnknownColumn();
        }

        // Remove Url prefixes
        $host_name = Helper::get_domain_name($base_url['host']);

        // Get Html Link
        return "<a class='wps-link-arrow' href='{$html_referrer}' title='{$title}'" . ($is_blank === true ? ' target="_blank"' : '') . "><span >{$host_name}</span></a>";
    }

    /**
     * Sanitizes the referrer
     *
     * @param     $referrer
     * @param int $length
     * @return string
     */
    public static function html_sanitize_referrer($referrer, $length = -1)
    {
        $referrer = trim($referrer);

        if ('data:' == strtolower(substr($referrer, 0, 5))) {
            $referrer = 'http://127.0.0.1';
        }

        if ('javascript:' == strtolower(substr($referrer, 0, 11))) {
            $referrer = 'http://127.0.0.1';
        }

        if ($length > 0) {
            $referrer = substr($referrer, 0, $length);
        }

        return $referrer;
    }

    /**
     * Get Number Referer Domain
     *
     * @param $url
     * @param string $type [list|number]
     * @param array $time_rang
     * @param null $limit
     * @return array
     * @throws \Exception
     */
    public static function get_referer_from_domain($url, $type = 'number', $time_rang = array(), $limit = null)
    {
        global $wpdb;

        //Get Domain Name
        $search_url = Helper::get_domain_name($url);

        //Prepare SQL
        $time_sql = '';
        if (count($time_rang) > 0 and !empty($time_rang)) {
            $time_sql = sprintf("AND `last_counter` BETWEEN '%s' AND '%s'", $time_rang[0], $time_rang[1]);
        }

        $sql = $wpdb->prepare(
            "SELECT " . ($type == 'number' ? 'COUNT(*)' : '*') . ", CAST(`version` AS SIGNED) AS `casted_version`
             FROM `" . DB::table('visitor') . "`
             WHERE `referred` REGEXP \"^[A-Za-z0-9\\.-]+\\.[A-Za-z]{2,}\"
               AND referred <> ''
               AND (`referred` LIKE %s OR `referred` LIKE %s OR `referred` LIKE %s OR `referred` LIKE %s)
               " . $time_sql . "
             ORDER BY `" . DB::table('visitor') . "`.`ID` DESC " . ($limit != null ? " LIMIT " . $limit : ""),
            'https://www.' . $wpdb->esc_like($search_url) . '%',
            'https://' . $wpdb->esc_like($search_url) . '%',
            'http://www.' . $wpdb->esc_like($search_url) . '%',
            'http://' . $wpdb->esc_like($search_url) . '%'
        ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        
        //Get Count
        return ($type == 'number' ? $wpdb->get_var($sql) : Visitor::prepareData($wpdb->get_results($sql))); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared	
    }

    /**
     * Downloads the referrer spam database
     *
     * @see https://github.com/matomo-org/referrer-spam-blacklist.
     * @return string
     */
    public static function download_referrer_spam()
    {

        if (Option::get('referrerspam') == false) {
            return '';
        }

        // Download the file from MaxMind, this places it in a temporary location.
        $response = wp_remote_get(self::$referrer_spam_link, array('timeout' => 60));
        if (is_wp_error($response)) {
            return false;
        }
        $referrerspamlist = wp_remote_retrieve_body($response);
        if (is_wp_error($referrerspamlist)) {
            return false;
        }

        if ($referrerspamlist != '' || Option::get('referrerspamlist') != '') {
            Option::update('referrerspamlist', $referrerspamlist);
        }

        return true;
    }

    /**
     * Get Top Referring Site
     *
     * @param int $number
     * @return array
     * @throws \Exception
     */
    public static function getTop($number = 10)
    {
        global $wpdb;

        //Get Top Referring
        if (false === ($get_urls = get_transient(self::$top_referring_transient))) {

            $sql = $wpdb->prepare("ORDER BY `number` DESC LIMIT %d", $number);

            $result = $wpdb->get_results(self::generateReferSql($sql, '')); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared	
            foreach ($result as $items) {
                $get_urls[$items->domain] = self::get_referer_from_domain($items->domain);
            }

            // Put the results in a transient. Expire after 12 hours.
            set_transient(self::$top_referring_transient, $get_urls, 20 * HOUR_IN_SECONDS);
        }

        // Return Data
        return self::PrepareReferData($get_urls);
    }

    /**
     * Prepare Refer Data
     *
     * @param $get_urls
     * @return array
     * @throws \Exception
     */
    public static function PrepareReferData($referrals)
    {
        if (empty($referrals)) return [];

        $list = [];
        foreach ($referrals as $domain => $number) {
            $referrerUrl = Referred::html_sanitize_referrer($domain);
            $list[]      = [
                'domain'    => $domain,
                'page_link' => Menus::admin_url('referrers', ['referrer' => $referrerUrl]),
                'number'    => number_format_i18n($number)
            ];
        }

        // Return Data
        return $list;
    }

    /**
     * Get Referred Site List
     *
     * @param array $args
     * @return mixed
     */
    public static function getList($args = array())
    {
        global $wpdb;

        // Check Custom Date
        $where = '';
        if (isset($args['from']) and isset($args['to'])) {
            $where = "AND `last_counter` BETWEEN '" . $args['from'] . "' AND '" . $args['to'] . "' ";
        }

        // Check Min Number
        $having = '';
        if (isset($args['min'])) {
            $having = "HAVING `number` >" . $args['min'];
        }

        // Check Limit
        $limit = '';
        if (isset($args['limit'])) {
            $limit = "LIMIT " . $args['limit'];
        }

        // Return List
        return $wpdb->get_results(self::generateReferSql($having . " ORDER BY `number` DESC " . $limit, $where)); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
    }

    /**
     * Generate Basic SQL Refer List
     *
     * @param string $where
     * @param string $extra
     * @return string
     */
    public static function generateReferSql($extra = '', $where = '')
    {

        // Check Protocol Of domain
        $domain_name = rtrim(preg_replace('/^https?:\/\//', '', get_site_url()), " / ");
        foreach (array("http", "https", "ftp") as $protocol) {
            foreach (array('', 'www.') as $w3) {
                $where .= " AND `referred` NOT LIKE '{$protocol}://{$w3}{$domain_name}%' ";
            }
        }

        // Return SQL
        return "SELECT referred AS `domain`, count(referred) AS `number`
            FROM " . DB::table('visitor') . "
            WHERE `referred` REGEXP \"^[\.A-Za-z0-9\-]+\\.[a-zA-Z]{2,4}\"
            AND referred <> '' " . $where . "
            GROUP BY `domain` " . $extra;
    }

    /**
     * Remove Complete Cache Data
     * @param $table_name
     */
    public function deleteCacheData($table_name)
    {
        if ($table_name == "visitor") {
            delete_transient(self::$top_referring_transient);
        }
    }
}

new Referred();