<?php

namespace WP_Statistics\Service\EmailReport\Block\Blocks;

use WP_Statistics\Service\EmailReport\Block\AbstractBlock;

/**
 * Header Block
 *
 * Email header with logo, site title, and date range.
 *
 * @package WP_Statistics\Service\EmailReport\Block\Blocks
 * @since 15.0.0
 */
class HeaderBlock extends AbstractBlock
{
    protected string $type = 'header';
    protected string $name = 'Header';
    protected string $description = 'Email header with logo and date range';
    protected string $icon = 'heading';
    protected string $category = 'layout';

    /**
     * @inheritDoc
     */
    public function getDefaultSettings(): array
    {
        return [
            'showLogo' => true,
            'showDateRange' => true,
            'showSiteTitle' => true,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSettingsSchema(): array
    {
        return [
            [
                'name' => 'showLogo',
                'type' => 'toggle',
                'label' => __('Show Logo', 'wp-statistics'),
                'default' => true,
            ],
            [
                'name' => 'showDateRange',
                'type' => 'toggle',
                'label' => __('Show Date Range', 'wp-statistics'),
                'default' => true,
            ],
            [
                'name' => 'showSiteTitle',
                'type' => 'toggle',
                'label' => __('Show Site Title', 'wp-statistics'),
                'default' => true,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getData(array $settings, string $period): array
    {
        $dateRange = $this->getDateRange($period);

        return [
            'siteName' => get_bloginfo('name'),
            'siteUrl' => get_bloginfo('url'),
            'logoUrl' => $this->getLogoUrl(),
            'dateRangeText' => $this->formatDateRange($dateRange['start'], $dateRange['end']),
            'periodLabel' => $this->getPeriodLabel($period),
        ];
    }

    /**
     * Get site logo URL
     *
     * @return string
     */
    private function getLogoUrl(): string
    {
        $customLogo = get_theme_mod('custom_logo');
        if ($customLogo) {
            $logoUrl = wp_get_attachment_image_url($customLogo, 'medium');
            if ($logoUrl) {
                return $logoUrl;
            }
        }

        return '';
    }

    /**
     * Format date range for display
     *
     * @param \DateTime $start Start date
     * @param \DateTime $end End date
     * @return string
     */
    private function formatDateRange(\DateTime $start, \DateTime $end): string
    {
        $format = get_option('date_format');
        return $start->format($format) . ' - ' . $end->format($format);
    }

    /**
     * Get period label
     *
     * @param string $period Period type
     * @return string
     */
    private function getPeriodLabel(string $period): string
    {
        $labels = [
            'daily' => __('Daily Report', 'wp-statistics'),
            'weekly' => __('Weekly Report', 'wp-statistics'),
            'biweekly' => __('Bi-Weekly Report', 'wp-statistics'),
            'monthly' => __('Monthly Report', 'wp-statistics'),
        ];

        return $labels[$period] ?? $labels['weekly'];
    }

    /**
     * @inheritDoc
     */
    public function render(array $settings, array $data, array $globalSettings): string
    {
        $settings = wp_parse_args($settings, $this->getDefaultSettings());

        return $this->renderTemplate('header', [
            'settings' => $settings,
            'data' => $data,
            'globalSettings' => $globalSettings,
        ]);
    }
}
