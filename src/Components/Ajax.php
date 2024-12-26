<?php

namespace Wp_Statistics\Components;

class Ajax
{
    public static function register($action, $callback, $public = true)
    {
        add_action('wp_ajax_wp_statistics_' . $action, $callback);

        if ($public) {
            add_action('wp_ajax_nopriv_wp_statistics_' . $action, $callback);
        }
    }
}