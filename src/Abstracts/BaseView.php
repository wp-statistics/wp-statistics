<?php

namespace WP_Statistics\Abstracts;

use WP_STATISTICS\Menus;
use WP_Statistics\Utils\Request;


abstract class BaseView
{
    protected $dataProvider;

    abstract protected function render();

    /**
     * Get the current page based on the "page" request parameter.
     *
     * @return string The current page.
     */
    protected function getCurrentPage()
    {
        return Menus::getPageKeyFromSlug(Request::get('page'))[0] ?? '';
    }
}