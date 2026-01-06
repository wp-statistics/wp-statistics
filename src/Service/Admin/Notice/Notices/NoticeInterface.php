<?php

namespace WP_Statistics\Service\Admin\Notice\Notices;

use WP_Statistics\Service\Admin\Notice\NoticeItem;

/**
 * Notice Generator Interface.
 *
 * Implement this interface to create notice generators that
 * dynamically produce notices based on conditions.
 *
 * @since 15.0.0
 */
interface NoticeInterface
{
    /**
     * Get notices to display.
     *
     * Returns an array of NoticeItem objects representing
     * the notices this generator wants to show.
     *
     * @return NoticeItem[]
     */
    public function getNotices(): array;

    /**
     * Check if this notice generator should run.
     *
     * Used for conditional notice generation. Return false
     * to skip this generator entirely (e.g., based on user
     * capabilities, page context, or feature flags).
     *
     * @return bool
     */
    public function shouldRun(): bool;
}
