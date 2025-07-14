<?php

namespace WP_Statistics\Service\CustomEvent;

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