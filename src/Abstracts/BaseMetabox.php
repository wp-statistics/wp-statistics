<?php
namespace WP_Statistics\Abstracts;

use WP_Statistics\Service\Admin\Overview\OverviewPage;

abstract class BaseMetabox
{
    /**
     * Returns the key for the metabox (should be unique)
     * @return string
     */
    abstract public function getKey();

    /**
     * Returns the name of the metabox
     * @return string
     */
    abstract public function getName();

    /**
     * Returns the priority of the metabox (side, normal, advanced)
     * @return string
     */
    abstract public function getPriority();

    /**
     * Returns the data for the metabox
     * @return array
     */
    abstract public function getData();

    /**
     * Renders the metabox output
     * @return string
     */
    abstract public function render($data);

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
     * @return array
     */
    public function getScreen()
    {
        return ['statistics_page_wps_overview-new_page', 'dashboard'];
    }
}