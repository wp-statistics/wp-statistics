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
    }

    /**
     * Returns the tooltip for the unlocked tab
     * @return string
     */
    abstract public function getTooltip();

    /**
     * Returns the data for the unlocked tab
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