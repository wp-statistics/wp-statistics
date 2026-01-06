<?php

namespace WP_Statistics\Service\Blocks;

use WP_Statistics\Service\Shortcode\ShortcodeService;

/**
 * Statistics Block for WP Statistics v15.
 *
 * Server-side rendered block for displaying statistics.
 * Uses ShortcodeService for data retrieval to ensure consistency
 * with the [wpstatistics] shortcode.
 *
 * @since 15.0.0
 */
class StatisticsBlock
{
    /**
     * Stat labels for display.
     *
     * @var array
     */
    private static $labels = [];

    /**
     * Stat icons (Dashicons).
     *
     * @var array
     */
    private static $icons = [];

    /**
     * ShortcodeService instance.
     *
     * @var ShortcodeService|null
     */
    private static $shortcodeService = null;

    /**
     * Initialize labels and icons.
     *
     * @return void
     */
    private static function initLabelsAndIcons()
    {
        if (empty(self::$labels)) {
            self::$labels = [
                'usersonline'    => __('Online Visitors', 'wp-statistics'),
                'visits'         => __('Views', 'wp-statistics'),
                'visitors'       => __('Visitors', 'wp-statistics'),
                'pagevisits'     => __('Page Views', 'wp-statistics'),
                'pagevisitors'   => __('Page Visitors', 'wp-statistics'),
                'searches'       => __('Searches', 'wp-statistics'),
                'referrer'       => __('Referrers', 'wp-statistics'),
                'postcount'      => __('Posts', 'wp-statistics'),
                'pagecount'      => __('Pages', 'wp-statistics'),
                'commentcount'   => __('Comments', 'wp-statistics'),
                'spamcount'      => __('Spam', 'wp-statistics'),
                'usercount'      => __('Users', 'wp-statistics'),
                'postaverage'    => __('Post Average', 'wp-statistics'),
                'commentaverage' => __('Comment Average', 'wp-statistics'),
                'useraverage'    => __('User Average', 'wp-statistics'),
                'lpd'            => __('Last Post Date', 'wp-statistics'),
            ];

            self::$icons = [
                'usersonline'    => 'dashicons-admin-users',
                'visits'         => 'dashicons-visibility',
                'visitors'       => 'dashicons-groups',
                'pagevisits'     => 'dashicons-analytics',
                'pagevisitors'   => 'dashicons-businessman',
                'searches'       => 'dashicons-search',
                'referrer'       => 'dashicons-admin-links',
                'postcount'      => 'dashicons-admin-post',
                'pagecount'      => 'dashicons-admin-page',
                'commentcount'   => 'dashicons-admin-comments',
                'spamcount'      => 'dashicons-warning',
                'usercount'      => 'dashicons-admin-users',
                'postaverage'    => 'dashicons-chart-bar',
                'commentaverage' => 'dashicons-format-chat',
                'useraverage'    => 'dashicons-chart-line',
                'lpd'            => 'dashicons-calendar-alt',
            ];
        }
    }

    /**
     * Get the ShortcodeService instance.
     *
     * @return ShortcodeService
     */
    private static function getShortcodeService()
    {
        if (self::$shortcodeService === null) {
            self::$shortcodeService = new ShortcodeService();
        }

        return self::$shortcodeService;
    }

    /**
     * Render the block.
     *
     * @param array  $attributes Block attributes.
     * @param string $content    Block content.
     * @return string Rendered HTML.
     */
    public static function render($attributes, $content)
    {
        self::initLabelsAndIcons();

        $defaults = [
            'stat'      => 'visitors',
            'time'      => 'today',
            'format'    => 'i18n',
            'showLabel' => true,
            'showIcon'  => true,
            'layout'    => 'card',
            'provider'  => 'all',
        ];

        $attributes = wp_parse_args($attributes, $defaults);

        // Build attributes for ShortcodeService - matches shortcode attribute format
        $atts = [
            'stat'     => $attributes['stat'],
            'time'     => $attributes['time'],
            'provider' => $attributes['provider'],
        ];

        // Include ID if provided (for page-specific stats)
        if (!empty($attributes['id'])) {
            $atts['id'] = (int) $attributes['id'];
        }

        // Get the statistic value using ShortcodeService
        // Uses getValue() which applies the same parsing and filters as shortcode
        $shortcodeService = self::getShortcodeService();
        $value = $shortcodeService->getValue($atts);

        // Format the value
        $formattedValue = self::formatValue($value, $attributes['format']);

        // Get label and icon
        $label = self::$labels[$attributes['stat']] ?? '';
        $icon  = self::$icons[$attributes['stat']] ?? '';

        // Build CSS classes
        $classes = [
            'wp-block-wp-statistics-statistics',
            'wps-statistics-block',
            'wps-statistics-block--' . $attributes['layout'],
        ];

        if (isset($attributes['className'])) {
            $classes[] = $attributes['className'];
        }

        $classString = esc_attr(implode(' ', $classes));

        // Get time period label
        $timePeriod = self::getTimePeriodLabel($attributes['time']);

        // Render based on layout
        return self::renderLayout(
            $attributes['layout'],
            $classString,
            $formattedValue,
            $label,
            $icon,
            $timePeriod,
            $attributes['showLabel'],
            $attributes['showIcon'],
            $attributes['anchor'] ?? ''
        );
    }

    /**
     * Render the block layout.
     *
     * @param string $layout         Layout type.
     * @param string $classes        CSS classes.
     * @param string $value          Formatted value.
     * @param string $label          Stat label.
     * @param string $icon           Icon class.
     * @param string $timePeriod     Time period label.
     * @param bool   $showLabel      Whether to show label.
     * @param bool   $showIcon       Whether to show icon.
     * @param string $anchor         Block anchor.
     * @return string HTML output.
     */
    private static function renderLayout($layout, $classes, $value, $label, $icon, $timePeriod, $showLabel, $showIcon, $anchor)
    {
        $anchorAttr = $anchor ? sprintf(' id="%s"', esc_attr($anchor)) : '';

        switch ($layout) {
            case 'inline':
                return self::renderInlineLayout($classes, $value, $label, $icon, $timePeriod, $showLabel, $showIcon, $anchorAttr);

            case 'minimal':
                return self::renderMinimalLayout($classes, $value, $label, $showLabel, $anchorAttr);

            case 'card':
            default:
                return self::renderCardLayout($classes, $value, $label, $icon, $timePeriod, $showLabel, $showIcon, $anchorAttr);
        }
    }

    /**
     * Render card layout.
     *
     * @return string HTML output.
     */
    private static function renderCardLayout($classes, $value, $label, $icon, $timePeriod, $showLabel, $showIcon, $anchorAttr)
    {
        $html = sprintf('<div class="%s"%s>', $classes, $anchorAttr);

        if ($showIcon && $icon) {
            $html .= sprintf('<span class="wps-statistics-block__icon dashicons %s"></span>', esc_attr($icon));
        }

        $html .= '<div class="wps-statistics-block__content">';
        $html .= sprintf('<span class="wps-statistics-block__value">%s</span>', esc_html($value));

        if ($showLabel && $label) {
            $html .= sprintf('<span class="wps-statistics-block__label">%s</span>', esc_html($label));
            $html .= sprintf('<span class="wps-statistics-block__period">%s</span>', esc_html($timePeriod));
        }

        $html .= '</div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Render inline layout.
     *
     * @return string HTML output.
     */
    private static function renderInlineLayout($classes, $value, $label, $icon, $timePeriod, $showLabel, $showIcon, $anchorAttr)
    {
        $html = sprintf('<span class="%s"%s>', $classes, $anchorAttr);

        if ($showIcon && $icon) {
            $html .= sprintf('<span class="wps-statistics-block__icon dashicons %s"></span> ', esc_attr($icon));
        }

        if ($showLabel && $label) {
            $html .= sprintf('<span class="wps-statistics-block__label">%s:</span> ', esc_html($label));
        }

        $html .= sprintf('<span class="wps-statistics-block__value">%s</span>', esc_html($value));

        if ($showLabel && $timePeriod) {
            $html .= sprintf(' <span class="wps-statistics-block__period">(%s)</span>', esc_html($timePeriod));
        }

        $html .= '</span>';

        return $html;
    }

    /**
     * Render minimal layout.
     *
     * @return string HTML output.
     */
    private static function renderMinimalLayout($classes, $value, $label, $showLabel, $anchorAttr)
    {
        $html = sprintf('<span class="%s"%s>', $classes, $anchorAttr);
        $html .= sprintf('<span class="wps-statistics-block__value">%s</span>', esc_html($value));

        if ($showLabel && $label) {
            $html .= sprintf(' <span class="wps-statistics-block__label">%s</span>', esc_html($label));
        }

        $html .= '</span>';

        return $html;
    }

    /**
     * Format the value based on format setting.
     *
     * @param int|string $value  Value to format.
     * @param string     $format Format type.
     * @return string Formatted value.
     */
    private static function formatValue($value, $format)
    {
        if (!is_numeric($value)) {
            return (string) $value;
        }

        // Determine decimal places for floating point values
        $decimals = self::getDecimalPlaces($value);

        switch ($format) {
            case 'i18n':
                return number_format_i18n($value, $decimals);

            case 'english':
                return number_format((float) $value, $decimals);

            case 'abbreviated':
                return self::formatAbbreviated($value);

            case 'none':
            default:
                return (string) $value;
        }
    }

    /**
     * Get the number of decimal places for a value.
     *
     * @param int|float $value The value to check.
     * @return int Number of decimal places (0 for integers, 2 for floats).
     */
    private static function getDecimalPlaces($value)
    {
        // If it's an integer or has no decimal part, use 0 decimals
        if ((float) $value == (int) $value) {
            return 0;
        }

        // For floating point values, use 2 decimal places
        return 2;
    }

    /**
     * Format number in abbreviated notation.
     *
     * @param int|float $number Number to format.
     * @return string Formatted number.
     */
    private static function formatAbbreviated($number)
    {
        $abbreviations = [
            'B' => 1000000000,
            'M' => 1000000,
            'K' => 1000,
        ];

        foreach ($abbreviations as $symbol => $threshold) {
            if ($number >= $threshold) {
                $formatted = $number / $threshold;
                return round($formatted, 1) . $symbol;
            }
        }

        return (string) $number;
    }

    /**
     * Get time period label.
     *
     * @param string $time Time period.
     * @return string Label.
     */
    private static function getTimePeriodLabel($time)
    {
        $labels = [
            'today'     => __('Today', 'wp-statistics'),
            'yesterday' => __('Yesterday', 'wp-statistics'),
            'week'      => __('This Week', 'wp-statistics'),
            'month'     => __('This Month', 'wp-statistics'),
            'year'      => __('This Year', 'wp-statistics'),
            'total'     => __('All Time', 'wp-statistics'),
        ];

        return $labels[$time] ?? $time;
    }
}
