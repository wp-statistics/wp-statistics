<?php

namespace WP_Statistics\Service\Integrations\Plugins;

use WP_STATISTICS\Option;

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
     * Checks if plugin is activated.
     *
     * @return  bool
     */
    public function isActive()
    {
        return is_plugin_active($this->getPath());
    }

    /**
     * Check if the integration option is selectable.
     *
     * @return  bool
     */
    public function isSelectable()
    {
        return $this->isActive();
    }

    /**
     * Determine if the integration's notice should be shown to the user.
     *
     * @return bool
     */
    public function showNotice()
    {
        return $this->isActive();
    }

    /**
     * Get integration status
     * @return array
     */
    public function getStatus()
    {
        return [
            'has_consent'       => $this->hasConsent(),
            'track_anonymously' => $this->trackAnonymously()
        ];
    }

    /**
     * If returns true, the user data will be collected anonymously
     * @return bool
     */
    public function trackAnonymously()
    {
        return Option::get('anonymous_tracking', false) != false;
    }

    /**
     * Integration name
     * @return string
     */
    abstract public function getName();

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

    /**
     * Return an array of js handles for this integration.
     * The result will be used as dependencies for the tracker js file
     *
     * @return  array
     */
    abstract public function getJsHandles();
}
