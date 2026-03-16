<?php

namespace WP_Statistics\Service\Consent\Providers;

use WP_Statistics\Service\Consent\AbstractConsentProvider;
use WP_Statistics\Utils\Query;

class BorlabsCookieProvider extends AbstractConsentProvider
{
    protected string $key = 'borlabs_cookie';
    protected string $pluginPath = 'borlabs-cookie/borlabs-cookie.php';

    private ?bool $serviceInstalled = null;

    public function getName(): string
    {
        return esc_html__('Borlabs Cookie', 'wp-statistics');
    }

    public function isAvailable(): bool
    {
        return parent::isAvailable() && $this->isServiceInstalled();
    }

    public function isServiceInstalled(): bool
    {
        if ($this->serviceInstalled !== null) {
            return $this->serviceInstalled;
        }

        if (!class_exists('Borlabs\Cookie\Repository\Service\ServiceRepository')) {
            $this->serviceInstalled = false;
            return false;
        }

        $row = Query::select('1')
            ->from('borlabs_cookie_services')
            ->where('`key`', '=', 'wp-statistics')
            ->where('status', '=', '1')
            ->getRow();

        $this->serviceInstalled = !empty($row);
        return $this->serviceInstalled;
    }

    public function getInlineScript(): string
    {
        return <<<'JS'
(function() {
    var r = window.WpStatisticsConsentAdapters = window.WpStatisticsConsentAdapters || {};
    if (!r.borlabs_cookie) {
        r.borlabs_cookie = {
            init: function(params) {
                var levels = params.config.levels;
                var addFilter = params.addFilter;

                addFilter('trackingLevel', function() {
                    return params.config.anonymousTracking ? levels.anonymous : levels.full;
                });
            }
        };
    }
})();
JS;
    }
}
