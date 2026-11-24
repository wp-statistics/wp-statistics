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

        // If the WP Statistics service is active, set the integration. When the service is removed
        // later, the saved choice stays and the admin sees a notice.
        if ($integration !== $this->getKey() && $this->isServiceInstalled()) {
            Option::update('consent_integration', $this->getKey());
        }
    }

    /**
     * Borlabs Cookie only blocks the tracker when the WP Statistics service is set up in it.
     *
     * @return string The warning, or an empty string when the setup is fine.
     */
    public function getConfigurationWarning()
    {
        if ($this->isAvailable() && !$this->isServiceInstalled()) {
            return esc_html__('Consent integration is set to Borlabs Cookie, but the WP Statistics service is not set up in Borlabs Cookie, so it does not ask visitors for consent.', 'wp-statistics');
        }

        return parent::getConfigurationWarning();
    }

    /**
     * Check if the WP Statistics service is installed in Borlabs Cookie.
     * The answer is kept for the request, since it is asked on every hit.
     *
     * @return  bool
     */
    public function isServiceInstalled()
    {
        static $isServiceInstalled = null;

        if ($isServiceInstalled !== null) {
            return $isServiceInstalled;
        }

        if (!class_exists(ServiceRepository::class)) {
            return false;
        }

        $service = Query::select('*')
            ->from(ServiceRepository::TABLE)
            ->where('`key`', '=', 'wp-statistics')
            ->where('status', '=', '1')
            ->getRow();

        $isServiceInstalled = !empty($service);

        return $isServiceInstalled;
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
     * Tells the tracker whether Anonymous Tracking is on, so a decline does not stop the count.
     *
     * @return  array
     */
    public function getStatus()
    {
        return array_merge(parent::getStatus(), [
            'anonymous_tracking' => $this->trackAnonymously(),
        ]);
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
