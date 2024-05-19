<?php

namespace WP_STATISTICS;

use WP_Statistics\Service\Analytics\VisitorProfile;

class Hits
{
    /**
     * Rest-APi Hit Record Params Key
     *
     * @var string
     */
    public static $rest_hits_key = 'wp_statistics_hit_rest';

    /**
     * Rest-Api Hit Data
     *
     * @var object
     */
    public $rest_hits;

    /**
     * WP_Statistics Hits Class.
     *
     * @throws \Exception
     */
    public function __construct()
    {

        # Sanitize Hit Data if Has Rest-Api Process
        if (self::is_rest_hit()) {

            # Get Hit Data
            $this->rest_hits = (object)self::rest_params();

            # Filter Data
            add_filter('wp_statistics_current_page', array($this, 'set_current_page'));
            add_filter('wp_statistics_page_uri', array($this, 'set_page_uri'));
            add_filter('wp_statistics_track_all_pages', array($this, 'set_track_all'));
            add_filter('wp_statistics_user_id', array($this, 'set_current_user'));
        }

        # Record Login Page Hits
        if (!Option::get('exclude_loginpage')) {
            add_action('init', array($this, 'record_login_page_hits'));
        }

        # Record WordPress Front Page Hits
        add_action('wp', array($this, 'record_wp_hits'));
    }

    /**
     * Set is track All Pages
     *
     * @param $track_all
     * @return mixed
     */
    public function set_track_all($track_all)
    {
        if (isset($this->rest_hits->track_all) and $this->rest_hits->track_all == 1) {
            $track_all = true;
        }

        return $track_all;
    }

    /**
     * Set Current Page
     *
     * @param $current_page
     * @return array
     */
    public function set_current_page($current_page)
    {

        if (isset($this->rest_hits->current_page_type) and isset($this->rest_hits->current_page_id)) {
            return array(
                'type'         => esc_sql($this->rest_hits->current_page_type),
                'id'           => esc_sql($this->rest_hits->current_page_id),
                'search_query' => isset($this->rest_hits->search_query) ? base64_decode($this->rest_hits->search_query) : ''
            );
        }

        return $current_page;
    }

    /**
     * Set Page Uri
     *
     * @param $page_uri
     * @return string
     */
    public function set_page_uri($page_uri)
    {
        return isset($this->rest_hits->page_uri) ? base64_decode($this->rest_hits->page_uri) : $page_uri;
    }

    /**
     * Set User ID
     *
     * @param $userId
     * @return mixed
     */
    public function set_current_user($userId)
    {
        $userIdFromGlobalVar = isset($GLOBALS['wp_statistics_user_id']) ? $GLOBALS['wp_statistics_user_id'] : 0;

        if (!$userId && $userIdFromGlobalVar) {
            $userId = $userIdFromGlobalVar;
        }

        return $userId;
    }

    /**
     * Check If Record Hits in Rest-Api Request
     *
     * @return bool
     */
    public static function is_rest_hit()
    {
        return (Helper::is_rest_request() and isset($_REQUEST[self::$rest_hits_key]));
    }

    /**
     * Get Params Value in Rest-APi Request Hit
     *
     * @param $params
     * @return Mixed
     */
    public static function rest_params($params = false)
    {
        $data = array();
        if (Helper::is_rest_request() and isset($_REQUEST[Hits::$rest_hits_key])) {
            foreach ($_REQUEST as $key => $value) {
                $data[$key] = $value;
            }

            return ($params === false ? $data : (isset($data[$params]) ? $data[$params] : false));
        }

        return $data;
    }

    /**
     * Get Visitor information and Record To DB
     *
     * @throws \Exception
     */
    public static function record()
    {
        $visitorProfile = new VisitorProfile();

        # Check Exclusion This Hits
        $exclusion = Exclusion::check($visitorProfile);

        # Record Hits Exclusion
        if ($exclusion['exclusion_match'] === true) {
            Exclusion::record($exclusion);
        }

        # Record User Views
        if (Visit::active() and $exclusion['exclusion_match'] === false) {
            Visit::record();
        }

        # Record Visitor Detail
        if (Visitor::active()) {
            $visitor_id = Visitor::record($visitorProfile, $exclusion);
        }

        # Record Search Engine
        if (isset($visitor_id) and $visitor_id > 0 and $exclusion['exclusion_match'] === false) {
            SearchEngine::record(array('visitor_id' => $visitor_id));
        }

        # Record Pages
        if (Pages::active() and $exclusion['exclusion_match'] === false and Pages::is_track_all_page() === true) {
            $page_id = Pages::record($visitorProfile);
        }

        # Record Visitor Relationship
        if (isset($visitor_id) and $visitor_id > 0 and isset($page_id) and $page_id > 0 and Option::get('visitors_log')) {
            Visitor::save_visitors_relationships($page_id, $visitor_id);
        }

        # Record User Online
        if (UserOnline::active() and ($exclusion['exclusion_match'] === false)) {
            $pageID = isset($page_id) && $page_id > 0 ? $page_id : 0;
            UserOnline::record($visitorProfile, [
                'page_id' => $pageID,
            ]);
        }

        return $exclusion;
    }

    /**
     * Record Hits in Login Page
     *
     * @throws \Exception
     */
    public static function record_login_page_hits()
    {
        if (Helper::is_login_page()) {
            self::record();
        }
    }

    /**
     * Record WordPress Frontend Hits
     *
     * @throws \Exception
     */
    public static function record_wp_hits()
    {
        if (!Option::get('use_cache_plugin') and !Helper::dntEnabled()) {
            Hits::record();
        }
    }
}

new Hits;