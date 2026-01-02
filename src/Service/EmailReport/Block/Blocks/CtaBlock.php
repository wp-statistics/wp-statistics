<?php

namespace WP_Statistics\Service\EmailReport\Block\Blocks;

use WP_Statistics\Service\EmailReport\Block\AbstractBlock;

/**
 * CTA Block
 *
 * Call-to-action button linking to dashboard.
 *
 * @package WP_Statistics\Service\EmailReport\Block\Blocks
 * @since 15.0.0
 */
class CtaBlock extends AbstractBlock
{
    protected string $type = 'cta';
    protected string $name = 'Dashboard Link';
    protected string $description = 'Button to view full statistics';
    protected string $icon = 'external';
    protected string $category = 'cta';

    /**
     * @inheritDoc
     */
    public function getDefaultSettings(): array
    {
        return [
            'text' => __('View Full Report', 'wp-statistics'),
            'url' => '',
            'alignment' => 'center',
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSettingsSchema(): array
    {
        return [
            [
                'name' => 'text',
                'type' => 'text',
                'label' => __('Button Text', 'wp-statistics'),
                'default' => __('View Full Report', 'wp-statistics'),
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
                'default' => 'center',
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getData(array $settings, string $period): array
    {
        return [
            'dashboardUrl' => admin_url('admin.php?page=wp-statistics#/overview'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function render(array $settings, array $data, array $globalSettings): string
    {
        $settings = wp_parse_args($settings, $this->getDefaultSettings());

        // Use dashboard URL if no custom URL provided
        if (empty($settings['url'])) {
            $settings['url'] = $data['dashboardUrl'];
        }

        return $this->renderTemplate('cta', [
            'settings' => $settings,
            'data' => $data,
            'globalSettings' => $globalSettings,
        ]);
    }
}
