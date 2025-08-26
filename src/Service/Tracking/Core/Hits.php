<?php

namespace WP_Statistics\Service\Tracking\Core;

use Exception;
use WP_Statistics\Abstracts\BaseTracking;
use WP_Statistics\Utils\Route;
use WP_Statistics\Entity\EntityFactory;
use WP_Statistics\Globals\Option;
use WP_Statistics\Service\Analytics\VisitorProfile;
use WP_Statistics\Service\Integrations\IntegrationHelper;
use WP_Statistics\Service\Tracking\TrackerHelper;
use WP_Statistics\Traits\ErrorLoggerTrait;
use WP_Statistics\Utils\Request;

/**
 * Handles hit tracking for visitors, including page views, REST API activity, and login tracking.
 *
 * Integrates with the exclusion system to respect rules such as DNT, user roles, and IP blocks, and etc.
 */
class Hits extends BaseTracking
{
    use ErrorLoggerTrait;

    /**
     * Request key used to identify REST API-based tracking calls.
     *
     * @var string
     */
    protected $restHitsKey = 'wp_statistics_hit';

    /**
     * Parsed data from REST tracking request, if applicable.
     *
     * @var object|null
     */
    protected $restHits = null;

    /**
     * Hits Constructor.
     * Adds filters and actions for tracking based on context.
     *
     * @return void
     */
    public function __construct()
    {
        if ($this->isRestHit()) {
            $this->restHits = (object)$this->getRestParams();
        }

        if (!Option::getValue('exclude_loginpage')) {
            add_action('init', [$this, 'trackLoginPageCallback']);
        }

        add_action('wp', [$this, 'trackServerSideCallback']);
    }

    /**
     * Check if the current request is a REST API hit tracking call.
     *
     * @return bool True if request is a REST hit, false otherwise.
     */
    protected function isRestHit()
    {
        return Request::isRestApiCall() && isset($_REQUEST[$this->getRestHitsKey()]);
    }

    /**
     * Get all or a specific key from REST hit parameters.
     *
     * @param string|false $key Optional key to fetch from parameters.
     * @return mixed Array of all params or single value, or false if key not found.
     */
    protected function getRestParams($key = false)
    {
        $data = [];

        if (!$this->isRestHit()) {
            return $data;
        }

        foreach ($_REQUEST as $requestKey => $value) {
            $data[$requestKey] = $value;
        }

        return $key === false ? $data : (isset($data[$key]) ? $data[$key] : false);
    }

    /**
     * Record a hit including visitor, device, geo, locale, referrer, session, view, and parameter tracking.
     *
     * @param VisitorProfile|null $visitorProfile Optional profile object.
     * @return array Exclusion data if visitor was excluded.
     * @throws Exception If visitor is excluded by rules.
     *
     * @todo UserAgent has very bad performance we need to discuss about it.
     */
    public function record($visitorProfile = null)
    {
        $visitorProfile = $this->resolveProfile($visitorProfile);
        $exclusion      = $this->checkAndThrowIfExcluded($visitorProfile);

        $resourceUriId = Request::get('resourceUriId', 0);

        if (empty($resourceUriId)) {
            throw new Exception(esc_html__('Missing or invalid resource uri ID', 'wp-statistics'), 200);
            return;
        }

        $visitorProfile->setResourceUriId($resourceUriId);

        EntityFactory::visitor($visitorProfile)
            ->record();

        EntityFactory::device($visitorProfile)
            ->recordType()
            ->recordOs()
            ->recordBrowser()
            ->recordBrowserVersion()
            ->recordResolution();

        EntityFactory::geo($visitorProfile)
            ->recordCountry()
            ->recordCity();

        EntityFactory::locale($visitorProfile)
            ->recordLanguage()
            ->recordTimezone();

        EntityFactory::referrer($visitorProfile)
            ->record();

        EntityFactory::session($visitorProfile)
            ->record();

        EntityFactory::view($visitorProfile)
            ->record();

        EntityFactory::parameter($visitorProfile)
            ->record();

        $this->errorListener();

        return $exclusion;
    }

    /**
     * Conditionally record login page hits.
     *
     * @return void
     */
    public function trackLoginPageCallback()
    {
        if (Route::isLoginPage()) {
            try {
                $this->record();
            } catch (Exception $e) {
                $this->errorListener();
            }
        }
    }

    /**
     * Track server-rendered page hits if conditions allow.
     *
     * @return void
     */
    public function trackServerSideCallback()
    {
        try {
            if (
                is_favicon() ||
                is_admin() ||
                is_preview() ||
                Option::getValue('use_cache_plugin') ||
                TrackerHelper::isDoNotTrackEnabled()
            ) {
                return;
            }

            $consentLevel = Option::getValue('consent_level_integration', 'disabled');

            if (
                $consentLevel === 'disabled' ||
                IntegrationHelper::shouldTrackAnonymously()
            ) {
                $this->record();
            }
        } catch (Exception $e) {
            $this->errorListener();
        }
    }
}
