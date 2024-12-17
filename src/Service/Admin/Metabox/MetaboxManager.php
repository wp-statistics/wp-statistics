<?php
namespace WP_Statistics\Service\Admin\Metabox;

use WP_Statistics\Abstracts\BaseMetabox;
use WP_Statistics\Service\Admin\Metabox\Metaboxes\TrafficSummary;

class MetaboxManager
{
    public function __construct()
    {
        add_action('add_meta_boxes', [$this, 'registerMetaboxes'], 99999);
    }

    /**
     * Registers active metaboxes.
     *
     * @return void
     */
    public function registerMetaboxes()
    {
        $metaboxes = MetaboxHelper::getActiveMetaboxes();

        foreach ($metaboxes as $metabox) {
            $metabox->register();
        }
    }
}