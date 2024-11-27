<?php
namespace WP_Statistics\Service\Admin\Metabox;

class MetaboxManager
{
    public function __construct()
    {
        $this->registerMetaboxes();
    }

    public function getMetaboxes()
    {
        $metaboxes = [];
        return apply_filters('wp_statistics_metabox_list', $metaboxes);
    }

    public function registerMetaboxes()
    {

    }
}