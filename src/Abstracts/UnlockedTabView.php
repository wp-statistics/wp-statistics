<?php
namespace WP_Statistics\Abstracts;

abstract class UnlockedTabView
{
    protected $tab;
    protected $page;

    public function __construct()
    {
        add_filter("wp_statistics_{$this->page}_{$this->tab}_locked", '__return_false');
        add_filter("wp_statistics_{$this->page}_{$this->tab}_tooltip", [$this, 'getTooltip']);
        add_action("wp_statistics_{$this->page}_{$this->tab}_template", [$this, 'view']);
        add_action("wp_statistics_{$this->page}_{$this->tab}_data", [$this, 'getData']);
        add_filter("wp_statistics_{$this->page}_{$this->tab}_report_export_data", [$this, 'getExportData'], 10, 2);
    }

    /**
     * Returns the tooltip for the unlocked tab
     * @return string
     */
    abstract public function getTooltip();

    /**
     * Returns the data for the export
     * @return array
     */
    public function getExportData($data, $args)
    {
        return $this->getData();
    }

    /**
     * Returns the data for the unlocked tab
     *
     * This method is defined without parameters for backward compatibility,
     * but subclasses may optionally accept an `$args` array to customize data retrieval.
     *
     * @return array
     */
    public function getData()
    {
        return [];
    }

    /**
     * Render the view
     * @param mixed $args
     * @return void
     */
    abstract public function view($args);
}