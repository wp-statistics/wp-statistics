<?php
namespace WP_Statistics\Service\Admin\Metabox;

use WP_Statistics\Abstracts\BaseMetabox;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TrafficSummary;

class MetaboxManager
{
    public $metaboxes = [
        TrafficSummary::class
    ];

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
        return apply_filters('wp_statistics_metabox_list', $this->metaboxes);
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
            if (!class_exists($metabox)) continue;

            $metabox = new $metabox();

            // Skip inactive metaboxes
            if (!$metabox->isActive()) continue;

            $metabox->register();
        }
    }
}