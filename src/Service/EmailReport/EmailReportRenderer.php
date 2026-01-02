<?php

namespace WP_Statistics\Service\EmailReport;

use WP_Statistics\Service\EmailReport\Block\BlockRegistry;
use WP_Statistics\Service\EmailReport\Metric\MetricRegistry;

/**
 * Email Report Renderer
 *
 * Renders email HTML from template configuration.
 *
 * @package WP_Statistics\Service\EmailReport
 * @since 15.0.0
 */
class EmailReportRenderer
{
    /**
     * Block registry
     *
     * @var BlockRegistry
     */
    private BlockRegistry $blockRegistry;

    /**
     * Metric registry
     *
     * @var MetricRegistry
     */
    private MetricRegistry $metricRegistry;

    /**
     * Constructor
     *
     * @param BlockRegistry $blockRegistry Block registry instance
     * @param MetricRegistry $metricRegistry Metric registry instance
     */
    public function __construct(BlockRegistry $blockRegistry, MetricRegistry $metricRegistry)
    {
        $this->blockRegistry = $blockRegistry;
        $this->metricRegistry = $metricRegistry;
    }

    /**
     * Render email HTML
     *
     * @param array $template Template configuration
     * @param string $period Report period
     * @return string
     */
    public function render(array $template, string $period): string
    {
        $globalSettings = $template['globalSettings'] ?? [];
        $blocks = $template['blocks'] ?? [];

        $renderedBlocks = [];
        foreach ($blocks as $blockConfig) {
            $blockType = $blockConfig['type'] ?? '';
            $blockSettings = $blockConfig['settings'] ?? [];

            $block = $this->blockRegistry->get($blockType);
            if (!$block) {
                continue;
            }

            // Get block data
            $data = $block->getData($blockSettings, $period);

            // Render block
            $renderedBlocks[] = $block->render($blockSettings, $data, $globalSettings);
        }

        // Wrap in email layout
        return $this->wrapInLayout(implode("\n", $renderedBlocks), $globalSettings);
    }

    /**
     * Wrap content in email layout
     *
     * @param string $content Block content
     * @param array $globalSettings Global settings
     * @return string
     */
    private function wrapInLayout(string $content, array $globalSettings): string
    {
        $primaryColor = $globalSettings['primaryColor'] ?? '#404BF2';

        ob_start();
        include WP_STATISTICS_DIR . 'src/Service/EmailReport/Templates/Emails/layout.php';
        return ob_get_clean();
    }
}
