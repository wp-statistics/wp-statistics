<?php
namespace WP_Statistics\Service\Assets\Handlers;

use WP_Statistics\Abstracts\BaseAssets;
use WP_Statistics\Components\Assets;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Bootstrap;
use WP_Statistics\Service\Consent\ConsentProviderInterface;
use WP_Statistics\Service\Consent\TrackingLevel;
use WP_Statistics\Service\Resources\ResourcesFactory;
use WP_Statistics\Utils\Signature;
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
        $this->setAssetDir('public/entries/tracker');
        $this->setPrefix('wp-statistics-frontend');

        add_filter('widget_text', 'do_shortcode');
        add_action('wp_enqueue_scripts', [$this, 'scripts'], 11);
        add_action('login_enqueue_scripts', [$this, 'scripts'], 11);
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
        $activeProvider  = Bootstrap::get('consent')->getActiveProvider();
        $trackingManager = Bootstrap::get('tracking');
        $trackerConfig   = $trackingManager->getTrackerConfig();

        $resource     = ResourcesFactory::getCurrentResource();
        $resourceType = $resource->getType();
        $resourceId   = $resource->getId();
        $userId       = get_current_user_id();

        $isAjax = $trackingManager->getTrackingMethod() === 'ajax';

        $jsArgs = array(
            'baseUrl'             => $trackerConfig['baseUrl'],
            'hitEndpoint'         => $trackerConfig['hitEndpoint'],
            'batchEndpoint'       => $trackerConfig['batchEndpoint'],
            'signature'           => Signature::generate([$resourceType, (int) $resourceId, (int) $userId]),
            'resource'            => [
                'resourceUriId' => ResourcesFactory::getCurrentResourceUri()->getId(),
                'resourceType'  => $resourceType,
                'resourceId'    => (int) $resourceId,
            ],
            'userId'              => (int) $userId,
            'option'              => $this->buildOptionArgs($activeProvider),
            'isLegacyEventLoaded' => Assets::isScriptEnqueued('event'),
            'customEventAjaxUrl'  => add_query_arg(['action' => 'wp_statistics_custom_event', 'nonce' => wp_create_nonce('wp_statistics_custom_event')], admin_url('admin-ajax.php')),
        );

        if (defined('WP_DEBUG') && WP_DEBUG) {
            $jsArgs['isConsoleVerbose'] = true;
        }

        $dependencies = $activeProvider->getJsDependencies();

        Assets::script('tracker', 'tracker.min.js', $dependencies, $jsArgs, true, $isAjax, null, '', '', true);

        $inlineScript = $activeProvider->getInlineScript();
        if ($inlineScript !== '') {
            wp_add_inline_script('wp-statistics-tracker', $inlineScript, 'before');
        }
    }

    /**
     * Build option arguments for the tracker script.
     *
     * @return array
     */
    private function buildOptionArgs(ConsentProviderInterface $activeProvider): array
    {
        return [
            'userOnline'        => Option::getValue('useronline'),
            'anonymousTracking' => (bool) Option::getValue('anonymous_tracking', false),
            'eventTracking'     => (bool) Option::getValue('event_tracking', false),
            'trackingLevel'     => TrackingLevel::all(),
            'consent'           => $activeProvider->getJsConfig(),
            'isPreview'         => is_preview(),
        ];
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

        // Get post hits using AnalyticsQueryHandler
        $queryHandler = new AnalyticsQueryHandler();
        $result = $queryHandler->handle([
            'sources'   => ['views'],
            'filters'   => [
                'post_type'   => $post_type,
                'resource_id' => $post_id,
            ],
            'date_from' => '1970-01-01',
            'date_to'   => date('Y-m-d'),
        ]);

        $hits = $result['data']['totals']['views'] ?? 0;

        $hits_html = '<p>' . sprintf(__('Views: %s', 'wp-statistics'), $hits) . '</p>';

        $position = Option::getValue('display_hits_position');

        if ($position == 'before_content') {
            return $hits_html . $content;
        } elseif ($position == 'after_content') {
            return $content . $hits_html;
        }

        return $content;
    }
}
