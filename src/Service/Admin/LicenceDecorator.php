<?php

namespace WP_Statistics\Service\Admin;

class LicenceDecorator
{
    /**
     * @var int|string|null
     */
    public $addOnSlug;
    /**
     * @var mixed
     */
    private $addOnName;

    private $optionMap = [
        'wpstatistics_realtime_stats_settings' => 'license_key_status'
    ];

    public function __construct($addOnSlug, $addOnName)
    {
        $this->addOnSlug = $addOnSlug;
        $this->addOnName = $addOnName;
    }

    public function getName()
    {
        return $this->addOnName;
    }

    public function getSlug()
    {
        return $this->addOnSlug;
    }

    public function getHtmlOptionName()
    {
        return sprintf('wp_statistics_license[%s]', $this->getSlug());
    }

    public function getOptionName()
    {
        return 'wpstatistics_realtime_stats_settings';
    }

    public function getLicenseFromOption()
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
            'plugin-name' => $this->addOnSlug,
            'license_key' => $this->getLicenseKey(),
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