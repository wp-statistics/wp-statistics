<?php

namespace WP_Statistics\Blocks;

use WP_Statistics\Components\Assets;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

class BlockAssetsManager
{
    /**
     * List of blocks to register.
     *
     * Note that you have to include the namespace too.
     *
     * @var array
     */
    private $blocks = [
        // \WP_STATISTICS\Blocks\TestBlock::class,
    ];

    public function __construct()
    {
        add_action('init', [$this, 'registerBlocks']);
        add_filter('block_categories_all', [$this, 'registerPluginBlockCategory'], 10, 2);
        add_action('enqueue_block_editor_assets', [$this, 'addEditorSidebar']);
    }

    /**
     * Registers WP Statistics blocks.
     *
     * @return  void
     *
     * @hooked	action: `init` - 10
     */
    public function registerBlocks()
    {
        if (!function_exists('register_block_type')) {
            \WP_Statistics::log(__('The "register_block_type" function is not supported in this version of WordPress.', 'wp-statistics'), 'error');
            return;
        }

        foreach ($this->blocks as $item) {
            if (class_exists($item)) {
                $block = new $item();
                $block->register();
            } else {
                Notice::addNotice(sprintf(
                    // translators: %s: Class name.
                    __('WP Statistics Error: Block encountered an error, class %s could not be loaded.', 'wp-statistics'),
                    '<b>' . esc_html($item) . '</b>'
                ), 'error');
            }
        }
    }

    /**
     * Registers WP Statistics block category.
     *
     * @param   array[]                     $block_categories   Array of categories for block types.
     * @param   \WP_Block_Editor_Context    $editor_context     The current block editor context.
     *
     * @return  array[]
     *
     * @hooked	filter: `block_categories_all` - 10
     */
    public function registerPluginBlockCategory($block_categories, $editor_context)
    {
        if (empty($this->blocks)) {
            return $block_categories;
        }

        return array_merge($block_categories, [[
            'slug'  => 'wp-statistics-blocks',
            'title' => __('WP Statistics', 'wp-statistics'),
        ]]);
    }

    /**
     * Adds post statistics to the editor sidebar.
     *
     * @return	void
     *
     * @hooked	action: `enqueue_block_editor_assets` - 10
     */
    public function addEditorSidebar()
    {
        global $pagenow;
        if ($pagenow === 'post-new.php') {
            return;
        }

        $args = [];

        Assets::script('editor-sidebar', 'blocks/index.js', ['wp-plugins', 'wp-editor'], $args);
    }
}
