<?php

namespace WP_Statistics\Service\Integrations\Plugins;

abstract class AbstractIntegration
{
    /**
     * Checks if plugin is activated.
     *
     * @return  bool
     */
    abstract public function isActive();

    /**
     * Checks if the user has given consent.
     * @return bool
     */
    abstract public function hasConsent();

    /**
     * Register integration hooks.
     * @return  void
     */
    abstract public function register();
}
