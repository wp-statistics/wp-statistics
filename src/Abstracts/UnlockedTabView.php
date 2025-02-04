<?php
namespace WP_Statistics\Abstracts;

abstract class UnlockedTabView
{
    protected $tab;
    protected $page;

    public function __construct()
    {
        add_filter("wp_statistics_{$this->page}_{$this->tab}_locked", '__return_false');
        add_action("wp_statistics_{$this->page}_{$this->tab}_template", [$this, 'view']);
    }

    /**
     * Render the view
     * @param mixed $args
     * @return void
     */
    abstract public function view($args);
}