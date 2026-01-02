<?php

namespace WP_Statistics\Abstracts;

use WP_Statistics\Components\Menu;
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
        return Menu::getPageKeyFromSlug(Request::get('page'))[0] ?? '';
    }
}