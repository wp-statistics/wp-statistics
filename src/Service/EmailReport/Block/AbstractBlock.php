<?php

namespace WP_Statistics\Service\EmailReport\Block;

/**
 * Abstract Email Block
 *
 * Base class for all email content blocks.
 * Provides common functionality and default implementations.
 *
 * @package WP_Statistics\Service\EmailReport\Block
 * @since 15.0.0
 */
abstract class AbstractBlock implements BlockInterface
{
    /**
     * Block type identifier
     *
     * @var string
     */
    protected string $type = '';

    /**
     * Block display name
     *
     * @var string
     */
    protected string $name = '';

    /**
     * Block description
     *
     * @var string
     */
    protected string $description = '';

    /**
     * Block icon (dashicon name)
     *
     * @var string
     */
    protected string $icon = 'admin-generic';

    /**
     * Block category
     *
     * @var string
     */
    protected string $category = 'content';

    /**
     * Get block type identifier
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get block display name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get block description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get block icon
     *
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * Get block category
     *
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Get default settings for the block
     *
     * @return array
     */
    public function getDefaultSettings(): array
    {
        return [];
    }

    /**
     * Get settings schema for the block
     *
     * @return array
     */
    public function getSettingsSchema(): array
    {
        return [];
    }

    /**
     * Get data for the block (default: empty)
     *
     * @param array $settings Block settings
     * @param string $period Report period
     * @return array
     */
    public function getData(array $settings, string $period): array
    {
        return [];
    }

    /**
     * Get date range for a period
     *
     * @param string $period Period type (daily, weekly, biweekly, monthly)
     * @return array ['start' => DateTime, 'end' => DateTime, 'previous_start' => DateTime, 'previous_end' => DateTime]
     */
    protected function getDateRange(string $period): array
    {
        $now = new \DateTime('now', wp_timezone());
        $end = clone $now;
        $start = clone $now;
        $previousEnd = clone $now;
        $previousStart = clone $now;

        switch ($period) {
            case 'daily':
                $start->modify('-1 day');
                $previousEnd->modify('-1 day');
                $previousStart->modify('-2 days');
                break;

            case 'weekly':
                $start->modify('-7 days');
                $previousEnd->modify('-7 days');
                $previousStart->modify('-14 days');
                break;

            case 'biweekly':
                $start->modify('-14 days');
                $previousEnd->modify('-14 days');
                $previousStart->modify('-28 days');
                break;

            case 'monthly':
                $start->modify('-30 days');
                $previousEnd->modify('-30 days');
                $previousStart->modify('-60 days');
                break;

            default:
                $start->modify('-7 days');
                $previousEnd->modify('-7 days');
                $previousStart->modify('-14 days');
        }

        return [
            'start' => $start,
            'end' => $end,
            'previous_start' => $previousStart,
            'previous_end' => $previousEnd,
            'start_date' => $start->format('Y-m-d'),
            'end_date' => $end->format('Y-m-d'),
            'previous_start_date' => $previousStart->format('Y-m-d'),
            'previous_end_date' => $previousEnd->format('Y-m-d'),
        ];
    }

    /**
     * Calculate percentage change
     *
     * @param int|float $current Current value
     * @param int|float $previous Previous value
     * @return array ['value' => float, 'direction' => string]
     */
    protected function calculateChange($current, $previous): array
    {
        if ($previous == 0) {
            if ($current > 0) {
                return ['value' => 100, 'direction' => 'up'];
            }
            return ['value' => 0, 'direction' => 'neutral'];
        }

        $change = (($current - $previous) / $previous) * 100;

        return [
            'value' => abs(round($change, 1)),
            'direction' => $change > 0 ? 'up' : ($change < 0 ? 'down' : 'neutral'),
        ];
    }

    /**
     * Format number for display
     *
     * @param int|float $number Number to format
     * @return string
     */
    protected function formatNumber($number): string
    {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        }

        if ($number >= 1000) {
            return round($number / 1000, 1) . 'K';
        }

        return number_format_i18n($number);
    }

    /**
     * Get block template path
     *
     * @param string $templateName Template name
     * @return string
     */
    protected function getTemplatePath(string $templateName): string
    {
        return WP_STATISTICS_DIR . 'src/Service/EmailReport/Templates/Emails/blocks/' . $templateName . '.php';
    }

    /**
     * Render template with data
     *
     * @param string $templateName Template name
     * @param array $data Data to pass to template
     * @return string
     */
    protected function renderTemplate(string $templateName, array $data): string
    {
        $templatePath = $this->getTemplatePath($templateName);

        if (!file_exists($templatePath)) {
            return '';
        }

        extract($data, EXTR_SKIP);

        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    /**
     * Convert block configuration to JSON for React
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->getType(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'icon' => $this->getIcon(),
            'category' => $this->getCategory(),
            'defaultSettings' => $this->getDefaultSettings(),
            'settingsSchema' => $this->getSettingsSchema(),
        ];
    }
}
