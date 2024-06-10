<?php

namespace WP_Statistics\Abstracts;

use WP_STATISTICS\Menus;
use WP_Statistics\Components\Singleton;

abstract class BasePage extends Singleton
{
    protected $pageSlug;

    public function __construct()
    {
        if (Menus::in_page($this->pageSlug)) {
            $this->init();
        }
    }

    protected function init()
    {
    }

    protected function disableScreenOption()
    {
        add_filter('screen_options_show_screen', '__return_false');
    }
}