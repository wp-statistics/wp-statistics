<?php

namespace WP_Statistics\Abstracts;

abstract class BaseBlock
{
    /**
     * The block name.
     *
     * @var string
     */
    protected $blockName;

    /**
     * Front-end script handle.
     *
     * @var string
     */
    protected $script = '';

    /**
     * Block version.
     *
     * @var string
     */
    protected $blockVersion;

    /**
     * Registers the block.
     *
     * This is the main method of the class and will be called by `BlockAssetsManager`.
     *
     * @return  void
     */
    public function register()
    {
        $blockPath = untrailingslashit(WP_STATISTICS_DIR . "assets/blocks/{$this->blockName}");

        // Define a base config for all blocks.
        $baseConfig = ['render_callback' => [$this, 'renderCallback']];
        $config     = $this->buildBlockAttributes($baseConfig);

        register_block_type($blockPath, $config);

        // Enqueue script and data
        if (!empty($this->script)) {
            wp_enqueue_script("wp-statistics-{$this->blockName}-block-data");
            wp_localize_script($this->script, "wp-statistics-{$this->blockName}-block-data", $this->buildBlockAjaxData());
        }
    }

    /**
     * Renders the block.
     *
     * @param   array       $attributes
     * @param   string      $content
     * @param   \WP_Block   $block
     *
     * @return  mixed
     */
    abstract public function renderCallback($attributes, $content, $block);

    /**
     * Builds the Ajax data for the block.
     *
     * @return  array
     */
    abstract public function buildBlockAjaxData();

    /**
     * Builds the block attributes.
     *
     * @return  array   Block attributes.
     */
    abstract public function buildBlockAttributes($config);
}
