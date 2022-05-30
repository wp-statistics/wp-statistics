<?php

namespace WP_STATISTICS;

class optimization_page
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

        Admin_Template::get_template(array('layout/header', 'layout/tabs-optimization', 'layout/title-after', 'optimization', 'layout/footer'), $args);
    }

    public function processForms()
    {
        global $wpdb;

        // Check Access Level
        if (Menus::in_page('optimization') and !User::Access('manage')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        // Check Wp Nonce and Require Field
        if (isset($_POST['submit']) && (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'wps_optimization_nonce'))) {
            return;
        }

        // Update All GEO IP Country
        if (isset($_POST['submit'], $_POST['populate-submit']) && intval($_POST['populate-submit']) == 1) {
            $result = GeoIP::Update_GeoIP_Visitor();

            // Show Notice
            Helper::addAdminNotice($result['data'], ($result['status'] === false ? "error" : "success"));
        }

        // Check Hash IP Update
        if (isset($_POST['submit'], $_POST['hash-ips-submit']) and intval($_POST['hash-ips-submit']) == 1) {
            IP::Update_HashIP_Visitor();

            // Show Notice
            Helper::addAdminNotice(__('IP Addresses replaced with hash values.', "wp-statistics"), "success");
        }

        // Re-install All DB Table
        if (isset($_POST['submit'], $_POST['install-submit']) and intval($_POST['install-submit']) == 1) {
            Install::create_table(false);

            // Show Notice
            Helper::addAdminNotice(__('Install routine complete.', "wp-statistics"), "success");
        }

        // Optimize Tables
        if (isset($_POST['submit'], $_POST['optimize-database-submit']) and !empty($_POST['optimize-table'])) {
            $tbl = trim(sanitize_text_field($_POST['optimize-table']));
            if ($tbl == "all") {
                $tables = array_filter(array_values(DB::table('all')));
            } else {
                $tables = array_filter(array(DB::table($tbl)));
            }

            if (!empty($tables)) {
                $notice = '';
                $okay   = true;

                // Use wp-admin/maint/repair.php
                foreach ($tables as $table) {
                    $check = $wpdb->get_row("CHECK TABLE $table");

                    if ('OK' === $check->Msg_text) {
                        /* translators: %s: Table name. */
                        $notice .= sprintf(__('The %s table is okay.', "wp-statistics"), "<code>$table</code>");
                        $notice .= '<br />';
                    } else {
                        $notice .= sprintf(__('The %1$s table is not okay. It is reporting the following error: %2$s. WordPress will attempt to repair this table&hellip;', "wp-statistics"), "<code>$table</code>", "<code>$check->Msg_text</code>");
                        $repair = $wpdb->get_row("REPAIR TABLE $table");

                        $notice .= '<br />';
                        if ('OK' === $repair->Msg_text) {
                            $notice .= sprintf(__('Successfully repaired the %s table.', "wp-statistics"), "<code>$table</code>");
                        } else {
                            $notice           .= sprintf(__('Failed to repair the %1$s table. Error: %2$s', "wp-statistics"), "<code>$table</code>", "<code>$check->Msg_text</code>") . '<br />';
                            $problems[$table] = $check->Msg_text;
                            $okay             = false;
                        }
                    }

                    if ($okay) {
                        $check = $wpdb->get_row("ANALYZE TABLE $table");
                        if ('Table is already up to date' === $check->Msg_text) {
                            $notice .= sprintf(__('The %s table is already optimized.', "wp-statistics"), "<code>$table</code>");
                            $notice .= '<br />';
                        } else {
                            $check = $wpdb->get_row("OPTIMIZE TABLE $table");
                            if ('OK' === $check->Msg_text || 'Table is already up to date' === $check->Msg_text) {
                                $notice .= sprintf(__('Successfully optimized the %s table.', 'wp-statistics'), "<code>$table</code>");
                                $notice .= '<br />';
                            } else {
                                $notice .= sprintf(__('The %1$s table does not support optimize, doing recreate + analyze instead.'), "<code>$table</code>");
                                $notice .= '<br />';
                            }
                        }
                    }
                }

                // Show Notice
                Helper::addAdminNotice($notice, "info");
            }
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

            // Historical Visits
            if (isset($_POST['wps_historical_visits'])) {
                // Update DB
                $result = $wpdb->update($historical_table, array('value' => sanitize_text_field($_POST['wps_historical_visits'])), array('category' => 'visits'));

                if ($result == 0) {
                    $result = $wpdb->insert($historical_table, array('value' => sanitize_text_field($_POST['wps_historical_visits']), 'category' => 'visits', 'page_id' => -2, 'uri' => '-2'));
                }
            }

            // Show Notice
            Helper::addAdminNotice(__('Updated Historical Values.', "wp-statistics"), "success");
        }
    }
}

new optimization_page;
