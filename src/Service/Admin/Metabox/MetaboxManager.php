<?php
namespace WP_Statistics\Service\Admin\Metabox;

use WP_Statistics\Abstracts\BaseMetabox;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TrafficSummary;

class MetaboxManager
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerMetaboxes']);
    }

    /**
     * Retrieves a list of all metaboxes.
     *
     * @return BaseMetabox[]
     */
    public function getMetaboxes()
    {
        $metaboxes = [
            TrafficSummary::class
        ];

        return apply_filters('wp_statistics_metabox_list', $metaboxes);
    }

    /**
     * Registers active metaboxes.
     *
     * @return void
     */
    public function registerMetaboxes()
    {
        $metaboxes = $this->getMetaboxes();

        foreach ($metaboxes as $metabox) {
            $metabox = new $metabox();

            // Skip inactive metaboxes
            if (!$metabox->isActive()) continue;

            $metabox->register();
        }
    }
}