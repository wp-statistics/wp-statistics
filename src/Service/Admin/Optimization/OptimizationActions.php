<?php
namespace WP_Statistics\Service\Admin\Optimization;

use Exception;
use WP_Statistics\Components\Ajax;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Historical;
use WP_STATISTICS\IP;
use WP_Statistics\Models\EventsModel;
use WP_STATISTICS\Option;
use WP_STATISTICS\Purge;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_Statistics\Service\Database\Managers\SchemaMaintainer;
use WP_Statistics\Service\Database\Migrations\BackgroundProcess\BackgroundProcessFactory;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Service\Geolocation\Provider\DbIpProvider;
use WP_Statistics\Service\Geolocation\Provider\MaxmindGeoIPProvider;
use WP_Statistics\Traits\AjaxUtilityTrait;
use WP_Statistics\Utils\Query;
use WP_Statistics\Utils\Request;

class OptimizationActions
{
    use AjaxUtilityTrait;

    public function register()
    {
        Ajax::register('purge_old_data', [$this, 'purgeOldData'], false);
        Ajax::register('purge_visitors', [$this, 'purgeVisitors'], false);
        Ajax::register('clear_user_ids', [$this, 'clearUserIds'], false);
        Ajax::register('clear_ua_strings', [$this, 'clearUAStrings'], false);
        Ajax::register('delete_word_count_data', [$this, 'deleteWordCountData'], false);
        Ajax::register('query_params_cleanup', [$this, 'cleanUpQueryParams'], false);
        Ajax::register('event_data_cleanup', [$this, 'cleanUpEventData'], false);
        Ajax::register('handle_historical_setting_form', [$this, 'handleHistoricalSettingForm'], false);
        Ajax::register('update_country_data', [$this, 'updateCountryData'], false);
        Ajax::register('update_source_channel_data', [$this, 'updateSourceChannelData'], false);
        Ajax::register('hash_ips', [$this, 'hashIps'], false);
        Ajax::register('recheck_schema', [$this, 'recheckSchema'], false);
        Ajax::register('repair_schema', [$this, 'repairSchema'], false);
    }

    /**
     * Setup an AJAX action to purge old data in the optimization page.
     */
    public function purgeOldData()
    {
        try {
            $this->verifyAjaxRequest();
            $this->checkAdminReferrer('wp_rest', 'wps_nonce');
            $this->checkCapability('manage');

            $purgeDays = Request::get('purge-days', 0, 'number');

            if ($purgeDays < 30) {
                throw new Exception(esc_html__('The number of days to purge must be at least 30.', 'wp-statistics'), 400);
            }

            $result = Purge::purge_data($purgeDays);

            Ajax::success($result);
        } catch (Exception $e) {
            Ajax::error($e->getMessage(), null, $e->getCode());
        }
    }

    /**
     * Setup an AJAX action to purge visitors.
     */
    public function purgeVisitors()
    {
        try {
            $this->verifyAjaxRequest();
            $this->checkAdminReferrer('wp_rest', 'wps_nonce');
            $this->checkCapability('manage');

            $hits     = Request::get('purge-hits');
            $ip       = Request::get('ip-address');
            $browser  = Request::get('agent-name');
            $platform = Request::get('platform-name');

            // Throw error if all fields are empty
            if (!$hits && !$ip && !$browser && !$platform) {
                throw new Exception(esc_html__('Please select at least one field to purge.', 'wp-statistics'), 400);
            }

            // Purge visitors by hits
            if ($hits !== false) {
                if (!is_numeric($hits) || $hits < 10) {
                    throw new Exception(esc_html__('View count must be 10 or more! Please enter a valid number and try again.', 'wp-statistics'), 400);
                }

                $result = Query::delete('visitor')->where('hits', '>', $hits)->execute();
            }

            // Purge visitors by IP
            if ($ip !== false) {
                if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                    throw new Exception(esc_html__('Invalid IP address! Please enter a valid IP address and try again.', 'wp-statistics'), 400);
                }

                $result = Query::delete('visitor')->where('ip', '=', $ip)->execute();
            }

            // Purge visitors by browser
            if ($browser !== false) {
                $result = Query::delete('visitor')->where('agent', '=', $browser)->execute();
            }

            // Purge visitors by platform
            if ($platform !== false) {
                $result = Query::delete('visitor')->where('platform', '=', $platform)->execute();
            }

            // Throw error on failure
            if ($result === false) {
                throw new Exception('Could not purge visitors. Please try again.', 500);
            }

            Ajax::success(sprintf(esc_html__('Removed %s visitors.', 'wp-statistics'), "<code>$result</code>"));
        } catch (Exception $e) {
            Ajax::error($e->getMessage(), null, $e->getCode());
        }
    }

    /**
     * Setup an AJAX action to clear user IDs from the database.
     */
    public function clearUserIds()
    {
        try {
            $this->verifyAjaxRequest();
            $this->checkAdminReferrer('wp_rest', 'wps_nonce');
            $this->checkCapability('manage');

            $result = Query::update('visitor')
                ->set(['user_id' => 0])
                ->execute();

            if ($result === false) {
                throw new Exception('Could not remove user IDs. Please try again.', 500);
            }

            Ajax::success(esc_html__('User IDs removed.', 'wp-statistics'));
        } catch (Exception $e) {
            Ajax::error($e->getMessage(), null, $e->getCode());
        }
    }

    /**
     * Setup an AJAX action to clear UA strings from the database.
     */
    public function clearUAStrings()
    {
        try {
            $this->verifyAjaxRequest();
            $this->checkAdminReferrer('wp_rest', 'wps_nonce');
            $this->checkCapability('manage');

            $result = Query::update('visitor')
                ->set(['UAString' => null])
                ->execute();

            if ($result === false) {
                throw new Exception('Could not remove user agent strings. Please try again.', 500);
            }

            Ajax::success(esc_html__('User agent strings removed.', 'wp-statistics'));
        } catch (Exception $e) {
            Ajax::error($e->getMessage(), null, $e->getCode());
        }
    }

    /**
     * Setup an AJAX action to delete word count data from the database.
     */
    public function deleteWordCountData()
    {
        try {
            $this->verifyAjaxRequest();
            $this->checkAdminReferrer('wp_rest', 'wps_nonce');
            $this->checkCapability('manage');

            Option::deleteOptionGroup('word_count_process_initiated', 'jobs');

            $result = Query::delete('postmeta')
                ->where('meta_key', '=', 'wp_statistics_words_count')
                ->execute();

            if ($result === false) {
                throw new Exception('Could not clear word count data. Please try again.', 500);
            }

            Ajax::success(esc_html__('Word count data cleared.', 'wp-statistics'));
        } catch (Exception $e) {
            Ajax::error($e->getMessage(), null, $e->getCode());
        }
    }

    /**
     * Setup an AJAX action to clean up query params from the database.
     */
    public function cleanUpQueryParams()
    {
        try {
            $this->verifyAjaxRequest();
            $this->checkAdminReferrer('wp_rest', 'wps_nonce');
            $this->checkCapability('manage');

            // Get allowed query params
            $allowedQueryParams = Helper::get_query_params_allow_list();

            // Get all rows from pages table
            $pages = Query::select('*')
                ->from('pages')
                ->where('uri', 'LIKE', '%?%')
                ->getAll();

            if ($pages) {
                // Update query strings based on allow list
                foreach ($pages as $page) {
                    Query::update('pages')
                        ->set(['uri' => Helper::FilterQueryStringUrl($page->uri, $allowedQueryParams)])
                        ->where('page_id', '=', $page->page_id)
                        ->execute();
                }
            }

            Ajax::success(esc_html__('Cleaned up query parameters in saved URLs.', 'wp-statistics'));
        } catch (Exception $e) {
            Ajax::error($e->getMessage(), null, $e->getCode());
        }
    }

    /**
     * Setup an AJAX action to clean up event data from the database.
     */
    public function cleanUpEventData()
    {
        try {
            $this->verifyAjaxRequest();
            $this->checkAdminReferrer('wp_rest', 'wps_nonce');
            $this->checkCapability('manage');

            $eventName = Request::get('event_name');

            if (!$eventName) {
                throw new Exception(esc_html__('Enter a valid event name.', 'wp-statistics'), 400);
            }

            $eventsModel = new EventsModel();
            $result      = $eventsModel->deleteEvents(['event_name' => $eventName]);

            if ($result === false) {
                throw new Exception('Could not remove event data. Please try again.', 500);
            }

            Ajax::success(sprintf(esc_html__('Event data removed for %s', 'wp-statistics'), "<code>$eventName</code>"));
        } catch (Exception $e) {
            Ajax::error($e->getMessage(), null, $e->getCode());
        }
    }

    /**
     * Update GeoIP data for visitors with incomplete information.
     */
    public function updateCountryData()
    {
        try {
            $this->verifyAjaxRequest();
            $this->checkAdminReferrer('wp_rest', 'wps_nonce');
            $this->checkCapability('manage');

            $providerMap = [
                'maxmind' => MaxmindGeoIPProvider::class,
                'dbip'    => DbIpProvider::class
            ];

            $method   = Option::get('geoip_location_detection_method', 'maxmind');
            $method   = false;

            $provider = $providerMap[$method] ?? $providerMap['maxmind'];

            // First download/update the GeoIP database
            GeolocationFactory::downloadDatabase($provider);

            // Update GeoIP data for visitors with incomplete information
            BackgroundProcessFactory::getBackgroundProcess('update_unknown_visitor_geoip')->process();

            Ajax::success(esc_html__('GeoIP update started.', 'wp-statistics'));
        } catch (Exception $e) {
            Ajax::error(sprintf(esc_html__('GeoIP update failed: %s', 'wp-statistics'), $e->getMessage()), null, $e->getCode());
        }
    }

    /**
     * Handles AJAX requests to update the source channel data for visitors.
     */
    public function updateSourceChannelData()
    {
        try {
            $this->verifyAjaxRequest();
            $this->checkAdminReferrer('wp_rest', 'wps_nonce');
            $this->checkCapability('manage');

            BackgroundProcessFactory::getBackgroundProcess('update_visitors_source_channel')->process();

            Ajax::success(esc_html__('Source channel update started.', 'wp-statistics'));
        } catch (Exception $e) {
            Ajax::error(sprintf(esc_html__('Source channel update failed: %s', 'wp-statistics'), $e->getMessage()), null, $e->getCode());
        }
    }


    /**
     * Handles AJAX requests to anonymize IP addresses of visitors.
     */
    public function hashIps()
    {
        try {
            $this->verifyAjaxRequest();
            $this->checkAdminReferrer('wp_rest', 'wps_nonce');
            $this->checkCapability('manage');

            $result = IP::Update_HashIP_Visitor();

            Ajax::success(sprintf(esc_html__('Anonymized `%d` IP addresses.', 'wp-statistics'), $result));
        } catch (Exception $e) {
            Ajax::error(sprintf(esc_html__('IP anonymization failed: %s', 'wp-statistics'), $e->getMessage()), null, $e->getCode());
        }
    }

    /**
     * Callback function to check the database schema via AJAX.
     */
    public function recheckSchema()
    {
        try {
            $this->verifyAjaxRequest();
            $this->checkAdminReferrer('wp_rest', 'wps_nonce');
            $this->checkCapability('manage');

            $schemaCheckResult = SchemaMaintainer::check(true);
            $databaseStatus    = $schemaCheckResult['status'] ?? null;

            if ($databaseStatus !== 'success') {
                throw new Exception(esc_html__('Database issues were found. Refresh this page to show the `Repair` button.', 'wp-statistics'));
            }

            Ajax::success(esc_html__('Database looks good. No issues found.', 'wp-statistics'));
        } catch (Exception $e) {
            Ajax::error($e->getMessage(), null, $e->getCode());
        }
    }

    /**
     * Handles AJAX requests to repair database schema issues
     */
    public function repairSchema()
    {
        try {
            $this->verifyAjaxRequest();
            $this->checkAdminReferrer('wp_rest', 'wps_nonce');
            $this->checkCapability('manage');

            $schemaRepairResult = SchemaMaintainer::repair();
            $databaseStatus     = $schemaRepairResult['status'] ?? null;

            if ($databaseStatus !== 'success') {
                throw new Exception(esc_html__('Could not repair the database. Please try again.', 'wp-statistics'));
            }

            Ajax::success(esc_html__('Database schema issues were repaired.', 'wp-statistics'));
        } catch (Exception $e) {
            Ajax::error(sprintf(esc_html__('Failed to repair database schema: %s', 'wp-statistics'), $e->getMessage()), null, $e->getCode());
        }
    }

    /**
     * Handles AJAX requests to save historical data settings.
     */
    public function handleHistoricalSettingForm()
    {
        try {
            $this->verifyAjaxRequest();
            $this->checkAdminReferrer('wps_optimization');
            $this->checkCapability('manage');

            $visitors = Request::get('visitors', 0, 'number');
            $visits   = Request::get('visits', 0, 'number');

            $storedVisitors = Historical::get('visitors');
            $storedVisits   = Historical::get('visits');

            // Update historical visitors
            if ($visitors != $storedVisitors) {
                $result = Query::update('historical')
                    ->set(['value' => $visitors])
                    ->where('category', '=','visitors')
                    ->execute();

                // If no record to update, insert it
                if (!$result) {
                    Query::insert('historical')
                        ->set(['value' => $visitors, 'category' => 'visitors', 'page_id' => -1, 'uri' => '-1'])
                        ->execute();
                }
            }

            // Update historical visits
            if ($visits != $storedVisits) {
                $result = Query::update('historical')
                    ->set(['value' => $visits])
                    ->where('category', '=','visits')
                    ->execute();

                // If no record to update, insert it
                if (!$result) {
                    Query::insert('historical')
                        ->set(['value' => $visits, 'category' => 'visits', 'page_id' => -2, 'uri' => '-2'])
                        ->execute();
                }
            }

            Notice::addFlashNotice(esc_html__('Historical Data Successfully Updated.', "wp-statistics"), "success");
            Ajax::success();
        } catch (Exception $e) {
            Notice::addFlashNotice($e->getMessage(), "error");
            Ajax::error($e->getMessage(), null, $e->getCode());
        }
    }
}