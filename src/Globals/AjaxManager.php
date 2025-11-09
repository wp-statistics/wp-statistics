<?php
namespace WP_Statistics\Globals;

use WP_Statistics\Components\Ajax;

class AjaxManager
{
    public function __construct()
    {
        add_action('init', [$this, 'register']);
    }

    /**
     * Register AJAX callbacks.
     *
     * @example Ajax::register('test', [$this, 'test'])
     */
    public function register()
    {

    }
}