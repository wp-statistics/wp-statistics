<?php

namespace WP_Statistics\Service\CustomEvent;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class CustomEventManager
{
    public function __construct()
    {
        add_action('admin_init', [$this, 'registerAjaxCallbacks']);
    }

    public function registerAjaxCallbacks()
    {
        $customEventActions = new CustomEventActions();
        $customEventActions->register();
    }
}