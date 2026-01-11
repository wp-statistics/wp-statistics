<?php

namespace WP_Statistics\Service\Admin\CommandPalette;

/**
 * Handles WordPress Command Palette integration.
 *
 * Enqueues the necessary JavaScript and provides command data to register
 * WP Statistics navigation commands with WordPress's native Command Palette.
 *
 * @since 15.0.0
 */
class CommandPaletteHandler
{
    /**
     * @var CommandPaletteDataProvider
     */
    private $dataProvider;

    /**
     * Initialize the handler and register hooks.
     */
    public function __construct()
    {
        $this->dataProvider = new CommandPaletteDataProvider();

        add_action('admin_enqueue_scripts', [$this, 'enqueueAssets']);
    }

    /**
     * Enqueue Command Palette assets.
     *
     * @return void
     */
    public function enqueueAssets(): void
    {
        // Only load if WordPress Command Palette is available (WP 6.3+)
        if (!$this->isCommandPaletteAvailable()) {
            return;
        }

        wp_enqueue_script(
            'wp-statistics-command-palette',
            WP_STATISTICS_URL . 'public/legacy/js/command-palette.min.js',
            ['wp-commands', 'wp-data', 'wp-element'],
            WP_STATISTICS_VERSION,
            true
        );

        wp_localize_script(
            'wp-statistics-command-palette',
            'wpStatisticsCommands',
            [
                'commands' => $this->dataProvider->getCommands(),
            ]
        );
    }

    /**
     * Check if WordPress Command Palette is available.
     *
     * The Command Palette was introduced in WordPress 6.3.
     *
     * @return bool True if Command Palette is available
     */
    private function isCommandPaletteAvailable(): bool
    {
        global $wp_version;

        return version_compare($wp_version, '6.3', '>=');
    }
}
