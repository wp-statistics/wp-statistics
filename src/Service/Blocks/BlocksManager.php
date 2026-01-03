<?php

namespace WP_Statistics\Service\Blocks;

/**
 * Blocks Manager for WP Statistics v15.
 *
 * Handles registration and management of Gutenberg blocks.
 *
 * @since 15.0.0
 */
class BlocksManager
{
    /**
     * Block namespace.
     */
    private const NAMESPACE = 'wp-statistics';

    /**
     * Block instances.
     *
     * @var array
     */
    private $blocks = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('init', [$this, 'registerBlocks']);
        add_action('enqueue_block_editor_assets', [$this, 'enqueueEditorAssets']);
    }

    /**
     * Register all blocks.
     *
     * @return void
     */
    public function registerBlocks()
    {
        // Statistics Block
        $this->registerStatisticsBlock();
    }

    /**
     * Register the Statistics block.
     *
     * @return void
     */
    private function registerStatisticsBlock()
    {
        register_block_type(self::NAMESPACE . '/statistics', [
            'api_version'     => 3,
            'title'           => __('WP Statistics', 'wp-statistics'),
            'description'     => __('Display statistics from WP Statistics.', 'wp-statistics'),
            'category'        => 'widgets',
            'icon'            => 'chart-pie',
            'keywords'        => [
                __('statistics', 'wp-statistics'),
                __('analytics', 'wp-statistics'),
                __('visitors', 'wp-statistics'),
                __('views', 'wp-statistics'),
            ],
            'supports'        => [
                'html'       => false,
                'align'      => ['wide', 'full'],
                'className'  => true,
                'anchor'     => true,
            ],
            'attributes'      => $this->getStatisticsAttributes(),
            'render_callback' => [StatisticsBlock::class, 'render'],
        ]);

        $this->blocks['statistics'] = new StatisticsBlock();
    }

    /**
     * Get Statistics block attributes.
     *
     * @return array Block attributes schema.
     */
    private function getStatisticsAttributes()
    {
        return [
            'stat' => [
                'type'    => 'string',
                'default' => 'visitors',
                'enum'    => [
                    'usersonline',
                    'visits',
                    'visitors',
                    'pagevisits',
                    'pagevisitors',
                    'searches',
                    'referrer',
                    'postcount',
                    'pagecount',
                    'commentcount',
                    'spamcount',
                    'usercount',
                ],
            ],
            'time' => [
                'type'    => 'string',
                'default' => 'today',
                'enum'    => [
                    'today',
                    'yesterday',
                    'week',
                    'month',
                    'year',
                    'total',
                ],
            ],
            'format' => [
                'type'    => 'string',
                'default' => 'i18n',
                'enum'    => [
                    'none',
                    'i18n',
                    'english',
                    'abbreviated',
                ],
            ],
            'showLabel' => [
                'type'    => 'boolean',
                'default' => true,
            ],
            'showIcon' => [
                'type'    => 'boolean',
                'default' => true,
            ],
            'layout' => [
                'type'    => 'string',
                'default' => 'card',
                'enum'    => [
                    'card',
                    'inline',
                    'minimal',
                ],
            ],
        ];
    }

    /**
     * Enqueue block editor assets.
     *
     * @return void
     */
    public function enqueueEditorAssets()
    {
        $assetFile = WP_STATISTICS_DIR . 'assets/js/blocks/statistics/index.asset.php';

        if (file_exists($assetFile)) {
            $asset = require $assetFile;
        } else {
            $asset = [
                'dependencies' => ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
                'version'      => WP_STATISTICS_VERSION,
            ];
        }

        // Enqueue editor script
        wp_enqueue_script(
            'wp-statistics-blocks-editor',
            WP_STATISTICS_URL . 'assets/js/blocks/statistics/index.js',
            $asset['dependencies'],
            $asset['version'],
            true
        );

        // Enqueue editor styles
        wp_enqueue_style(
            'wp-statistics-blocks-editor',
            WP_STATISTICS_URL . 'assets/css/blocks/statistics-editor.css',
            [],
            WP_STATISTICS_VERSION
        );

        // Pass data to the editor
        wp_localize_script('wp-statistics-blocks-editor', 'wpStatisticsBlockData', [
            'pluginUrl' => WP_STATISTICS_URL,
            'ajaxUrl'   => admin_url('admin-ajax.php'),
            'nonce'     => wp_create_nonce('wp_rest'),
            'stats'     => $this->getAvailableStats(),
        ]);
    }

    /**
     * Get available statistics for the block.
     *
     * @return array Available statistics.
     */
    private function getAvailableStats()
    {
        return [
            [
                'value' => 'usersonline',
                'label' => __('Online Visitors', 'wp-statistics'),
                'icon'  => 'user',
            ],
            [
                'value' => 'visits',
                'label' => __('Total Views', 'wp-statistics'),
                'icon'  => 'visibility',
            ],
            [
                'value' => 'visitors',
                'label' => __('Total Visitors', 'wp-statistics'),
                'icon'  => 'groups',
            ],
            [
                'value' => 'pagevisits',
                'label' => __('Page Views', 'wp-statistics'),
                'icon'  => 'analytics',
            ],
            [
                'value' => 'pagevisitors',
                'label' => __('Page Visitors', 'wp-statistics'),
                'icon'  => 'person',
            ],
            [
                'value' => 'searches',
                'label' => __('Search Queries', 'wp-statistics'),
                'icon'  => 'search',
            ],
            [
                'value' => 'referrer',
                'label' => __('Referrers', 'wp-statistics'),
                'icon'  => 'link',
            ],
            [
                'value' => 'postcount',
                'label' => __('Post Count', 'wp-statistics'),
                'icon'  => 'admin-post',
            ],
            [
                'value' => 'pagecount',
                'label' => __('Page Count', 'wp-statistics'),
                'icon'  => 'admin-page',
            ],
            [
                'value' => 'commentcount',
                'label' => __('Comment Count', 'wp-statistics'),
                'icon'  => 'admin-comments',
            ],
            [
                'value' => 'usercount',
                'label' => __('User Count', 'wp-statistics'),
                'icon'  => 'admin-users',
            ],
        ];
    }

    /**
     * Get a block instance.
     *
     * @param string $name Block name.
     * @return object|null Block instance.
     */
    public function getBlock($name)
    {
        return $this->blocks[$name] ?? null;
    }
}
