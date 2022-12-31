<?php

namespace WP_Statistics\Service\Admin;

class AddOnsDecorator
{
    private $addOn;

    private $optionMap = [
        'wpstatistics_realtime_stats_settings' => 'license_key_status'
    ];

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

    public function isFeatured()
    {
        return $this->addOn->is_feature == true ? true : false;
    }

    public function getFeaturedLabel()
    {
        return $this->addOn->featured_label;
    }

    public function getHtmlOptionName()
    {
        return sprintf('wp_statistics_license[%s]', $this->getSlug());
    }

    public function getOptionName()
    {
        return 'wpstatistics_realtime_stats_settings';
    }

    public function exist()
    {

    }

    public function getLicenseFromOption()
    {

    }

    public function getActivateUrl()
    {

    }

    public function getDeactivateUrl()
    {

    }

    public function getStatus()
    {
        return 'Active';
        return get_option($this->getOptionName());
    }

    public function getRemoteStatus()
    {
        $response = wp_remote_get(add_query_arg(array(
            'plugin-name' => $this->getSlug(),
            'license_key' => $this->getLicenseFromOption(),
            'website'     => get_bloginfo('url'),
        ), WP_STATISTICS_SITE . '/wp-json/plugins/v1/validate'));

        if (is_wp_error($response)) {
            return;
        }

        $response = json_decode($response['body']);

        if (isset($response->status) and $response->status == 200) {
            return true;
        }
    }

    public function getAccoutUrl()
    {
        return esc_url(WP_STATISTICS_SITE_URL . '/my-account/orders/');
    }
}