<?php

namespace WP_Statistics\Service\Admin\CommandPalette;

use WP_Statistics\Service\Admin\DashboardBootstrap\Providers\LayoutDataProvider;

/**
 * Provides command data for WordPress Command Palette integration.
 *
 * Transforms sidebar navigation configuration into commands that can be
 * registered with WordPress's native Command Palette (Cmd+K).
 *
 * @since 15.0.0
 */
class CommandPaletteDataProvider
{
    /**
     * Icon mapping from Lucide icon names to WordPress Dashicon names.
     *
     * @var array<string, string>
     */
    private const ICON_MAP = [
        'LayoutDashboard'   => 'dashboard',
        'User'              => 'admin-users',
        'File'              => 'admin-page',
        'FileChartColumn'   => 'chart-bar',
        'RefreshCw'         => 'update',
        'Earth'             => 'admin-site',
        'MonitorSmartphone' => 'smartphone',
        'Settings'          => 'admin-generic',
    ];

    /**
     * Get commands data formatted for WordPress Command Palette.
     *
     * @return array Array of command objects with name, label, icon, and url
     */
    public function getCommands(): array
    {
        $layoutProvider = new LayoutDataProvider();
        $layoutData     = $layoutProvider->getData();
        $sidebarConfig  = $layoutData['sidebar'] ?? [];

        $commands = [];
        $baseUrl  = admin_url('admin.php?page=wp-statistics#');

        foreach ($sidebarConfig as $key => $config) {
            $icon = $this->mapIcon($config['icon'] ?? '');

            if (isset($config['subPages']) && is_array($config['subPages'])) {
                // Add each sub-page as a command
                foreach ($config['subPages'] as $subKey => $subPage) {
                    $commands[] = [
                        'name'  => 'wp-statistics/' . sanitize_title($subPage['slug']),
                        'label' => sprintf(
                            /* translators: %s: Page name */
                            __('WP Statistics: %s', 'wp-statistics'),
                            $subPage['label']
                        ),
                        'icon'  => $icon,
                        'url'   => $baseUrl . '/' . $subPage['slug'],
                    ];
                }
            } else {
                // Single-level menu item
                $commands[] = [
                    'name'  => 'wp-statistics/' . sanitize_title($config['slug']),
                    'label' => sprintf(
                        /* translators: %s: Page name */
                        __('WP Statistics: %s', 'wp-statistics'),
                        $config['label']
                    ),
                    'icon'  => $icon,
                    'url'   => $baseUrl . '/' . $config['slug'],
                ];
            }
        }

        return $commands;
    }

    /**
     * Map Lucide icon name to WordPress Dashicon name.
     *
     * @param string $lucideIcon The Lucide icon name
     * @return string The corresponding Dashicon name
     */
    private function mapIcon(string $lucideIcon): string
    {
        return self::ICON_MAP[$lucideIcon] ?? 'analytics';
    }
}
