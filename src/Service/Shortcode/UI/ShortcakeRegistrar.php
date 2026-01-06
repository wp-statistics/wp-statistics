<?php

namespace WP_Statistics\Service\Shortcode\UI;

/**
 * Registers Shortcake UI for the wpstatistics shortcode.
 *
 * Provides a visual editor interface for users to configure
 * the shortcode without writing code.
 *
 * @since 15.0.0
 */
class ShortcakeRegistrar
{
    /**
     * Register the Shortcake UI.
     *
     * @return void
     */
    public function register(): void
    {
        if (!function_exists('shortcode_ui_register_for_shortcode')) {
            return;
        }

        shortcode_ui_register_for_shortcode('wpstatistics', [
            'label'         => 'WP Statistics',
            'listItemImage' => $this->getLogo(),
            'attrs'         => $this->getAttributes(),
        ]);
    }

    /**
     * Get the logo HTML.
     *
     * @return string
     */
    private function getLogo(): string
    {
        return sprintf(
            '<img alt="logo" src="%s" width="128" height="128">',
            WP_STATISTICS_URL . 'public/images/logo-250.png'
        );
    }

    /**
     * Get all attribute definitions.
     *
     * @return array
     */
    private function getAttributes(): array
    {
        return [
            $this->statAttribute(),
            $this->timeAttribute(),
            $this->providerAttribute(),
            $this->formatAttribute(),
            $this->idAttribute(),
        ];
    }

    /**
     * Stat type attribute.
     *
     * @return array
     */
    private function statAttribute(): array
    {
        return [
            'label'       => __('Statistic', 'wp-statistics'),
            'attr'        => 'stat',
            'type'        => 'select',
            'description' => __('Select the statistic to display.', 'wp-statistics'),
            'value'       => 'usersonline',
            'options'     => [
                'usersonline'    => __('Online Visitors', 'wp-statistics'),
                'visits'         => __('Views', 'wp-statistics'),
                'visitors'       => __('Visitors', 'wp-statistics'),
                'pagevisits'     => __('Page Views', 'wp-statistics'),
                'pagevisitors'   => __('Page Visitors', 'wp-statistics'),
                'searches'       => __('Searches', 'wp-statistics'),
                'referrer'       => __('Referrer', 'wp-statistics'),
                'postcount'      => __('Post Count', 'wp-statistics'),
                'pagecount'      => __('Page Count', 'wp-statistics'),
                'commentcount'   => __('Comment Count', 'wp-statistics'),
                'spamcount'      => __('Spam Count', 'wp-statistics'),
                'usercount'      => __('User Count', 'wp-statistics'),
                'postaverage'    => __('Post Average', 'wp-statistics'),
                'commentaverage' => __('Comment Average', 'wp-statistics'),
                'useraverage'    => __('User Average', 'wp-statistics'),
                'lpd'            => __('Last Post Date', 'wp-statistics'),
            ],
        ];
    }

    /**
     * Time frame attribute.
     *
     * @return array
     */
    private function timeAttribute(): array
    {
        return [
            'label'       => __('Time Frame', 'wp-statistics'),
            'attr'        => 'time',
            'type'        => 'text',
            'description' => __('Options: today, yesterday, week, month, year, total', 'wp-statistics'),
            'meta'        => ['placeholder' => 'total'],
        ];
    }

    /**
     * Search provider attribute.
     *
     * @return array
     */
    private function providerAttribute(): array
    {
        return [
            'label'       => __('Search Provider', 'wp-statistics'),
            'attr'        => 'provider',
            'type'        => 'select',
            'description' => __('For search statistics only.', 'wp-statistics'),
            'value'       => 'all',
            'options'     => [
                'all'        => __('All Providers', 'wp-statistics'),
                'google'     => 'Google',
                'bing'       => 'Bing',
                'yahoo'      => 'Yahoo',
                'duckduckgo' => 'DuckDuckGo',
            ],
        ];
    }

    /**
     * Number format attribute.
     *
     * @return array
     */
    private function formatAttribute(): array
    {
        return [
            'label'       => __('Number Format', 'wp-statistics'),
            'attr'        => 'format',
            'type'        => 'select',
            'description' => __('How to display numbers.', 'wp-statistics'),
            'value'       => 'none',
            'options'     => [
                'none'        => __('None', 'wp-statistics'),
                'english'     => __('English (1,234)', 'wp-statistics'),
                'i18n'        => __('Localized', 'wp-statistics'),
                'abbreviated' => __('Abbreviated (1.2K)', 'wp-statistics'),
            ],
        ];
    }

    /**
     * Post/Page ID attribute.
     *
     * @return array
     */
    private function idAttribute(): array
    {
        return [
            'label'       => __('Post/Page ID', 'wp-statistics'),
            'attr'        => 'id',
            'type'        => 'number',
            'description' => __('For page-specific statistics.', 'wp-statistics'),
        ];
    }
}
