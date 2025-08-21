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

    /**
     * Check if the integration option is selectable.
     *
     * @return  bool
     */
    public function isSelectable()
    {
        return $this->isActive() && $this->isServiceInstalled();
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
        $integration = Option::get('consent_integration');

        // If any other consent integration is active, return
        if (!empty($integration) && $integration !== $this->getKey()) return;

        $isServiceActive = $this->isServiceInstalled();

        // If the WP Statistics service is no longer active, remove the integration
        if ($integration === $this->getKey() && !$isServiceActive) {
            Option::update('consent_integration', '');
        }

        // If the WP Statistics service is active, set the integration
        if ($isServiceActive) {
            Option::update('consent_integration', $this->getKey());
        }
    }

    /**
     * Check if the WP Statistics service is installed in Borlabs Cookie.
     *
     * @return  bool
     */
    public function isServiceInstalled()
    {
        if (!class_exists(ServiceRepository::class)) {
            return false;
        }

        $isServiceInstalled = Query::select('*')
            ->from(ServiceRepository::TABLE)
            ->where('`key`', '=', 'wp-statistics')
            ->where('status', '=', '1')
            ->getRow();

        return !empty($isServiceInstalled);
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
