<?php

namespace WP_Statistics\Service\EmailReport\Block\Blocks;

use WP_Statistics\Service\EmailReport\Block\AbstractBlock;

/**
 * Text Block
 *
 * Custom text/HTML content block.
 *
 * @package WP_Statistics\Service\EmailReport\Block\Blocks
 * @since 15.0.0
 */
class TextBlock extends AbstractBlock
{
    protected string $type = 'text';
    protected string $name = 'Custom Text';
    protected string $description = 'Add custom text content';
    protected string $icon = 'editor-paragraph';
    protected string $category = 'content';

    /**
     * @inheritDoc
     */
    public function getDefaultSettings(): array
    {
        return [
            'content' => '',
            'alignment' => 'left',
            'fontSize' => 'normal',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSettingsSchema(): array
    {
        return [
            [
                'name' => 'content',
                'type' => 'textarea',
                'label' => __('Content', 'wp-statistics'),
                'placeholder' => __('Enter your text here...', 'wp-statistics'),
                'default' => '',
            ],
            [
                'name' => 'alignment',
                'type' => 'select',
                'label' => __('Alignment', 'wp-statistics'),
                'options' => [
                    ['value' => 'left', 'label' => __('Left', 'wp-statistics')],
                    ['value' => 'center', 'label' => __('Center', 'wp-statistics')],
                    ['value' => 'right', 'label' => __('Right', 'wp-statistics')],
                ],
                'default' => 'left',
            ],
            [
                'name' => 'fontSize',
                'type' => 'select',
                'label' => __('Font Size', 'wp-statistics'),
                'options' => [
                    ['value' => 'small', 'label' => __('Small', 'wp-statistics')],
                    ['value' => 'normal', 'label' => __('Normal', 'wp-statistics')],
                    ['value' => 'large', 'label' => __('Large', 'wp-statistics')],
                ],
                'default' => 'normal',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getData(array $settings, string $period): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function render(array $settings, array $data, array $globalSettings): string
    {
        $settings = wp_parse_args($settings, $this->getDefaultSettings());

        return $this->renderTemplate('text', [
            'settings' => $settings,
            'data' => $data,
            'globalSettings' => $globalSettings,
        ]);
    }
}
