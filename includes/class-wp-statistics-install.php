<?php

namespace WP_STATISTICS;

use WP_Statistics\Service\Database\Managers\TableHandler;

/**
 * DEPRECATED: This class is not supported anymore. Please do not use it in your code.
 *
 * @deprecated This class is deprecated. Use Core operations instead.
 */
class Install
{
    /**
     * Install
     *
     * @param $network_wide
     */
    public function install($network_wide)
    {
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-option.php';
        require_once WP_STATISTICS_DIR . 'src/Service/Database/DatabaseManager.php';
        require_once WP_STATISTICS_DIR . 'src/Service/Database/Managers/TransactionHandler.php';
        require_once WP_STATISTICS_DIR . 'src/Service/Database/AbstractDatabaseOperation.php';
        require_once WP_STATISTICS_DIR . 'src/Service/Database/Operations/AbstractTableOperation.php';
        require_once WP_STATISTICS_DIR . 'src/Service/Database/Operations/Create.php';
        require_once WP_STATISTICS_DIR . 'src/Service/Database/Operations/Inspect.php';
        require_once WP_STATISTICS_DIR . 'src/Service/Database/DatabaseFactory.php';
        require_once WP_STATISTICS_DIR . 'src/Service/Database/Schema/Manager.php';
        require_once WP_STATISTICS_DIR . 'src/Service/Database/Managers/TableHandler.php';
        require_once WP_STATISTICS_DIR . 'src/Core/AbstractCore.php';
        require_once WP_STATISTICS_DIR . 'src/Core/CoreFactory.php';

        global $wpdb;


        if (is_multisite() && $network_wide) {
            $blog_ids = $wpdb->get_col("SELECT `blog_id` FROM $wpdb->blogs");
            foreach ($blog_ids as $blog_id) {

                switch_to_blog($blog_id);
                $this->checkIsFresh();
                TableHandler::createAllTables();
                restore_current_blog();
            }
        } else {
            $this->checkIsFresh();
            TableHandler::createAllTables();
        }

        $this->markBackgroundProcessAsInitiated();

        // Create Default Option in Database
        self::create_options();

        // Set Version information
        update_option('wp_statistics_plugin_version', WP_STATISTICS_VERSION);
    }

    /**
     * Checks whether the plugin is a fresh installation.
     *
     * @return void
     */
    private function checkIsFresh()
    {
        $version = get_option('wp_statistics_plugin_version');

        if (empty($version)) {
            update_option('wp_statistics_is_fresh', true);
        } else {
            update_option('wp_statistics_is_fresh', false);
        }

        $installationTime = get_option('wp_statistics_installation_time');
        if (empty($installationTime)) {
            update_option('wp_statistics_installation_time', time());
        }
    }

    /**
     * Determines if the plugin is marked as freshly installed.
     *
     * @return bool.
     */
    public static function isFresh()
    {
        $isFresh = get_option('wp_statistics_is_fresh', false);

        if ($isFresh) {
            return true;
        }

        return false;
    }

    /**
     * Checks background processes during a fresh installation.
     *
     * @return void
     */
    private function markBackgroundProcessAsInitiated()
    {
        Option::deleteOptionGroup('data_migration_process_started', 'jobs');

        if (!self::isFresh()) {
            return;
        }

        Option::saveOptionGroup('update_source_channel_process_initiated', true, 'jobs');
        Option::saveOptionGroup('update_geoip_process_initiated', true, 'jobs');
        Option::saveOptionGroup('schema_migration_process_started', true, 'jobs');
        Option::saveOptionGroup('update_source_channel_process_initiated', true, 'jobs');
        Option::saveOptionGroup('table_operations_process_initiated', true, 'jobs');
        Option::saveOptionGroup('word_count_process_initiated', true, 'jobs');
    }

    public static function delete_duplicate_data()
    {
        global $wpdb;

        // Define the table name
        $table_name = DB::table('visitor_relationships');

        // Start a transaction
        $wpdb->query('START TRANSACTION');

        // Execute the delete query
        $wpdb->query("DELETE v1 FROM `" . $table_name . "` AS v1 INNER JOIN `" . $table_name . "` AS v2 WHERE v1.ID > v2.ID AND v1.visitor_id = v2.visitor_id AND v1.page_id = v2.page_id AND DATE(v1.date) = DATE(v2.date)");

        // If no errors, commit the transaction
        $wpdb->query('COMMIT');
    }

    /**
     * Load WordPress dbDelta Function
     */
    public static function load_dbDelta()
    {
        if (!function_exists('dbDelta')) {
            require(ABSPATH . 'wp-admin/includes/upgrade.php');
        }
    }

    /**
     * Create Default Option
     */
    public static function create_options()
    {

        //Require File For Create Default Option
        require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-template.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-option.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-helper.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user-online.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-visitor.php';

        // Create Default Option
        $exist_option = get_option(Option::$opt_name);
        if ($exist_option === false || (isset($exist_option) and !is_array($exist_option))) {
            update_option(Option::$opt_name, Option::defaultOption());
        }
    }

    /**
     * Creating Table for New Blog in WordPress
     *
     * @param $blog_id
     */
    public function add_table_on_create_blog($blog_id)
    {
        if (is_plugin_active_for_network(plugin_basename(WP_STATISTICS_MAIN_FILE))) {
            $options = get_option(Option::$opt_name);
            switch_to_blog($blog_id);
            TableHandler::createAllTables();
            update_option(Option::$opt_name, $options);
            restore_current_blog();
        }
    }

    /**
     * Remove Table On Delete Blog WordPress
     *
     * @param $tables
     * @return array
     */
    public function remove_table_on_delete_blog($tables)
    {
        $tables[] = array_merge($tables, DB::table('all'));
        return $tables;
    }

    /**
     * Add a WordPress plugin page and rating links to the meta information to the plugin list.
     *
     * @param string $links Links
     * @param string $file File
     * @return string
     */
    public function add_meta_links($links, $file)
    {
        if ($file == plugin_basename(WP_STATISTICS_MAIN_FILE)) {
            $plugin_url = 'https://wordpress.org/plugins/wp-statistics/';

            $links[]  = '<a href="' . $plugin_url . '" target="_blank" title="' . __('Click here to visit the plugin on WordPress.org', 'wp-statistics') . '">' . __('Visit WordPress.org page', 'wp-statistics') . '</a>';
            $rate_url = 'https://wordpress.org/support/plugin/wp-statistics/reviews/?rate=5#new-post';
            $links[]  = '<a href="' . $rate_url . '" target="_blank" title="' . __('Click here to rate and review this plugin on WordPress.org', 'wp-statistics') . '">' . __('Rate this plugin', 'wp-statistics') . '</a>';
        }

        return $links;
    }

    /**
     * Update WordPress Page Type for older wp-statistics Version
     *
     * @since 12.6
     *
     * -- List Methods ---
     * init_page_type_updater        -> define WordPress Hook
     * get_require_number_update     -> Get number of rows that require update page type
     * is_require_update_page        -> Check Wp-statistics require update page table
     * get_page_type_by_obj          -> Get Page Type by information
     * @todo, this legacy functionality should move to Background Processing
     */
    public static function init_page_type_updater()
    {

        # Check Require Admin Process
        if (self::is_require_update_page() === true) {

            # Add Admin Notice
            add_action('admin_notices', function () {
                echo '<div class="notice notice-info is-dismissible" id="wp-statistics-update-page-area" style="display: none;">';
                echo '<p style="margin-top: 17px; float:' . (is_rtl() ? 'right' : 'left') . '">';
                echo __('WP Statistics database requires upgrade.', 'wp-statistics'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                echo '</p>';
                echo '<div style="float:' . (is_rtl() ? 'left' : 'right') . '">';
                echo '<button type="button" id="wps-upgrade-db" class="button button-primary" style="padding: 20px;line-height: 0px;box-shadow: none !important;border: 0px !important;margin: 10px 0;"/>' . esc_html__('Upgrade Database', 'wp-statistics') . '</button>';
                echo '</div>';
                echo '<div style="clear:both;"></div>';
                echo '</div>';
            });

            # Add Script
            add_action('admin_footer', function () {
                ?>
                <script>
                    jQuery(document).ready(function () {

                        // Check Page is complete Loaded
                        jQuery(window).load(function () {
                            jQuery("#wp-statistics-update-page-area").fadeIn(2000);
                            jQuery("#wp-statistics-update-page-area button.notice-dismiss").hide();
                        });

                        // Update Page type function
                        function wp_statistics_update_page_type() {

                            //Complete Progress
                            let wps_end_progress = `<div id="wps_end_process" style="display:none;">`;
                            wps_end_progress += `<p>`;
                            wps_end_progress += `<?php esc_html__('Database Upgrade Completed Successfully!', 'wp-statistics'); ?>`;
                            wps_end_progress += `</p>`;
                            wps_end_progress += `</div>`;
                            wps_end_progress += `<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>`;

                            //new Ajax Request
                            jQuery.ajax({
                                url: ajaxurl,
                                type: 'get',
                                dataType: "json",
                                cache: false,
                                data: {
                                    '_wpnonce': '<?php echo esc_js(wp_create_nonce('update_post_type')); ?>',
                                    'action': 'wp_statistics_update_post_type_db',
                                    'number_all': <?php echo esc_html(self::get_require_number_update()); ?>
                                },
                                success: function (data) {
                                    if (data.process_status === "complete") {

                                        // Get Process Area
                                        let wps_notice_area = jQuery("#wp-statistics-update-page-area");
                                        //Add Html Content
                                        wps_notice_area.html(wps_end_progress);
                                        //Fade in content
                                        jQuery("#wps_end_process").fadeIn(2000);
                                        //enable demiss button
                                        wps_notice_area.removeClass('notice-info').addClass('notice-success');
                                    } else {

                                        //Get number Process
                                        jQuery("span#wps_num_page_process").html(data.number_process);
                                        //Get process Percentage
                                        jQuery("progress#wps_upgrade_html_progress").attr("value", data.percentage);
                                        jQuery("span#wps_num_percentage").html(data.percentage);
                                        //again request
                                        wp_statistics_update_page_type();
                                    }
                                },
                                error: function () {
                                    jQuery("#wp-statistics-update-page-area").html('<p><?php esc_html_e('Error During Operation. Please Refresh the Page.', 'wp-statistics'); ?></p>');
                                }
                            });
                        }

                        //Click Start Progress
                        jQuery(document).on('click', 'button#wps-upgrade-db', function (e) {
                            e.preventDefault();

                            // Added Progress Html
                            let wps_progress = `<div id="wps_process_upgrade" style="display:none;"><p>`;
                            wps_progress += `<?php esc_html_e('Please don\'t close the browser window until the database operation was completed.', 'wp-statistic'); ?>`;
                            wps_progress += `</p><p><b>`;
                            wps_progress += `<?php echo esc_html_e('Item processed', 'wp-statistics'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>`;
                            wps_progress += ` : <span id="wps_num_page_process">0</span> / <?php echo esc_html(number_format(self::get_require_number_update())); ?> &nbsp;<span class="wps-text-warning">(<span id="wps_num_percentage">0</span>%)</span></b></p>`;
                            wps_progress += '<p><progress id="wps_upgrade_html_progress" value="0" max="100" style="height: 20px;width: 100%;"></progress></p></div>';

                            // set new Content
                            jQuery("#wp-statistics-update-page-area").html(wps_progress);
                            jQuery("#wps_process_upgrade").fadeIn(2000);

                            // Run WordPress Ajax Updator
                            wp_statistics_update_page_type();
                        });

                        //Remove Notice event
                        jQuery(document).on('click', '#wp-statistics-update-page-area button.notice-dismiss', function (e) {
                            e.preventDefault();
                            jQuery("#wp-statistics-update-page-area").fadeOut('normal');
                        });
                    });
                </script>
                <?php
            });

        }

        # Add Admin Ajax Process
        add_action('wp_ajax_wp_statistics_update_post_type_db', function () {
            global $wpdb;

            # Check nonce
            check_ajax_referer('update_post_type');

            # Create Default Obj
            $return = array('process_status' => 'complete', 'number_process' => 0, 'percentage' => 0);

            # Check is Ajax WordPress
            if (defined('DOING_AJAX') && DOING_AJAX && User::Access('manage')) {

                # Check Status Of Process
                if (self::is_require_update_page() === true) {

                    # Number Process Per Query
                    $number_per_query = 80;

                    # Check Number Process
                    $number_process = self::get_require_number_update();
                    $i              = 0;
                    if ($number_process > 0) {

                        # Start Query
                        $query = $wpdb->get_results(
                            $wpdb->prepare("SELECT * FROM `" . DB::table('pages') . "` WHERE `type` = '' ORDER BY `page_id` DESC LIMIT 0,%d", $number_per_query),
                            ARRAY_A);
                        foreach ($query as $row) {

                            # Get Page Type
                            $page_type = self::get_page_type_by_obj($row['id'], $row['uri']);

                            # Update Table
                            $wpdb->update(
                                DB::table('pages'),
                                array(
                                    'type' => $page_type
                                ),
                                array('page_id' => $row['page_id'])
                            );

                            $i++;
                        }

                        # Sanitize the data
                        $number_all = sanitize_text_field($_GET['number_all']);

                        if ($number_all > $number_per_query) {
                            # calculate number process
                            $return['number_process'] = $number_all - ($number_process - $i);

                            # Calculate Per
                            $return['percentage'] = round(($return['number_process'] / $number_all) * 100);

                            # Set Process
                            $return['process_status'] = 'incomplete';

                        } else {

                            $return['number_process'] = $number_all;
                            $return['percentage']     = 100;
                            update_option('wp_statistics_update_page_type', 'yes');
                        }
                    }
                } else {

                    # Closed Process
                    update_option('wp_statistics_update_page_type', 'yes');
                }

                # Export Data
                wp_send_json($return);
                exit;
            }
        });


    }

    public static function get_require_number_update()
    {
        global $wpdb;
        $pagesTable = DB::table('pages');

        if (!DB::ExistTable($pagesTable)) {
            return 0;
        }

        return $wpdb->get_var("SELECT COUNT(*) FROM `{$pagesTable}` WHERE `type` = ''");
    }

    public static function is_require_update_page()
    {

        # require update option name
        $opt_name = 'wp_statistics_update_page_type';

        # Check exist option
        $get_opt = get_option($opt_name);
        if (!empty($get_opt)) {
            return false;
        }

        # Check number require row
        if (self::get_require_number_update() > 0) {
            return true;
        }

        return false;
    }

    public static function get_page_type_by_obj($obj_ID, $page_url)
    {

        //Default page type
        $page_type = 'unknown';

        //check if Home Page
        if ($page_url == "/") {
            return 'home';

        } else {

            // Page url
            $page_url = ltrim($page_url, "/");
            $page_url = trim(get_bloginfo('url'), "/") . "/" . $page_url;

            // Check Page Path is exist
            $exist_page = url_to_postid($page_url);

            //Check Post Exist
            if ($exist_page > 0) {

                # Get Post Type
                $p_type = get_post_type($exist_page);

                # Check Post Type
                if ($p_type == "product") {
                    $page_type = 'product';
                } elseif ($p_type == "page") {
                    $page_type = 'page';
                } elseif ($p_type == "attachment") {
                    $page_type = 'attachment';
                } else {
                    $page_type = 'post';
                }

            } else {

                # Check is Term
                $term = get_term($obj_ID);
                if (is_wp_error(get_term_link($term)) === true) {
                    //Don't Stuff
                } else {
                    //Which Taxonomy
                    $taxonomy = $term->taxonomy;

                    //Check Url is contain
                    $term_link = get_term_link($term);
                    $term_link = ltrim(str_ireplace(get_bloginfo('url'), "", $term_link), "/");
                    if (stripos($page_url, $term_link) === false) {
                        //Return Unknown
                    } else {
                        //Check Type of taxonomy
                        if ($taxonomy == "category") {
                            $page_type = 'category';
                        } elseif ($taxonomy == "post_tag") {
                            $page_type = 'post_tag';
                        } else {
                            $page_type = 'tax_' . $taxonomy;
                        }
                    }

                }
            }
        }

        return $page_type;
    }
}

new Install;

