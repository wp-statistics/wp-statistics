<?php

namespace WP_Statistics\Service\EmailReport\Block\Blocks;

use WP_Statistics\Service\EmailReport\Block\AbstractBlock;

/**
 * Divider Block
 *
 * Horizontal divider/separator.
 *
 * @package WP_Statistics\Service\EmailReport\Block\Blocks
 * @since 15.0.0
 */
class DividerBlock extends AbstractBlock
{
    protected string $type = 'divider';
    protected string $name = 'Divider';
    protected string $description = 'Add a horizontal divider';
    protected string $icon = 'minus';
    protected string $category = 'layout';

    /**
     * @inheritDoc
     */
    public function getDefaultSettings(): array
    {
        return [
            'style' => 'solid',
            'color' => '#e0e0e0',
            'spacing' => 'normal',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSettingsSchema(): array
    {
        return [
            [
                'name' => 'style',
                'type' => 'select',
                'label' => __('Style', 'wp-statistics'),
                'options' => [
                    ['value' => 'solid', 'label' => __('Solid', 'wp-statistics')],
                    ['value' => 'dashed', 'label' => __('Dashed', 'wp-statistics')],
                    ['value' => 'dotted', 'label' => __('Dotted', 'wp-statistics')],
                ],
                'default' => 'solid',
            ],
            [
                'name' => 'spacing',
                'type' => 'select',
                'label' => __('Spacing', 'wp-statistics'),
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

        return $this->renderTemplate('divider', [
            'settings' => $settings,
            'data' => $data,
            'globalSettings' => $globalSettings,
        ]);
    }
}
