<?php
namespace WP_Statistics\Service\Admin\Metabox;


class MetaboxManager
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerMetaboxes']);
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