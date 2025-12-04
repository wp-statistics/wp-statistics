<?php
namespace WP_Statistics\Service\Assets\Handlers;

use WP_Statistics\Abstracts\BaseAssets;
use WP_Statistics\Components\Assets;
use WP_Statistics\Globals\Option;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Service\Integrations\IntegrationHelper;
use WP_Statistics\Service\Resources\ResourcesFactory;
use WP_Statistics\Service\Tracking\TrackerHelper;
use WP_Statistics\Service\Tracking\TrackingFactory;
use WP_Statistics\Utils\Route;

/**
 * Frontend Assets Service
 * 
 * Handles WordPress frontend assets (CSS/JS) in WP Statistics plugin.
 * Manages loading and enqueuing of frontend-specific styles and scripts.
 * 
 * @package WP_STATISTICS\Service\Assets
 * @since   15.0.0
 */
class FrontendHandler extends BaseAssets
{
    /**
     * Initialize the assets manager
     *
     * @return void
     */
    public function __construct()
    {
        $this->setContext('frontend');
        $this->setAssetDir('public/frontend');
        $this->setPrefix('wp-statistics-frontend');

        add_filter('widget_text', 'do_shortcode');
        add_action('wp_enqueue_scripts', [$this, 'scripts'], 11);
        add_action('wp_head', [$this, 'printHtmlComment']);

        if (Option::getValue('show_hits')) {
            add_filter('the_content', [$this, 'showHits']);
        }
    }

    /**
     * Register and enqueue frontend styles
     *
     * @return void
     */
    public function styles(){}

    /**
     * Register and enqueue frontend scripts
     *
     * @param string $hook Current admin page hook (optional)
     * @return void
     */
    public function scripts($hook = '')
    {
        if (Option::getValue('use_cache_plugin')) {
            $params = array_merge([TrackingFactory::hits()->getRestHitsKey() => 1], TrackerHelper::getHitsDefaultParams());
            $params = apply_filters('wp_statistics_js_localized_arguments', $params);

            $requestUrl = !empty($params['requestUrl']) ? $params['requestUrl'] : get_site_url();
            $hitParams  = !empty($params['hitParams']) ? $params['hitParams'] : [];

             $jsArgs = array(
                'requestUrl'          => $requestUrl,
                'ajaxUrl'             => admin_url('admin-ajax.php'),
                'hitParams'           => $hitParams,
                'option'              => [
                    'userOnline'           => Option::getValue('useronline'),
                    'dntEnabled'           => Option::getValue('do_not_track'),
                    'bypassAdBlockers'     => Option::getValue('bypass_ad_blockers', false),
                    'consentIntegration'   => IntegrationHelper::getIntegrationStatus(),
                    'isPreview'            => is_preview(),

                    // legacy params for backward compatibility (with older versions of DataPlus)
                    'trackAnonymously'     => IntegrationHelper::shouldTrackAnonymously(),
                    'isWpConsentApiActive' => IntegrationHelper::isIntegrationActive('wp_consent_api'),
                    'consentLevel'         => Option::getValue('consent_level_integration', 'disabled'),
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

            Assets::script('tracker', 'js/tracker.min.js', $dependencies, $jsArgs, true, Option::getValue('bypass_ad_blockers', false), null, '', '', true);
        }

        if (Route::isAdminBarShowing()) {
            Assets::script('chart.js', 'js/chartjs/chart.umd.min.js', [], [], true, false, null, '4.4.4', '', true);
            Assets::script('mini-chart', 'js/mini-chart.min.js', [], [], true, false, null, '', '', true);
            Assets::style('front', 'css/frontend.min.css');
        }
    }

    /**
     * Print HTML comment for WP Statistics in the page source
     *
     * @return void
     */
    public function printHtmlComment()
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
    public function showHits($content)
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
        if (Option::getValue('display_hits_position') == 'before_content') {
            return $hits_html . $content;
        } elseif (Option::getValue('display_hits_position') == 'after_content') {
            return $content . $hits_html;
        } else {
            return $content;
        }
    }
}