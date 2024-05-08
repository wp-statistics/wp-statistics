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

        # Register and enqueue check online users scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_styles'));

        # Print out the WP Statistics HTML comment
        add_action('wp_head', array($this, 'print_out_plugin_html'));

        # Check to show hits in posts/pages
        if (Option::get('show_hits')) {
            add_filter('the_content', array($this, 'show_hits'));
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
        wp_enqueue_script('wp-statistics-tracker', WP_STATISTICS_URL . 'assets/js/tracker.js', [], WP_STATISTICS_VERSION, ['in_footer' => true]);

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
        $hitRequestUrl        = add_query_arg($params, get_rest_url(null, RestAPI::$namespace . '/' . Api\v2\Hit::$endpoint));
        $keepOnlineRequestUrl = add_query_arg($params, get_rest_url(null, RestAPI::$namespace . '/' . Api\v2\CheckUserOnline::$endpoint));

        $jsArgs = array(
            'hitRequestUrl'        => $hitRequestUrl,
            'keepOnlineRequestUrl' => $keepOnlineRequestUrl,
            'option'               => [
                'dntEnabled'         => Option::get('do_not_track'),
                'cacheCompatibility' => Option::get('use_cache_plugin')
            ],
        );

        wp_localize_script('wp-statistics-tracker', 'WP_Statistics_Tracker_Object', $jsArgs);
    }

    /**
     * Enqueue Styles
     */
    public function enqueue_styles()
    {

        // Load Admin Bar Css
        if (AdminBar::show_admin_bar() and is_admin_bar_showing()) {
            wp_enqueue_style('wp-statistics', WP_STATISTICS_URL . 'assets/css/frontend.min.css', true, WP_STATISTICS_VERSION);
        }
    }

    /*
     * Print out the WP Statistics HTML comment
     */
    public function print_out_plugin_html()
    {
        if (apply_filters('wp_statistics_html_comment', true)) {
            echo '<!-- Analytics by WP Statistics v' . WP_STATISTICS_VERSION . ' - ' . WP_STATISTICS_SITE . ' -->' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
        $hits_html = '<p>' . sprintf(__('Views: %s', 'wp-statistics'), $hits) . '</p>';

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
