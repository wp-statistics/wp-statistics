<?php
namespace WP_Statistics\Service\Admin\Optimization;

use WP_Statistics\Abstracts\BasePage;
use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\DB;
use WP_STATISTICS\Menus;
use WP_Statistics\Service\Admin\NoticeHandler\Notice;
use WP_STATISTICS\User;

class OptimizationPage extends BasePage
{
    protected $pageSlug = 'optimization';

    public function __construct()
    {
        parent::__construct();

        add_action('admin_init', [$this, 'processForms']);
    }

    public function view()
    {
        $args = [
            'class'  => 'wp-statistics-settings',
            'title'  => esc_html__('Optimization', 'wp-statistics'),
            'tables' => DB::getTableRows(),
        ];

        Admin_Template::get_template(['layout/header', 'optimization', 'layout/footer'], $args);
    }

    /**
     * @todo Refactor this function to store data via AJAX
     */
    public function processForms()
    {
        if (!Menus::in_page('optimization')) {
            return;
        }

        global $wpdb;

        // Check Access Level
        if (!User::Access('manage')) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.'));
        }

        // Check Wp Nonce and Require Field
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'wps_optimization_nonce')) {
            return;
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

            Notice::addFlashNotice(esc_html__('Historical Data Successfully Updated.', "wp-statistics"), "success");
        }
    }
}