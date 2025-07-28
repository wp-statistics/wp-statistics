<?php

namespace WP_Statistics\Service\Integrations\Plugins;

use Borlabs\Cookie\Repository\Service\ServiceRepository;
use WP_STATISTICS\Option;
use WP_Statistics\Utils\Query;

class BorlabsCookie extends AbstractIntegration
{
    protected $key = 'borlabs_cookie';
    protected $path = 'borlabs-cookie/borlabs-cookie.php';

    /**
     * Returns the name of the integration.
     *
     * @return  string
     */
    public function getName()
    {
        return esc_html__('Borlabs Cookie', 'wp-statistics');
    }

    public function detectionNotice()
    {
        return [
            'key'           => 'borlabs_cookie_detection_notice',
            'title'         => esc_html__('Consent integration available', 'wp-statistics'),
            'description'   => esc_html__('We’ve detected Borlabs consent plugin. Enable the “Borlabs Cookie integration” in WP Statistics → Settings → Privacy & Data Protection so your analytics respect visitor consent.', 'wp-statistics'),
        ];
    }

    /**
     * Since this plugin handles consent automatically itself, we always return true
     * @note: this approach is not compatible with Server side tracking mode
     *
     * @return  bool
     */
    public function hasConsent()
    {
        return true;
    }

    /**
     * Registers our plugin in "Borlabs Cookie'.
     * @return  void
     */
    public function register()
    {
        if (!class_exists(ServiceRepository::class) || Option::get('consent_integration') === 'borlabs_cookie') {
            return;
        }

        $isServiceActive = Query::select('*')
            ->from(ServiceRepository::TABLE)
            ->where(ServiceRepository::TABLE . '.key', '=', 'wp-statistics')
            ->where('status', '=', '1')
            ->getRow();

        if (!$isServiceActive) return;

        Option::update('consent_integration', $this->getKey());
    }

    /**
     * Return an array of js handles for this integration.
     * The result will be used as dependencies for the tracker js file
     *
     * @return  array
     */
    public function getJsHandles()
    {
        return [];
    }
}
