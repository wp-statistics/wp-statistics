<?php

namespace WP_STATISTICS;

use Exception;
use WP_Statistics\Components\Singleton;
use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_Statistics\Service\Integrations\WpConsentApi;

class Hits extends Singleton
{
    /**
     * Rest-APi Hit Record Params Key
     *
     * @var string
     */
    public static $rest_hits_key = 'wp_statistics_hit';

    /**
     * Rest-Api Hit Data
     *
     * @var object
     */
    public $rest_hits;

    /**
     * WP_Statistics Hits Class.
     *
     * @throws Exception
     */
    public function __construct()
    {

        // Sanitize Hit Data if Has Rest-Api Process
        if (self::is_rest_hit()) {

            // Get Hit Data
            $this->rest_hits = (object)self::rest_params();

            // Filter Data
            add_filter('wp_statistics_current_page', array($this, 'set_current_page'));
            add_filter('wp_statistics_page_uri', array($this, 'set_page_uri'));
            add_filter('wp_statistics_user_id', array($this, 'set_current_user'));
        }

        // Record Login Page Hits
        if (!Option::get('exclude_loginpage')) {
            add_action('init', array($this, 'trackLoginPageCallback'));
        }

        // Server Side Tracking
        add_action('wp', array($this, 'trackServerSideCallback'));
    }

    /**
     * Set Current Page
     *
     * @param $current_page
     * @return array
     */
    public function set_current_page($current_page)
    {

        if (isset($this->rest_hits->source_type) and isset($this->rest_hits->source_id)) {
            return array(
                'type'         => esc_sql($this->rest_hits->source_type),
                'id'           => esc_sql($this->rest_hits->source_id),
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
     * Record the visitor
     *
     * @throws Exception
     */
    public static function record($visitorProfile = null)
    {
        if (!$visitorProfile) {
            $visitorProfile = new VisitorProfile();
        }

        /**
         * Check the exclusion
         */
        $exclusion = Exclusion::check($visitorProfile);

        /**
         * Record exclusion if needed & then skip the tracking
         */
        if ($exclusion['exclusion_match'] === true) {
            Exclusion::record($exclusion);

            throw new Exception($exclusion['exclusion_reason'], 200);
        }

        /**
         * Record User Views
         */
        if (Visit::active()) {
            Visit::record();
        }

        /**
         * Record Visitor Detail
         */
        $visitorId = false;
        if (Visitor::active()) {
            $visitorId = Visitor::record($visitorProfile);
        }

        /**
         * Record Search Engine
         */
        if ($visitorId) {
            SearchEngine::record(array('visitor_id' => $visitorId));
        }

        /**
         * Record Pages
         */
        $pageId = false;
        if (Pages::active()) {
            $pageId = Pages::record($visitorProfile);
        }

        /**
         * Record Visitor Relationship
         */
        if ($visitorId && $pageId) {
            Visitor::save_visitors_relationships($pageId, $visitorId);
        }

        /**
         * Record User Online with the visitor request in the same time.
         */
        self::recordOnline($visitorProfile, $exclusion, $pageId);

        return $exclusion;
    }

    /**
     * Record the user online standalone
     *
     * @throws Exception
     */
    public static function recordOnline($visitorProfile = null, $exclusion = null, $pageId = null)
    {
        if (!UserOnline::active()) {
            return;
        }

        if (!$visitorProfile) {
            $visitorProfile = new VisitorProfile();
        }

        /**
         * Check the exclusion
         */
        if (!$exclusion) {
            $exclusion = Exclusion::check($visitorProfile);
        }

        /**
         * Record exclusion if needed & then skip the tracking
         */
        if ($exclusion['exclusion_match'] === true) {
            Exclusion::record($exclusion);

            throw new Exception($exclusion['exclusion_reason'], 200);
        }

        $args = null;
        if ($pageId) {
            $args['page_id'] = $pageId;
        }

        UserOnline::record($visitorProfile, $args);

        return $exclusion;
    }

    /**
     * Record Hits in Login Page
     *
     * @throws Exception
     */
    public static function trackLoginPageCallback()
    {
        if (Helper::is_login_page()) {
            try {
                self::record();
            } catch (Exception $e) {

            }
        }
    }

    /**
     * Server-Side Tracking Callback
     *
     * @throws Exception
     */
    public static function trackServerSideCallback()
    {
        try {
            if (is_admin() or is_preview() or Option::get('use_cache_plugin') or Helper::dntEnabled()) {
                return;
            }

            $consentLevel = Option::get('consent_level_integration', 'disabled');

            if ($consentLevel == 'disabled' || Helper::shouldTrackAnonymously() || !WpConsentApi::isWpConsentApiActive() || !function_exists('wp_has_consent') || wp_has_consent($consentLevel)) {
                self::record();
            }

        } catch (Exception $e) {

        }
    }
}

Hits::instance();