<?php

namespace WP_Statistics\Components;

use WP_STATISTICS\Menus;

abstract class BasePage extends Singleton
{
    protected $pageSlug;

    public function __construct()
    {
        if (Menus::in_page($this->pageSlug)) {
            $this->initializePage();
        }
    }

    protected function initializePage()
    {
    }

    protected function disableScreenOption()
    {
        add_filter('screen_options_show_screen', '__return_false');
    }
}