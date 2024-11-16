<?php

namespace WP_Statistics\Service\Integrations\Plugins;

abstract class AbstractIntegration
{
    /**
     * Integration key
     * @return string
     */
    abstract public function getKey();

    /**
     * Integration name
     * @return string
     */
    abstract public function getName();

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
     * Get integration status
     * @return array
     */
    abstract public function getStatus();

    /**
     * Register integration hooks.
     * @return  void
     */
    abstract public function register();
}
