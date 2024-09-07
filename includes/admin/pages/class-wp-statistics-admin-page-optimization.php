<?php

namespace WP_STATISTICS;

use WP_Statistics\Async\BackgroundProcessFactory;
use WP_Statistics\Components\Singleton;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;

class optimization_page extends Singleton
{

    public function __construct()
    {
        // Optimize and Repair Database MySQL
        add_action('admin_init', array($this, 'processForms'));
    }

    /**
     * This function displays the HTML for the settings page.
     */
    public static function view()
    {

        // Add Class inf
        $args['class'] = 'wp-statistics-settings';

        // Get List Table
        $args['list_table'] = DB::table('all');
        $args['result']     = DB::getTableRows();

        Admin_Template::get_template(array('layout/header', 'layout/title-after', 'optimization', 'layout/footer'), $args);
    }

    public function processForms()
    {
        global $wpdb;

        // Check Access Level
        if (Menus::in_page('optimization') and !User::Access('manage')) {
            wp_die(__('You do not have sufficient permissions to access this page.')); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	
        }

        // Check Wp Nonce and Require Field
        if (isset($_POST['submit']) && (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'wps_optimization_nonce'))) {
            return;
        }

        // Update All GEO IP Country
        if (isset($_POST['submit'], $_POST['populate-submit']) && intval($_POST['populate-submit']) == 1) {
            // First download/update the GeoIP database
            GeoIP::download('update');

            // Update GeoIP data for visitors with incomplete information
            BackgroundProcessFactory::batchUpdateIncompleteGeoIpForVisitors();

            // Show Notice
            Notice::addFlashNotice(__('GeoIP update for incomplete visitors initiated successfully.', 'wp-statistics'), 'success');
        }

        // Check Hash IP Update
        if (isset($_POST['submit'], $_POST['hash-ips-submit']) and intval($_POST['hash-ips-submit']) == 1) {
            $result = IP::Update_HashIP_Visitor();

            // Show Notice
            Notice::addFlashNotice(sprintf(__('Successfully anonymized <b>%d</b> IP addresses using hash values.', 'wp-statistics'), $result), 'success');
        }

        // Update Historical Value
        if (isset($_POST['submit'], $_POST['historical-submit']) and intval($_POST['historical-submit']) == 1) {
            $historical_table = DB::table('historical');

            // Historical Visitors
            if (isset($_POST['wps_historical_visitors'])) {

                // Update DB
                $result = $wpdb->update($historical_table, array('value' => sanitize_text_field($_POST['wps_historical_visitors'])), array('category' => 'visitors'));
                if ($result == 0) {
                    $result = $wpdb->insert($historical_table, array('value' => sanitize_text_field($_POST['wps_historical_visitors']), 'category' => 'visitors', 'page_id' => -1, 'uri' => '-1'));
                }
            }

            // Historical Views
            if (isset($_POST['wps_historical_visits'])) {
                // Update DB
                $result = $wpdb->update($historical_table, array('value' => sanitize_text_field($_POST['wps_historical_visits'])), array('category' => 'visits'));

                if ($result == 0) {
                    $result = $wpdb->insert($historical_table, array('value' => sanitize_text_field($_POST['wps_historical_visits']), 'category' => 'visits', 'page_id' => -2, 'uri' => '-2'));
                }
            }

            // Show Notice
            Notice::addFlashNotice(__('Historical Data Successfully Updated.', "wp-statistics"), "success");
        }
    }
}

optimization_page::instance();
