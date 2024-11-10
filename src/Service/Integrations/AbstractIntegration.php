<?php

namespace WP_Statistics\Service\Integrations;

abstract class AbstractIntegration
{
    /**
     * Checks if plugin is activated.
     *
     * @return  bool
     */
    abstract public static function isActive();

    /**
     * Register integration hooks.
     * @return  void
     */
    abstract public function register();
}
