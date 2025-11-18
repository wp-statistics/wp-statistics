<?php

namespace WP_STATISTICS;

use WP_Statistics\Components\Assets;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Service\Resources\ResourcesFactory;
use WP_Statistics\Service\Tracking\TrackingFactory;
use WP_Statistics\Service\Integrations\IntegrationHelper;
use WP_Statistics\Service\Tracking\TrackerHelper;

class Frontend
{
    public function __construct()
    {
        # Enable ShortCode in Widget
        add_filter('widget_text', 'do_shortcode');

        # Enqueue scripts & styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'), 11);

        # Print out the WP Statistics HTML comment
        add_action('wp_head', array($this, 'print_out_plugin_html'));

        # Check to show hits in posts/pages
        if (Option::get('show_hits')) {
            add_filter('the_content', array($this, 'show_hits'));
        }
    }

    /**
     * Enqueue Scripts
     */
    public function enqueue_scripts()
    {
        if (Option::get('use_cache_plugin')) {

            /**
             * Get default params
             */
            $params = array_merge([TrackingFactory::hits()->getRestHitsKey() => 1], TrackerHelper::getHitsDefaultParams());
            $params = apply_filters('wp_statistics_js_localized_arguments', $params);

            $requestUrl   = !empty($params['requestUrl']) ? $params['requestUrl'] : get_site_url();
            $hitParams    = !empty($params['hitParams']) ? $params['hitParams'] : [];
            $onlineParams = !empty($params['onlineParams']) ? $params['onlineParams'] : [];

            /**
             * Build the parameters
             */
            $jsArgs = array(
                'requestUrl'          => $requestUrl,
                'ajaxUrl'             => admin_url('admin-ajax.php'),
                'hitParams'           => $hitParams,
                'onlineParams'        => $onlineParams,
                'option'              => [
                    'userOnline'           => Option::get('useronline'),
                    'dntEnabled'           => Option::get('do_not_track'),
                    'bypassAdBlockers'     => Option::get('bypass_ad_blockers', false),
                    'consentIntegration'   => IntegrationHelper::getIntegrationStatus(),
                    'isPreview'            => is_preview(),

                    // legacy params for backward compatibility (with older versions of DataPlus)
                    'trackAnonymously'     => IntegrationHelper::shouldTrackAnonymously(),
                    'isWpConsentApiActive' => IntegrationHelper::isIntegrationActive('wp_consent_api'),
                    'consentLevel'         => Option::get('consent_level_integration', 'disabled'),
                ],
                'resourceUriId'       => ResourcesFactory::getCurrentResourceUri()->getId(),
                'jsCheckTime'         => apply_filters('wp_statistics_js_check_time_interval', 60000),
                'isLegacyEventLoaded' => Assets::isScriptEnqueued('event'), // Check if the legacy event.js script is already loaded
                'customEventAjaxUrl'  => add_query_arg(['action' => 'wp_statistics_custom_event', 'nonce' => wp_create_nonce('wp_statistics_custom_event')], admin_url('admin-ajax.php')),
            );

            if (defined('WP_DEBUG') && WP_DEBUG) {
                $jsArgs['isConsoleVerbose'] = true;
            }


            // Add tracker.js dependencies
            $dependencies = [];
            $integration = IntegrationHelper::getActiveIntegration();
            if ($integration) {
                $dependencies = $integration->getJsHandles();
            }

            Assets::script('tracker', 'js/tracker.js', $dependencies, $jsArgs, true, Option::get('bypass_ad_blockers', false));
        }

        // Load Chart.js library
        if (Helper::isAdminBarShowing()) {
            Assets::script('chart.js', 'js/chartjs/chart.umd.min.js', [], [], true, false, null, '4.4.4', '', true);
            Assets::script('mini-chart', 'js/mini-chart.min.js', [], [], true, false, null, '', '', true);
            Assets::style('front', 'css/frontend.min.css');
        }
    }

    /*
     * Print out the WP Statistics HTML comment
     */
    public function print_out_plugin_html()
    {
        if (apply_filters('wp_statistics_html_comment', true)) {
            echo '<!-- Analytics by WP Statistics - ' . esc_url(WP_STATISTICS_SITE_URL) . ' -->' . "\n";
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

        // Check post type
        $post_type = get_post_type($post_id);

        // Get post hits
        $viewsModel = new ViewsModel();
        $hits       = $viewsModel->countViews([
            'resource_type' => $post_type,
            'post_id'       => $post_id,
            'date'          => 'total',
            'post_type'     => '',
        ]);

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
