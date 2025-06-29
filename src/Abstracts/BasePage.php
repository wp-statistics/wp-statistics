<?php

namespace WP_Statistics\Abstracts;

use Exception;
use WP_STATISTICS\Menus;
use WP_Statistics\Components\Singleton;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

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

    public function render()
    {
    }

    public function view()
    {
        try {
            $this->render();
        } catch (Exception $e) {
            Notice::renderNotice($e->getMessage(), $e->getCode(), 'error');
        }
    }
}