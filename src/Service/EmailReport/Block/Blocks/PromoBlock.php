<?php

namespace WP_Statistics\Service\EmailReport\Block\Blocks;

use WP_Statistics\Service\EmailReport\Block\AbstractBlock;

/**
 * Promo Block
 *
 * Promotional content for add-ons.
 *
 * @package WP_Statistics\Service\EmailReport\Block\Blocks
 * @since 15.0.0
 */
class PromoBlock extends AbstractBlock
{
    protected string $type = 'promo';
    protected string $name = 'Promo Banner';
    protected string $description = 'Promotional message for add-ons';
    protected string $icon = 'megaphone';
    protected string $category = 'cta';

    /**
     * @inheritDoc
     */
    public function getDefaultSettings(): array
    {
        return [
            'promoType' => 'advanced-reporting',
            'showPromo' => true,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSettingsSchema(): array
    {
        return [
            [
                'name' => 'promoType',
                'type' => 'select',
                'label' => __('Promotion Type', 'wp-statistics'),
                'options' => [
                    ['value' => 'advanced-reporting', 'label' => __('Advanced Reporting', 'wp-statistics')],
                    ['value' => 'data-plus', 'label' => __('Data Plus', 'wp-statistics')],
                    ['value' => 'realtime', 'label' => __('Realtime Stats', 'wp-statistics')],
                ],
                'default' => 'advanced-reporting',
            ],
            [
                'name' => 'showPromo',
                'type' => 'toggle',
                'label' => __('Show Promotion', 'wp-statistics'),
                'default' => true,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getData(array $settings, string $period): array
    {
        $promos = [
            'advanced-reporting' => [
                'title' => __('Want More Detailed Reports?', 'wp-statistics'),
                'description' => __('Get advanced email reports with custom metrics, scheduled delivery, and more insights with Advanced Reporting add-on.', 'wp-statistics'),
                'url' => 'https://wp-statistics.com/add-ons/advanced-reporting/?utm_source=email-report&utm_medium=email&utm_campaign=promo',
                'buttonText' => __('Learn More', 'wp-statistics'),
            ],
            'data-plus' => [
                'title' => __('Unlock More Data Insights', 'wp-statistics'),
                'description' => __('Get detailed visitor data, geographic insights, and behavioral analytics with Data Plus.', 'wp-statistics'),
                'url' => 'https://wp-statistics.com/add-ons/data-plus/?utm_source=email-report&utm_medium=email&utm_campaign=promo',
                'buttonText' => __('Learn More', 'wp-statistics'),
            ],
            'realtime' => [
                'title' => __('See Visitors in Real-Time', 'wp-statistics'),
                'description' => __('Watch your visitors navigate your site in real-time with live analytics.', 'wp-statistics'),
                'url' => 'https://wp-statistics.com/add-ons/realtime-stats/?utm_source=email-report&utm_medium=email&utm_campaign=promo',
                'buttonText' => __('Learn More', 'wp-statistics'),
            ],
        ];

        $promoType = $settings['promoType'] ?? 'advanced-reporting';

        return $promos[$promoType] ?? $promos['advanced-reporting'];
    }

    /**
     * @inheritDoc
     */
    public function render(array $settings, array $data, array $globalSettings): string
    {
        $settings = wp_parse_args($settings, $this->getDefaultSettings());

        if (!$settings['showPromo']) {
            return '';
        }

        return $this->renderTemplate('promo', [
            'settings' => $settings,
            'data' => $data,
            'globalSettings' => $globalSettings,
        ]);
    }
}
