<?php

namespace WP_Statistics\Service\EmailReport\Block;

/**
 * Email Block Interface
 *
 * Defines the contract for email content blocks.
 * Each block represents a section of the email template.
 *
 * @package WP_Statistics\Service\EmailReport\Block
 * @since 15.0.0
 */
interface BlockInterface
{
    /**
     * Get block type identifier
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Get block display name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get block description
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Get block icon (dashicon name or SVG)
     *
     * @return string
     */
    public function getIcon(): string;

    /**
     * Get block category
     *
     * @return string
     */
    public function getCategory(): string;

    /**
     * Get default settings for the block
     *
     * @return array
     */
    public function getDefaultSettings(): array;

    /**
     * Get settings schema for the block (for React form)
     *
     * @return array
     */
    public function getSettingsSchema(): array;

    /**
     * Render the block HTML
     *
     * @param array $settings Block settings
     * @param array $data Computed data for the block
     * @param array $globalSettings Global template settings
     * @return string
     */
    public function render(array $settings, array $data, array $globalSettings): string;

    /**
     * Get data for the block
     *
     * @param array $settings Block settings
     * @param string $period Report period
     * @return array
     */
    public function getData(array $settings, string $period): array;
}
