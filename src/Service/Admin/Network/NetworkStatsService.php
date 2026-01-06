<?php

namespace WP_Statistics\Service\Admin\Network;

use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Database\DatabaseSchema;

/**
 * Network Statistics Service.
 *
 * Aggregates analytics data across all sites in a WordPress Multisite network.
 * Uses the existing AnalyticsQuery system per site with proper context switching.
 *
 * IMPORTANT: This service uses switch_to_blog() to query each site's tables.
 * After each switch, DatabaseSchema::clearCache() must be called because
 * DatabaseSchema caches the table prefix statically.
 *
 * @since 15.0.0
 */
class NetworkStatsService
{
    /**
     * Get aggregated network statistics.
     *
     * Returns total visitors, views, and sessions across all sites,
     * plus per-site breakdown.
     *
     * @param string $dateFrom Start date (Y-m-d format).
     * @param string $dateTo   End date (Y-m-d format).
     * @return array Network statistics data.
     */
    public function getNetworkStats($dateFrom, $dateTo)
    {
        if (!is_multisite() || !is_super_admin()) {
            return [
                'success' => false,
                'error'   => __('Network statistics require multisite and super admin access.', 'wp-statistics'),
            ];
        }

        $sites = get_sites([
            'number'   => 100,
            'orderby'  => 'id',
            'order'    => 'ASC',
            'archived' => 0,
            'deleted'  => 0,
        ]);

        $networkTotals = [
            'visitors' => 0,
            'views'    => 0,
            'sessions' => 0,
        ];

        $sitesData = [];

        foreach ($sites as $site) {
            $blogId = $site->blog_id;

            // Switch to site context
            switch_to_blog($blogId);

            // Clear DatabaseSchema cache - it caches the table prefix statically
            DatabaseSchema::clearCache();

            try {
                $siteStats = $this->getSiteStats($dateFrom, $dateTo);

                $sitesData[] = [
                    'blog_id'  => $blogId,
                    'name'     => get_bloginfo('name'),
                    'url'      => get_home_url(),
                    'admin_url' => get_admin_url($blogId, 'admin.php?page=wp-statistics'),
                    'visitors' => $siteStats['visitors'],
                    'views'    => $siteStats['views'],
                    'sessions' => $siteStats['sessions'],
                ];

                // Aggregate totals
                $networkTotals['visitors'] += $siteStats['visitors'];
                $networkTotals['views']    += $siteStats['views'];
                $networkTotals['sessions'] += $siteStats['sessions'];
            } catch (\Exception $e) {
                // Log error but continue with other sites
                $sitesData[] = [
                    'blog_id'  => $blogId,
                    'name'     => get_bloginfo('name'),
                    'url'      => get_home_url(),
                    'admin_url' => get_admin_url($blogId, 'admin.php?page=wp-statistics'),
                    'visitors' => 0,
                    'views'    => 0,
                    'sessions' => 0,
                    'error'    => $e->getMessage(),
                ];
            }

            // Restore to original site context
            restore_current_blog();

            // Clear cache again after restoring
            DatabaseSchema::clearCache();
        }

        return [
            'success' => true,
            'totals'  => $networkTotals,
            'sites'   => $sitesData,
            'period'  => [
                'from' => $dateFrom,
                'to'   => $dateTo,
            ],
        ];
    }

    /**
     * Get statistics for the current site context.
     *
     * Uses AnalyticsQueryHandler to fetch visitors, views, and sessions.
     *
     * @param string $dateFrom Start date.
     * @param string $dateTo   End date.
     * @return array Site statistics.
     */
    private function getSiteStats($dateFrom, $dateTo)
    {
        $handler = new AnalyticsQueryHandler(false); // Disable cache for cross-site queries

        // Query visitors
        $visitorsResult = $handler->handle([
            'sources'     => ['visitors'],
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
            'format'      => 'flat',
            'show_totals' => true,
        ]);

        // Query views
        $viewsResult = $handler->handle([
            'sources'     => ['views'],
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
            'format'      => 'flat',
            'show_totals' => true,
        ]);

        // Query sessions
        $sessionsResult = $handler->handle([
            'sources'     => ['sessions'],
            'date_from'   => $dateFrom,
            'date_to'     => $dateTo,
            'format'      => 'flat',
            'show_totals' => true,
        ]);

        return [
            'visitors' => $visitorsResult['totals']['visitors'] ?? 0,
            'views'    => $viewsResult['totals']['views'] ?? 0,
            'sessions' => $sessionsResult['totals']['sessions'] ?? 0,
        ];
    }

    /**
     * Get list of network sites with basic info.
     *
     * Returns site name, URL, and admin dashboard link for each site.
     *
     * @return array List of sites.
     */
    public function getNetworkSites()
    {
        if (!is_multisite() || !is_super_admin()) {
            return [];
        }

        $sites = get_sites([
            'number'   => 100,
            'orderby'  => 'id',
            'order'    => 'ASC',
            'archived' => 0,
            'deleted'  => 0,
        ]);

        $sitesData = [];

        foreach ($sites as $site) {
            $blogId   = $site->blog_id;
            $siteName = get_blog_option($blogId, 'blogname');

            $sitesData[] = [
                'blog_id'   => $blogId,
                'name'      => $siteName,
                'url'       => get_home_url($blogId),
                'admin_url' => get_admin_url($blogId, 'admin.php?page=wp-statistics'),
            ];
        }

        return $sitesData;
    }
}
