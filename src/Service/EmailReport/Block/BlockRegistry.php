<?php

namespace WP_Statistics\Service\EmailReport\Block;

use WP_Statistics\Service\EmailReport\Block\Blocks\HeaderBlock;
use WP_Statistics\Service\EmailReport\Block\Blocks\MetricsBlock;
use WP_Statistics\Service\EmailReport\Block\Blocks\TopPagesBlock;
use WP_Statistics\Service\EmailReport\Block\Blocks\TopReferrersBlock;
use WP_Statistics\Service\EmailReport\Block\Blocks\TopAuthorsBlock;
use WP_Statistics\Service\EmailReport\Block\Blocks\TopCategoriesBlock;
use WP_Statistics\Service\EmailReport\Block\Blocks\TextBlock;
use WP_Statistics\Service\EmailReport\Block\Blocks\DividerBlock;
use WP_Statistics\Service\EmailReport\Block\Blocks\CtaBlock;
use WP_Statistics\Service\EmailReport\Block\Blocks\PromoBlock;

/**
 * Block Registry
 *
 * Manages registration and retrieval of email content blocks.
 *
 * @package WP_Statistics\Service\EmailReport\Block
 * @since 15.0.0
 */
class BlockRegistry
{
    /**
     * Registered blocks
     *
     * @var array<string, BlockInterface>
     */
    private array $blocks = [];

    /**
     * Block categories
     *
     * @var array
     */
    private array $categories = [
        'layout' => [
            'name' => 'Layout',
            'icon' => 'layout',
        ],
        'data' => [
            'name' => 'Data & Analytics',
            'icon' => 'chart-bar',
        ],
        'content' => [
            'name' => 'Content',
            'icon' => 'text',
        ],
        'cta' => [
            'name' => 'Call to Action',
            'icon' => 'megaphone',
        ],
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->registerDefaultBlocks();
    }

    /**
     * Register default blocks
     *
     * @return void
     */
    private function registerDefaultBlocks(): void
    {
        // Layout blocks
        $this->register(new HeaderBlock());
        $this->register(new DividerBlock());

        // Data blocks
        $this->register(new MetricsBlock());
        $this->register(new TopPagesBlock());
        $this->register(new TopReferrersBlock());
        $this->register(new TopAuthorsBlock());
        $this->register(new TopCategoriesBlock());

        // Content blocks
        $this->register(new TextBlock());

        // CTA blocks
        $this->register(new CtaBlock());
        $this->register(new PromoBlock());

        /**
         * Allow add-ons to register custom blocks
         *
         * @param BlockRegistry $registry Block registry instance
         */
        do_action('wp_statistics_email_report_register_blocks', $this);
    }

    /**
     * Register a block
     *
     * @param BlockInterface $block Block instance
     * @return void
     */
    public function register(BlockInterface $block): void
    {
        $this->blocks[$block->getType()] = $block;
    }

    /**
     * Unregister a block
     *
     * @param string $type Block type
     * @return void
     */
    public function unregister(string $type): void
    {
        unset($this->blocks[$type]);
    }

    /**
     * Check if a block is registered
     *
     * @param string $type Block type
     * @return bool
     */
    public function has(string $type): bool
    {
        return isset($this->blocks[$type]);
    }

    /**
     * Get a block by type
     *
     * @param string $type Block type
     * @return BlockInterface|null
     */
    public function get(string $type): ?BlockInterface
    {
        return $this->blocks[$type] ?? null;
    }

    /**
     * Get all registered blocks
     *
     * @return array<string, BlockInterface>
     */
    public function getAll(): array
    {
        return $this->blocks;
    }

    /**
     * Get available blocks for the email builder UI
     *
     * @return array
     */
    public function getAvailableBlocks(): array
    {
        $available = [];

        foreach ($this->blocks as $type => $block) {
            $available[] = $block->toArray();
        }

        return $available;
    }

    /**
     * Get blocks grouped by category
     *
     * @return array
     */
    public function getBlocksByCategory(): array
    {
        $grouped = [];

        foreach ($this->categories as $categoryId => $category) {
            $grouped[$categoryId] = [
                'name' => $category['name'],
                'icon' => $category['icon'],
                'blocks' => [],
            ];
        }

        foreach ($this->blocks as $block) {
            $category = $block->getCategory();
            if (isset($grouped[$category])) {
                $grouped[$category]['blocks'][] = $block->toArray();
            }
        }

        // Remove empty categories
        return array_filter($grouped, function ($category) {
            return !empty($category['blocks']);
        });
    }

    /**
     * Get categories
     *
     * @return array
     */
    public function getCategories(): array
    {
        return $this->categories;
    }
}
