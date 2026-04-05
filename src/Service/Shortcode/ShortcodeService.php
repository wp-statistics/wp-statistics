<?php

namespace WP_Statistics\Service\Shortcode;

use WP_Statistics\Utils\Page;
use WP_Statistics\Traits\TransientCacheTrait;
use WP_Statistics\Service\Shortcode\Handlers\AnalyticsStatHandler;
use WP_Statistics\Service\Shortcode\Handlers\WordPressStatHandler;
use WP_Statistics\Service\Shortcode\Formatters\NumberFormatter;

/**
 * Main shortcode service.
 *
 * Registers the [wpstatistics] shortcode and orchestrates
 * rendering by delegating to the stat registry.
 *
 * @since 15.0.0
 */
class ShortcodeService
{
    use TransientCacheTrait;

    /**
     * @var StatRegistry
     */
    private $registry;

    /**
     * @var NumberFormatter
     */
    private $formatter;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->registry  = new StatRegistry();
        $this->formatter = new NumberFormatter();

        $this->registerDefaultHandlers();

        add_shortcode('wpstatistics', [$this, 'render']);
    }

    /**
     * Register default stat handlers.
     *
     * @return void
     */
    private function registerDefaultHandlers(): void
    {
        $this->registry->register(new AnalyticsStatHandler());
        $this->registry->register(new WordPressStatHandler());
    }

    /**
     * Get the stat registry.
     *
     * Allows external code to register custom handlers.
     *
     * @return StatRegistry
     */
    public function getRegistry(): StatRegistry
    {
        return $this->registry;
    }

    /**
     * Render the shortcode.
     *
     * @param array|string $atts Shortcode attributes.
     * @return string Rendered output.
     */
    public function render($atts): string
    {
        $atts = $this->parseAttributes($atts);

        if (empty($atts['stat'])) {
            return '';
        }

        /**
         * Filter shortcode attributes before processing.
         *
         * @since 15.0.0
         * @param array $atts Shortcode attributes.
         */
        $atts = apply_filters('wp_statistics_shortcode_attributes', $atts);

        $value = $this->registry->resolve($atts['stat'], $atts);

        /**
         * Filter the shortcode result.
         *
         * @since 15.0.0
         * @param mixed  $value Stat value.
         * @param string $stat  Stat type.
         * @param array  $atts  Shortcode attributes.
         */
        $value = apply_filters('wp_statistics_shortcode_result', $value, $atts['stat'], $atts);

        return $this->formatOutput($value, $atts);
    }

    /**
     * Parse shortcode attributes.
     *
     * @param array|string $atts Raw attributes.
     * @return array Normalized attributes.
     */
    private function parseAttributes($atts): array
    {
        if (!is_array($atts)) {
            return ['stat' => ''];
        }

        $atts = wp_parse_args($atts, [
            'stat'     => '',
            'time'     => null,
            'provider' => 'all',
            'format'   => '',
            'id'       => null,
            'type'     => '',
        ]);

        // Normalize time value
        $atts['time'] = $this->normalizeTime($atts['time']);

        // Auto-detect page context
        if (empty($atts['id'])) {
            $atts['id']   = get_the_ID();
            $currentPage  = Page::getType();
            $atts['type'] = $currentPage['type'] ?? '';
        } else {
            $atts['type'] = $this->getResourceType($atts['id']);
        }

        return $atts;
    }

    /**
     * Format the output value.
     *
     * @param mixed $value Raw value.
     * @param array $atts  Shortcode attributes.
     * @return string Formatted output.
     */
    private function formatOutput($value, array $atts): string
    {
        // Don't format date stats
        if ($atts['stat'] === 'lpd') {
            return (string) $value;
        }

        return $this->formatter->format($value, $atts['format'] ?? '');
    }

    /**
     * Normalize time parameter.
     *
     * @param mixed $time Time value.
     * @return mixed Normalized time.
     */
    private function normalizeTime($time)
    {
        if (empty($time)) {
            return null;
        }

        $mapping = [
            'week'  => '7days',
            'month' => '30days',
            'year'  => '12months',
        ];

        if (isset($mapping[$time])) {
            return $mapping[$time];
        }

        if (is_numeric($time)) {
            return [
                'from' => date('Y-m-d', strtotime("-{$time} days")),
                'to'   => date('Y-m-d'),
            ];
        }

        return $time;
    }

    /**
     * Get resource type with caching.
     *
     * @param int|null $resourceId Resource ID.
     * @return string Resource type.
     */
    private function getResourceType($resourceId): string
    {
        if (empty($resourceId)) {
            return '';
        }

        $cacheKey = $this->getCacheKey('shortcode_resource_type_' . $resourceId);
        $type     = $this->getCachedResult($cacheKey);

        if (!$type) {
            $type = get_post_type($resourceId) ?: '';
            $this->setCachedResult($cacheKey, $type);
        }

        return $type;
    }

    /**
     * Get raw stat value with proper attribute parsing.
     *
     * Used by Gutenberg blocks to get the same value as shortcodes.
     *
     * @param array $atts Attributes (stat, time, id, type, provider).
     * @return mixed Raw stat value.
     */
    public function getValue(array $atts)
    {
        $atts = $this->parseAttributes($atts);

        if (empty($atts['stat'])) {
            return '';
        }

        $atts = apply_filters('wp_statistics_shortcode_attributes', $atts);

        $value = $this->registry->resolve($atts['stat'], $atts);

        return apply_filters('wp_statistics_shortcode_result', $value, $atts['stat'], $atts);
    }
}
