<?php

namespace WP_Statistics\Service\EmailReport\Block\Blocks;

use WP_Statistics\Service\EmailReport\Block\AbstractBlock;

/**
 * Top Referrers Block
 *
 * Displays top traffic referrers.
 *
 * @package WP_Statistics\Service\EmailReport\Block\Blocks
 * @since 15.0.0
 */
class TopReferrersBlock extends AbstractBlock
{
    protected string $type = 'top-referrers';
    protected string $name = 'Top Referrers';
    protected string $description = 'Show top traffic sources';
    protected string $icon = 'admin-links';
    protected string $category = 'data';

    /**
     * @inheritDoc
     */
    public function getDefaultSettings(): array
    {
        return [
            'limit' => 5,
            'showVisitors' => true,
        ];
    }

    /**
     * @inheritDoc
     */
    public function getSettingsSchema(): array
    {
        return [
            [
                'name' => 'limit',
                'type' => 'select',
                'label' => __('Number of Referrers', 'wp-statistics'),
                'options' => [
                    ['value' => 3, 'label' => '3'],
                    ['value' => 5, 'label' => '5'],
                    ['value' => 10, 'label' => '10'],
                ],
                'default' => 5,
            ],
            [
                'name' => 'showVisitors',
                'type' => 'toggle',
                'label' => __('Show Visitors Count', 'wp-statistics'),
                'default' => true,
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function getData(array $settings, string $period): array
    {
        global $wpdb;

        $settings = wp_parse_args($settings, $this->getDefaultSettings());
        $dateRange = $this->getDateRange($period);

        // v15 tables
        $sessionsTable = $wpdb->prefix . 'statistics_sessions';
        $referrersTable = $wpdb->prefix . 'statistics_referrers';

        $referrers = $wpdb->get_results($wpdb->prepare(
            "SELECT
                ref.referrer_url,
                COUNT(DISTINCT s.visitor_id) AS visitors
            FROM {$sessionsTable} s
            INNER JOIN {$referrersTable} ref ON s.referrer_id = ref.id
            WHERE DATE(s.started_at) BETWEEN %s AND %s
            GROUP BY ref.id
            ORDER BY visitors DESC
            LIMIT %d",
            $dateRange['start_date'],
            $dateRange['end_date'],
            $settings['limit']
        ));

        $formattedReferrers = [];
        foreach ($referrers as $ref) {
            $domain = wp_parse_url($ref->referrer_url, PHP_URL_HOST) ?: $ref->referrer_url;
            $domain = preg_replace('/^www\./', '', $domain);

            $formattedReferrers[] = [
                'domain' => $domain,
                'url' => $ref->referrer_url,
                'visitors' => intval($ref->visitors),
                'visitorsFormatted' => $this->formatNumber($ref->visitors),
                'favicon' => 'https://www.google.com/s2/favicons?domain=' . urlencode($domain),
            ];
        }

        return [
            'referrers' => $formattedReferrers,
            'hasData' => !empty($formattedReferrers),
        ];
    }

    /**
     * @inheritDoc
     */
    public function render(array $settings, array $data, array $globalSettings): string
    {
        $settings = wp_parse_args($settings, $this->getDefaultSettings());

        return $this->renderTemplate('top-referrers', [
            'settings' => $settings,
            'data' => $data,
            'globalSettings' => $globalSettings,
        ]);
    }
}
