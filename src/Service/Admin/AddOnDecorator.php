<?php

namespace WP_Statistics\Service\Admin;

class AddOnDecorator
{
    private $addOn;
    private $isActivated = false;
    private $status;

    /**
     * @param $addOn
     */
    public function __construct($addOn)
    {
        $this->addOn = $addOn;
    }

    public function getName()
    {
        return $this->addOn->name;
    }

    public function getSlug()
    {
        return $this->addOn->slug;
    }

    public function getUrl()
    {
        return $this->addOn->url;
    }

    public function getDescription()
    {
        return $this->addOn->description;
    }

    public function getIcon()
    {
        return $this->addOn->icon;
    }

    public function getImage()
    {
        return $this->addOn->image;
    }

    public function getPrice()
    {
        return $this->addOn->price;
    }

    public function getVersion()
    {
        return $this->addOn->version;
    }

    public function isFeatured()
    {
        return $this->addOn->is_feature == true ? true : false;
    }

    public function getFeaturedLabel()
    {
        return $this->addOn->featured_label;
    }

    public function isExist()
    {
        return file_exists(WP_PLUGIN_DIR . '/' . $this->getPluginName());
    }

    public function isEnabled()
    {
        return is_plugin_active($this->getPluginName());
    }

    public function isActivated()
    {
        $this->status = $this->getRemoteStatus();

        return $this->isActivated;
    }

    public function getOptionName()
    {
        return AddOnsFactory::getSettingNameByKey($this->getSlug());
    }

    public function getLicense()
    {
        $option = get_option($this->getOptionName());
        return isset($option['license_key']) ? $option['license_key'] : '';
    }

    public function getPluginName()
    {
        return sprintf('%s/%s.php', $this->getSlug(), $this->getSlug());
    }

    public function getActivateUrl()
    {
        return add_query_arg([
            'action'   => 'activate',
            'plugin'   => $this->getPluginName(),
            '_wpnonce' => wp_create_nonce("activate-plugin_{$this->getPluginName()}")
        ], admin_url('plugins.php'));
    }

    public function getDeactivateUrl()
    {
        return add_query_arg([
            'action'   => 'deactivate',
            'plugin'   => $this->getPluginName(),
            '_wpnonce' => wp_create_nonce("deactivate-plugin_{$this->getPluginName()}")
        ], admin_url('plugins.php'));
    }

    public function getStatus()
    {
        if ($this->isEnabled()) {

            $this->status = $this->getRemoteStatus();

            if (is_wp_error($this->status)) {
                return $this->status->get_error_message();
            }

            if ($this->status) {
                return __('Activated', 'wp-statistics');
            } else {
                return __('Not activated', 'wp-statistics');
            }

        } else if ($this->isExist()) {
            return __('Inactive', 'wp-statistics');
        }

        return __('Not installed', 'wp-statistics');
    }

    public function getRemoteStatus()
    {
        // Avoid remote request
        if (!$this->isExist() or !$this->getLicense()) {
            return false;
        }

        // Cache the status
        if ($this->status) {
            return $this->status;
        }

        $transientKey = AddOnsFactory::getLicenseTransientKey($this->getSlug());

        // Get any existing copy of our transient data
        if (false === ($response = get_transient($transientKey))) {

            $response = wp_remote_get(add_query_arg(array(
                'plugin-name' => $this->getSlug(),
                'license_key' => $this->getLicense(),
                'website'     => get_bloginfo('url'),
            ), WP_STATISTICS_SITE . '/wp-json/plugins/v1/validate'));

            if (is_wp_error($response)) {
                return $response;
            }

            $body     = wp_remote_retrieve_body($response);
            $response = json_decode($body, false);

            set_transient($transientKey, $response, DAY_IN_SECONDS);
        }
        
        if (isset($response->code) && $response->code == 'error') {
            return new \WP_Error($response->data->status, $response->message);
        }

        if (isset($response->status) and $response->status == 200) {
            $this->isActivated = true;

            return true;
        }
    }
}