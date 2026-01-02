<?php

namespace WP_Statistics\Service\EmailReport\Block\Blocks;

use WP_Statistics\Service\EmailReport\Block\AbstractBlock;
use WP_Statistics\Service\EmailReport\Metric\MetricRegistry;

/**
 * Metrics Block
 *
 * Displays key performance indicators (KPIs) grid.
 *
 * @package WP_Statistics\Service\EmailReport\Block\Blocks
 * @since 15.0.0
 */
class MetricsBlock extends AbstractBlock
{
    protected string $type = 'metrics';
    protected string $name = 'Key Metrics';
    protected string $description = 'Display key performance indicators';
    protected string $icon = 'chart-bar';
    protected string $category = 'data';

    /**
     * @inheritDoc
     */
    public function getDefaultSettings(): array
    {
        return [
            'show' => ['visitors', 'views', 'sessions', 'referrals'],
            'columns' => 4,
            'showComparison' => true,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSettingsSchema(): array
    {
        return [
            [
                'name' => 'show',
                'type' => 'multiselect',
                'label' => __('Metrics to Show', 'wp-statistics'),
                'options' => [
                    ['value' => 'visitors', 'label' => __('Visitors', 'wp-statistics')],
                    ['value' => 'views', 'label' => __('Views', 'wp-statistics')],
                    ['value' => 'sessions', 'label' => __('Sessions', 'wp-statistics')],
                    ['value' => 'referrals', 'label' => __('Referrals', 'wp-statistics')],
                    ['value' => 'contents', 'label' => __('Published Content', 'wp-statistics')],
                ],
                'default' => ['visitors', 'views', 'sessions', 'referrals'],
            ],
            [
                'name' => 'columns',
                'type' => 'select',
                'label' => __('Columns', 'wp-statistics'),
                'options' => [
                    ['value' => 2, 'label' => '2'],
                    ['value' => 3, 'label' => '3'],
                    ['value' => 4, 'label' => '4'],
                ],
                'default' => 4,
            ],
            [
                'name' => 'showComparison',
                'type' => 'toggle',
                'label' => __('Show Comparison %', 'wp-statistics'),
                'default' => true,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getData(array $settings, string $period): array
    {
        $settings = wp_parse_args($settings, $this->getDefaultSettings());
        $metricRegistry = new MetricRegistry();
        $metrics = [];

        foreach ($settings['show'] as $metricType) {
            $metric = $metricRegistry->get($metricType);
            if ($metric) {
                $metrics[] = $metric->calculate($period);
            }
        }

        return [
            'metrics' => $metrics,
            'columns' => $settings['columns'],
        ];
    }

    /**
     * @inheritDoc
     */
    public function render(array $settings, array $data, array $globalSettings): string
    {
        $settings = wp_parse_args($settings, $this->getDefaultSettings());

        return $this->renderTemplate('metrics', [
            'settings' => $settings,
            'data' => $data,
            'globalSettings' => $globalSettings,
        ]);
    }
}
