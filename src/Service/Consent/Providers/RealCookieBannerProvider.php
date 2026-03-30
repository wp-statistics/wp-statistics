<?php

namespace WP_Statistics\Service\Consent\Providers;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Consent\AbstractConsentProvider;

class RealCookieBannerProvider extends AbstractConsentProvider
{
    protected string $key = 'real_cookie_banner';
    protected string $pluginPath = 'real-cookie-banner-pro/index.php';

    public function getName(): string
    {
        return esc_html__('Real Cookie Banner PRO', 'wp-statistics');
    }

    public function register(): void
    {
        add_action('wp_statistics_save_settings', [$this, 'clearTemplateCache']);
        add_action('RCB/Templates/TechnicalHandlingIntegration', [$this, 'handleIntegration']);
    }

    public function clearTemplateCache(): void
    {
        if (function_exists('wp_rcb_invalidate_templates_cache')) {
            wp_rcb_invalidate_templates_cache();
        }
    }

    public function handleIntegration($integration): void
    {
        $storeIp = (bool) Option::getValue('store_ip');
        $file    = WP_STATISTICS_MAIN_FILE;

        $integration->integrate($file, 'wp-statistics');

        if ($storeIp) {
            $integration->integrate($file, 'wp-statistics-with-data-processing');
        }
    }

    public function getJsDependencies(): array
    {
        return ['real-cookie-banner-pro-banner'];
    }

    public function getInlineScript(): string
    {
        return <<<'JS'
(function() {
    var r = window.WpStatisticsConsentAdapters = window.WpStatisticsConsentAdapters || {};
    if (!r.real_cookie_banner) {
        r.real_cookie_banner = {
            init: function(params) {
                var levels = params.config.levels;
                var addFilter = params.addFilter;
                var doAction = params.doAction;

                var resolvedLevel = levels.none;

                addFilter('trackingLevel', function() {
                    return resolvedLevel;
                });

                if (!window.consentApi || typeof window.consentApi.consent !== 'function') {
                    console.warn('WP Statistics: Real Cookie Banner consentApi not found. Tracking disabled until consent API loads.');
                    return;
                }

                var dpConsent = null;
                try {
                    dpConsent = window.consentApi.consentSync('wp-statistics-with-data-processing');
                } catch (e) {
                    console.warn('WP Statistics: Error checking RCB data processing consent.', e);
                }

                if (dpConsent && dpConsent.cookie != null && dpConsent.cookieOptIn) {
                    resolvedLevel = levels.full;
                    return;
                }

                var baseConsent = null;
                try {
                    baseConsent = window.consentApi.consentSync('wp-statistics');
                } catch (e) {
                    console.warn('WP Statistics: Error checking RCB base consent.', e);
                }

                if (baseConsent && baseConsent.cookie != null && baseConsent.cookieOptIn) {
                    resolvedLevel = levels.anonymous;
                    return;
                }

                window.consentApi.consent('wp-statistics')
                    .then(function() {
                        resolvedLevel = levels.anonymous;
                        doAction('consentChanged');
                    })
                    .catch(function(err) {
                        if (err) {
                            console.debug('WP Statistics: RCB consent not given or error:', err);
                        }
                    });
            }
        };
    }
})();
JS;
    }
}
