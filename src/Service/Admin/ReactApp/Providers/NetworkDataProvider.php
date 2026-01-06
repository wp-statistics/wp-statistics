<?php

namespace WP_Statistics\Service\Admin\ReactApp\Providers;

use WP_Statistics\Service\Admin\ReactApp\Contracts\LocalizeDataProviderInterface;

/**
 * Provider for network (multisite) data.
 *
 * This provider delivers network-specific data to the React application,
 * including:
 * - Whether this is a multisite installation
 * - Whether we're in network admin context
 * - List of sites in the network with their dashboard links
 *
 * Data is only populated when in network admin context.
 *
 * @since 15.0.0
 */
class NetworkDataProvider implements LocalizeDataProviderInterface
{
    /**
     * Get network data.
     *
     * @return array Array of network data
     */
    public function getData()
    {
        $data = [
            'isMultisite'    => is_multisite(),
            'isNetworkAdmin' => is_network_admin(),
            'sites'          => [],
        ];

        // Only populate sites when in network admin context
        if (is_multisite() && is_network_admin() && is_super_admin()) {
            $data['sites'] = $this->getNetworkSites();
        }

        return $data;
    }

    /**
     * Get the localize data key.
     *
     * @return string The key 'network' for network data
     */
    public function getKey()
    {
        return 'network';
    }

    /**
     * Get list of network sites with basic info.
     *
     * @return array List of sites
     */
    private function getNetworkSites()
    {
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
                'blogId'       => (int) $blogId,
                'name'         => $siteName,
                'url'          => get_home_url($blogId),
                'dashboardUrl' => get_admin_url($blogId, 'admin.php?page=wp-statistics'),
            ];
        }

        return $sitesData;
    }
}
