<?php

# Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * Main bootstrap class for WP Statistics
 *
 * @package WP Statistics
 */
final class WP_Statistics
{
    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = array();

    /**
     * The single instance of the class.
     *
     * @var WP-Statistics
     */
    protected static $_instance = null;

    /**
     * Main WP-Statistics Instance.
     * Ensures only one instance of WP-Statistics is loaded or can be loaded.
     *
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * WP_Statistics constructor.
     */
    public function __construct()
    {
        /**
         * Check PHP Support
         */
        if (!$this->require_php_version()) {
            add_action('admin_notices', array($this, 'php_version_notice'));
            return;
        }

        /**
         * Plugin Loaded Action
         */
        add_action('plugins_loaded', array($this, 'plugin_setup'));

        /**
         * Install And Upgrade plugin
         */
        register_activation_hook(WP_STATISTICS_MAIN_FILE, array('WP_Statistics', 'install'));
        register_uninstall_hook(WP_STATISTICS_MAIN_FILE, array('WP_Statistics', 'uninstall'));

        /**
         * wp-statistics loaded
         */
        do_action('wp_statistics_loaded');
    }

    /**
     * Cloning is forbidden.
     *
     * @since 13.0
     */
    public function __clone()
    {
        \WP_STATISTICS\Helper::doing_it_wrong(__CLASS__, esc_html__('Cloning is forbidden.', 'wp-statistics'), '13.0');
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @param $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->container[$key];
    }

    /**
     * Constructors plugin Setup
     *
     * @throws Exception
     */
    public function plugin_setup()
    {
        /**
         * Load Text Domain
         */
        add_action('init', array($this, 'load_textdomain'));

        /**
         * Include Require File
         */
        $this->includes();

        /**
         * instantiate Plugin
         */
        $this->instantiate();
    }

    /**
     * Includes plugin files
     */
    public function includes()
    {

        // third-party Libraries
        require_once WP_STATISTICS_DIR . 'includes/vendor/autoload.php';

        // Utility classes.
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-db.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-timezone.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-option.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-helper.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-mail.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-menus.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-meta-box.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-admin-bar.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-rest-api.php';

        // Hits Class
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-country.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user-online.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-user-agent.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-ip.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-geoip.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-pages.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-visitor.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-historical.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-visit.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-referred.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-search-engine.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-exclusion.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-hits.php';

        // Admin classes
        if (is_admin()) {

            require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-install.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-template.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-ajax.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-dashboard.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-export.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-network.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-purge.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-assets.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-notices.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-post.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-user.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/class-wp-statistics-admin-taxonomy.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/TinyMCE/class-wp-statistics-tinymce.php';

            // Admin Pages List
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-settings.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-optimization.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-plugins.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-overview.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-online.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-hits.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-refer.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-words.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-searches.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-pages.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-visitors.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-country.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-category.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-authors.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-tags.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-browsers.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-platforms.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-top-visitors-today.php';
            require_once WP_STATISTICS_DIR . 'includes/admin/pages/class-wp-statistics-admin-page-exclusions.php';
        }

        // WordPress ShortCode and Widget
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-shortcode.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-widget.php';

        // Meta Box List
        \WP_STATISTICS\Meta_Box::includes();

        // Rest-Api
        require_once WP_STATISTICS_DIR . 'includes/api/v2/class-wp-statistics-api-hit.php';
        require_once WP_STATISTICS_DIR . 'includes/api/v2/class-wp-statistics-api-meta-box.php';

        // WordPress Cron
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-schedule.php';

        // Front Class.
        if (!is_admin()) {
            require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-frontend.php';
        }

        // WP-CLI
        if (defined('WP_CLI') && WP_CLI && WP_STATISTICS\Option::get('wp_cli') == true) {
            require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-cli.php';
        }

        // Template functions.
        include WP_STATISTICS_DIR . 'includes/template-functions.php';
    }

    /**
     * Loads the load plugin text domain code.
     */
    public function load_textdomain()
    {
        load_plugin_textdomain('wp-statistics', false, basename(WP_STATISTICS_DIR) . '/languages');
    }

    /**
     * Check PHP Version
     */
    public function require_php_version()
    {
        if (!version_compare(phpversion(), WP_STATISTICS_REQUIRE_PHP_VERSION, ">=")) {
            return false;
        }

        return true;
    }

    /**
     * Show notice about PHP version
     *
     * @return void
     */
    function php_version_notice()
    {

        $error = __('Your installed PHP Version is: ', 'wp-statistics') . PHP_VERSION . '. ';
        $error .= __('The <strong>WP-Statistics</strong> plugin requires PHP version <strong>', 'wp-statistics') . WP_STATISTICS_REQUIRE_PHP_VERSION . __('</strong> or greater.', 'wp-statistics');
        ?>
        <div class="error">
            <p><?php printf($error); ?></p>
        </div>
        <?php
    }

    /**
     * The main logging function
     *
     * @param $message
     * @uses error_log
     */
    public static function log($message)
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }
        $file = fopen(ABSPATH . "/wp-statistics.log", "a");
        fwrite($file, "\n" . date('Y-m-d h:i:s') . " :: " . $message);
        fclose($file);
    }

    /**
     * Create tables on plugin activation
     *
     * @param object $network_wide
     */
    public static function install($network_wide)
    {
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-db.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-install.php';
        $installer = new \WP_STATISTICS\Install();
        $installer->install($network_wide);
    }

    /**
     * Manage task on plugin deactivation
     *
     * @return void
     */
    public static function uninstall()
    {
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-db.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-uninstall.php';
        new \WP_STATISTICS\Uninstall();
    }

    /**
     * Instantiate the classes
     *
     * @return void
     * @throws Exception
     */
    public function instantiate()
    {

        # Get Country Codes
        $this->container['country_codes'] = \WP_STATISTICS\Country::getList();

        # Get User Detail
        $this->container['user_id'] = \WP_STATISTICS\User::get_user_id();

        # Set Options
        $this->container['option'] = new \WP_STATISTICS\Option();

        # User IP
        $this->container['ip'] = \WP_STATISTICS\IP::getIP();

        # User Agent
        $this->container['agent'] = \WP_STATISTICS\UserAgent::getUserAgent();

        # User Online
        $this->container['users_online'] = new \WP_STATISTICS\UserOnline();

        # Visitor
        $this->container['visitor'] = new \WP_STATISTICS\Visitor();

        # Referer
        $this->container['referred'] = \WP_STATISTICS\Referred::get();
    }
}