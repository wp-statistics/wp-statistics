<?php

/**
 * Class WP_Statistics_Rest
 */
class WP_Statistics_Rest {

    //Set Default namespace
    const route = 'wpstatistics/v1';

    //Set Default Statistic Save method
    const func = 'hit';

    //Set Default Session Name
    const session = 'wp_statistics_hit';


	/**
	 * Setup an Wordpress REst Api action.
	 */
	static function init() {

	    /*
	     * add Router Rest Api
	     */
        if( WP_Statistics_Frontend::is_cache_active() ) {
            add_action('rest_api_init', array(self::class, 'route'));
        }

	}


	/*
	 * Add Endpoint Route
	 */
	static function route() {
        register_rest_route( self::route, '/'.self::func, array(
            'methods' => 'POST',
            'callback' => array( self::class, 'hit' ),
        ));
    }


    /*
     * Wp Statistic Hit Save
     */
    static public function hit()
    {
        global $WP_Statistics;


        /*
         * Check Security Referer Only This Domain Access
         */
        $header = getallheaders();

        //check is Referer From This Site Request
        if (array_key_exists("Referer",$header) ===false) {
            return new WP_Error( 'error', 'You have no right to access', array( 'status' => 403 ) );
        }

        //Check Only Request From This Website
        $this_domain = parse_url(get_option('siteurl'))['host'];
        if ( !stristr($header['Referer'], $this_domain) ) {
            return new WP_Error( 'error', 'You have no right to access', array( 'status' => 403 ) );
        }

        //Check Auth Key Request
        if ( !isset($header['X-Ajax-WP-Statistics']) ) {
            return new WP_Error( 'error', 'You have no right to access', array( 'status' => 403 ) );
        }

        //Check all Parameter is exist
        $array = array(
          'base',
          'browser',
          'platform',
          'version',
          'referred',
          'ip',
          'hash_ip',
          'ua',
          'timestamp',
          'current_page_id',
          'page_uri',
          'exclude',
          'exclude_reason',
          'track_all',
        );
        foreach($array as $field) {
            if( !isset($_SESSION[self::session][$field]) ) {
                return new WP_Error( 'error', 'You have no right to access', array( 'status' => 403 ) );
            }
        }

        // If something has gone horribly wrong and $WP_Statistics isn't an object, bail out.
        // This seems to happen sometimes with WP Cron calls.
        if ( ! is_object( $WP_Statistics ) ) {
            return;
        }

        $h = new WP_Statistics_GEO_IP_Hits;

        // Call the online users tracking code.
        if ( $WP_Statistics->get_option( 'useronline' ) ) {
            $h->Check_online();
        }

        // Call the visitor tracking code.
        if ( $WP_Statistics->get_option( 'visitors' ) ) {
            $h->Visitors();
        }

        // Call the visit tracking code.
        if ( $WP_Statistics->get_option( 'visits' ) ) {
            $h->Visits();
        }

        // Call the page tracking code.
        if ( $WP_Statistics->get_option( 'pages' ) ) {
            $h->Pages();
        }

        //Remove Session
        unset($_SESSION[self::session]);
    }

    /*
     * Check is Rest Request
     */
    static public function is_rest()
    {
        $header = getallheaders();
        if( isset($header['X-Ajax-WP-Statistics']) and isset($_SESSION[self::session]) ) {
            return true;
        }
        return false;
    }

    /*
     * Get Params Request
     */
    static public function params($params)
    {
        return $_SESSION[self::session][$params];
    }

}
