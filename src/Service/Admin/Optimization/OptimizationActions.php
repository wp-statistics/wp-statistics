<?php
namespace WP_Statistics\Service\Admin\Optimization;

use Exception;
use WP_Statistics\Components\Ajax;
use WP_STATISTICS\DB;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\EventsModel;
use WP_STATISTICS\Option;
use WP_STATISTICS\Purge;
use WP_Statistics\Traits\AjaxUtilityTrait;
use WP_Statistics\Utils\Query;
use WP_Statistics\Utils\Request;

class OptimizationActions
{
    use AjaxUtilityTrait;

    public function register()
    {
        Ajax::register('purge_data', [$this, 'purgeOldData'], false);
        Ajax::register('purge_visitors_by_hits', [$this, 'purgeVisitorsByHits'], false);
        Ajax::register('purge_visitors_by_ip', [$this, 'purgeVisitorsByIp'], false);
        Ajax::register('purge_visitors_by_browser', [$this, 'purgeVisitorsByBrowser'], false);
        Ajax::register('purge_visitors_by_platform', [$this, 'purgeVisitorsByPlatform'], false);
        Ajax::register('clear_user_ids', [$this, 'clearUserIds'], false);
        Ajax::register('clear_ua_strings', [$this, 'clearUAStrings'], false);
        Ajax::register('delete_word_count_data', [$this, 'deleteWordCountData'], false);
        Ajax::register('query_params_cleanup', [$this, 'cleanUpQueryParams'], false);
        Ajax::register('event_data_cleanup', [$this, 'cleanUpEventData'], false);
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

            $result = Purge::purge_data($purgeDays);

            Ajax::success($result);
        } catch (Exception $e) {
            Ajax::error($e->getMessage(), null, $e->getCode());
        }
    }

    /**
     * Setup an AJAX action to purge visitors by hits.
     */
    public function purgeVisitorsByHits()
    {
        try {
            $this->verifyAjaxRequest();
            $this->checkAdminReferrer('wp_rest', 'wps_nonce');
            $this->checkCapability('manage');

            $hits = Request::get('purge-hits', 10, 'number');

            if ($hits < 10) {
                throw new Exception(esc_html__('View count must be 10 or more! Please enter a valid number and try again.', 'wp-statistics'), 400);
            }

            $result = Query::delete('visitor')
                ->where('hits', '>', $hits)
                ->execute();

            if ($result === false) {
                global $wpdb;
                throw new Exception($wpdb->last_error, 500);
            }

            Ajax::success(sprintf(esc_html__('%s Records Successfully Purged.', 'wp-statistics'), "<code>$result</code>"));
        } catch (Exception $e) {
            Ajax::error($e->getMessage(), null, $e->getCode());
        }
    }

    /**
     * Setup an AJAX action to purge visitors by IP.
     */
    public function purgeVisitorsByIp()
    {
        try {
            $this->verifyAjaxRequest();
            $this->checkAdminReferrer('wp_rest', 'wps_nonce');
            $this->checkCapability('manage');

            $ip = Request::get('ip-address');

            if (empty($ip)) {
                throw new Exception(esc_html__('Invalid IP address provided! Please enter a valid IP and try again.', 'wp-statistics'), 400);
            }

            $result = Query::delete('visitor')
                ->where('ip', '=', $ip)
                ->execute();

            if ($result === false) {
                global $wpdb;
                throw new Exception($wpdb->last_error, 500);
            }

            Ajax::success(sprintf(esc_html__('Successfully deleted %s IP data.', 'wp-statistics'), "<code>$ip</code>"));
        } catch (Exception $e) {
            Ajax::error($e->getMessage(), null, $e->getCode());
        }
    }

    /**
     * Setup an AJAX action to purge visitors by browser.
     */
    public function purgeVisitorsByBrowser()
    {
        try {
            $this->verifyAjaxRequest();
            $this->checkAdminReferrer('wp_rest', 'wps_nonce');
            $this->checkCapability('manage');

            $browser = Request::get('agent-name');

            if (empty($browser)) {
                throw new Exception(esc_html__('Invalid agent name provided! Please enter a valid agent and try again.', 'wp-statistics'), 400);
            }

            $result = Query::delete('visitor')
                ->where('agent', '=', $browser)
                ->execute();

            if ($result === false) {
                global $wpdb;
                throw new Exception($wpdb->last_error, 500);
            }

            Ajax::success(sprintf(esc_html__('Successfully deleted %s agent data.', 'wp-statistics'), "<code>$browser</code>"));
        } catch (Exception $e) {
            Ajax::error($e->getMessage(), null, $e->getCode());
        }
    }

    /**
     * Setup an AJAX action to purge visitors by platform.
     */
    public function purgeVisitorsByPlatform()
    {
        try {
            $this->verifyAjaxRequest();
            $this->checkAdminReferrer('wp_rest', 'wps_nonce');
            $this->checkCapability('manage');

            $platform = Request::get('platform-name');

            if (empty($platform)) {
                throw new Exception(esc_html__('Invalid platform name provided! Please enter a valid platform and try again.', 'wp-statistics'), 400);
            }

            $result = Query::delete('visitor')
                ->where('platform', '=', $platform)
                ->execute();

            if ($result === false) {
                global $wpdb;
                throw new Exception($wpdb->last_error, 500);
            }

            Ajax::success(sprintf(esc_html__('Successfully deleted %s platform data.', 'wp-statistics'), "<code>$platform</code>"));
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
                global $wpdb;
                throw new Exception($wpdb->last_error, 500);
            }

            Ajax::success(esc_html__('Successfully deleted User ID data.', 'wp-statistics'));
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
                global $wpdb;
                throw new Exception($wpdb->last_error, 500);
            }

            Ajax::success(esc_html__('Successfully deleted user agent strings data.', 'wp-statistics'));
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
                global $wpdb;
                throw new Exception($wpdb->last_error, 500);
            }

            Ajax::success(esc_html__('Successfully deleted word count data.', 'wp-statistics'));
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

            Ajax::success(esc_html__('Successfully removed query string parameter data from `pages` table.', 'wp-statistics'));
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
                throw new Exception(esc_html__('Please enter a valid event name and try again.', 'wp-statistics'), 400);
            }

            $eventsModel = new EventsModel();
            $result      = $eventsModel->deleteEvents(['event_name' => $eventName]);

            if ($result === false) {
                global $wpdb;
                throw new Exception($wpdb->last_error, 500);
            }

            Ajax::success(esc_html__('Successfully removed event data from `events` table.', 'wp-statistics'));
        } catch (Exception $e) {
            Ajax::error($e->getMessage(), null, $e->getCode());
        }
    }
}