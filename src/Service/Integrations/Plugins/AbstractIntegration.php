<?php

namespace WP_Statistics\Service\Integrations\Plugins;

abstract class AbstractIntegration
{
    protected $key;
    protected $path;

    /**
     * Integration key
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Get plugin path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Integration name
     * @return string
     */
    abstract public function getName();

    /**
     * Detection notice
     */
    abstract public function detectionNotice();

    /**
     * Checks if plugin is activated.
     *
     * @return  bool
     */
    abstract public function isActive();

    /**
     * If returns true, the user data will be collected anonymously
     * @return bool
     */
    abstract public function trackAnonymously();

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
