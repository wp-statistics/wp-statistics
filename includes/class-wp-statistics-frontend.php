<?php

namespace WP_STATISTICS;

class Frontend
{
    public function __construct()
    {

        # Enable ShortCode in Widget
        add_filter('widget_text', 'do_shortcode');

        # Add the honey trap code in the footer.
        add_action('wp_footer', array($this, 'add_honeypot'));

        # Enqueue scripts & styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        # Add inline Rest Request
        add_action('wp_head', array($this, 'add_inline_rest_js'));

        # Add Html Comment in head
        if (!Option::get('use_cache_plugin')) {
            add_action('wp_head', array($this, 'html_comment'));
        }

        # Check to show hits in posts/pages
        if (Option::get('show_hits')) {
            add_filter('the_content', array($this, 'show_hits'));
        }
    }

    /*
     * Create HTML Comment to support Wappalyzer
     */
    public function html_comment()
    {
        if (apply_filters('wp_statistics_html_comment', true)) {
            echo '<!-- Analytics by WP Statistics v' . WP_STATISTICS_VERSION . ' - ' . WP_STATISTICS_SITE . ' -->' . "\n";
        }
    }

    /**
     * Footer Action
     */
    public function add_honeypot()
    {
        if (Option::get('use_honeypot') && Option::get('honeypot_postid') > 0) {
            $post_url = get_permalink(Option::get('honeypot_postid'));
            echo '<a href="' . esc_html($post_url) . '" style="display: none;">&nbsp;</a>';
        }
    }

    /**
     * Enqueue Scripts
     */
    public function enqueue_scripts()
    {

        // Load Admin Bar Css
        if (AdminBar::show_admin_bar() and is_admin_bar_showing()) {
            wp_enqueue_style('wp-statistics', WP_STATISTICS_URL . 'assets/css/frontend.min.css', true, WP_STATISTICS_VERSION);
        }
    }

    /*
     * Inline Js for client-side request
     */
    public function add_inline_rest_js()
    {
        if (Option::get('use_cache_plugin')) {

            /**
             * Print out the WP Statistics HTML comment
             */
            $this->html_comment();

            $params = array(
                Hits::$rest_hits_key => 'yes',
            );

            /**
             * Merge parameters
             */
            $params = array_merge($params, Helper::getHitsDefaultParams());

            /**
             * Build request URL
             */
            $apiUrl     = RestAPI::$namespace . '/' . Api\v2\Hit::$endpoint;
            $requestUrl = add_query_arg($params, get_rest_url(null, $apiUrl));

            /**
             * Print Script
             */
            Admin_Template::get_template(array('tracker-js'), array(
                'requestUrl' => $requestUrl,
                'dntEnabled' => Option::get('do_not_track'),
            ));
        }
    }

    /**
     * Show Hits in After WordPress the_content
     *
     * @param $content
     * @return string
     */
    public function show_hits($content)
    {

        // Get post ID
        $post_id = get_the_ID();

        // Check post ID
        if (!$post_id) {
            return $content;
        }

        // Get post hits
        $hits      = wp_statistics_pages('total', "", $post_id);
        $hits_html = '<p>' . sprintf(__('Hits: %s', 'wp-statistics'), $hits) . '</p>';

        // Check hits position
        if (Option::get('display_hits_position') == 'before_content') {
            return $hits_html . $content;
        } elseif (Option::get('display_hits_position') == 'after_content') {
            return $content . $hits_html;
        } else {
            return $content;
        }
    }
}

new Frontend;
