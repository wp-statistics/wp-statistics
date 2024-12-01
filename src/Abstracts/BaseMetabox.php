<?php
namespace WP_Statistics\Abstracts;

use Wp_Statistics\Components\Ajax;

abstract class BaseMetabox
{
    protected $key;
    protected $priority;

    /**
     * Returns the name of the metabox
     * @return string
     */
    abstract public function getName();

    /**
     * Returns the data for the metabox
     * @return void
     */
    abstract public function getData();

    /**
     * Renders the metabox output
     * @return void
     */
    abstract public function render();

    /**
     * Returns the key for the metabox (should be unique)
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Returns the priority of the metabox (side, normal, advanced)
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Determines if the metabox is active and should be displayed
     * @return bool
     */
    public function isActive()
    {
        return true;
    }

    /**
     * Returns the screens the metabox is active on
     * @todo get overview screen id dynamically
     * @return array
     */
    public function getScreen()
    {
        return ['statistics_page_wps_overview-new_page', 'dashboard'];
    }

    /**
     * Registers the metabox
     *
     * Registers the metabox with the admin and hooks into the WordPress AJAX handler
     *
     * @return void
     */
    public function register()
    {
        Ajax::register($this->getKey() . '_metabox_get_data', [$this, 'getData'], false);
        add_meta_box($this->getKey(), $this->getName(), [$this, 'render'], $this->getScreen(), $this->getPriority());
    }
}