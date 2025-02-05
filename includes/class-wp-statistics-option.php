<?php

namespace WP_STATISTICS;

class Option
{
    /**
     * Get WP Statistics Basic Option name
     *
     * @var string
     */
    public static $opt_name = 'wp_statistics';

    /**
     * WP Statistics Option name Prefix
     *
     * @var string
     */
    public static $opt_prefix = 'wps_';

    /**
     * Get Complete Option name with WP Statistics Prefix
     *
     * @param $name
     * @return mixed
     */
    public static function get_option_name($name)
    {
        return self::$opt_prefix . $name;
    }

    /**
     * WP Statistics Default Option
     *
     * @return array
     */
    public static function defaultOption()
    {

        $options = array(
            'robotlist'                       => Helper::get_robots_list(),
            'query_params_allow_list'         => Helper::get_default_query_params_allow_list('string'),
            'anonymize_ips'                   => true,
            'hash_ips'                        => true,
            'geoip'                           => true,
            'useronline'                      => true,
            'pages'                           => true,
            'menu_bar'                        => true,
            'coefficient'                     => Visitor::getCoefficient(),
            'email_list'                      => get_bloginfo('admin_email'),
            'use_cache_plugin'                => true,
            'time_report'                     => '0',
            'send_report'                     => 'mail',
            'geoip_license_type'              => 'js-deliver',
            'geoip_license_key'               => '',
            'content_report'                  => '',
            'email_free_content_header'       => '',
            'email_free_content_footer'       => '',
            'update_geoip'                    => true,
            'privacy_audit'                   => true,
            'store_ua'                        => false,
            'consent_level_integration'       => 'disabled',
            'anonymous_tracking'              => false,
            'do_not_track'                    => false,
            'exclude_administrator'           => true,
            'referrerspam'                    => true,
            'map_type'                        => 'jqvmap',
            'ip_method'                       => 'sequential',
            'exclude_loginpage'               => true,
            'exclude_404s'                    => false,
            'exclude_feeds'                   => true,
            'schedule_dbmaint'                => true,
            'schedule_dbmaint_days'           => '180',
            'charts_previous_period'          => true,
            'attribution_model'               => 'first-touch',
            'geoip_location_detection_method' => 'maxmind'
        );

        return $options;
    }

    /**
     * Get WP Statistics All Options
     *
     * @return mixed
     */
    public static function getOptions()
    {
        $get_opt = get_option(self::$opt_name);
        if (!isset($get_opt) || !is_array($get_opt)) {
            return array();
        }

        return $get_opt;
    }

    /**
     * Saves the current options array to the database.
     *
     * @param $options
     */
    public static function save_options($options)
    {
        update_option(self::$opt_name, $options);
    }

    /**
     * Get the only Option that we want
     *
     * @param $option_name
     * @param null $default
     * @return string
     */
    public static function get($option_name, $default = null)
    {

        // Get all Options
        $options = self::getOptions();

        // if the option isn't set yet, return the $default if it exists, otherwise FALSE.
        if (!array_key_exists($option_name, $options)) {
            if (isset($default)) {
                return $default;
            } else {
                return false;
            }
        }

        /**
         * Filters a For Return WP Statistics Option
         *
         * @param string $option Option name.
         * @param string $value Option Value.
         * @example add_filter('wp_statistics_option_coefficient', function(){ return 5; });
         */
        return apply_filters("wp_statistics_option_{$option_name}", $options[$option_name]);
    }

    /**
     * Update WP Statistics Option
     *
     * @param $option
     * @param $value
     */
    public static function update($option, $value)
    {

        // Get All Option
        $options = self::getOptions();

        // Store the value in the array.
        $options[$option] = $value;

        // Write the array to the database.
        update_option(self::$opt_name, $options);
    }

    /**
     * Get WP Statistics User Meta
     *
     * @param      $option
     * @param null $default
     * @return bool|null
     */
    public static function getUserOption($option, $default = null)
    {

        // If the user id has not been set return FALSE.
        if (User::get_user_id() == 0) {
            return false;
        }

        // Check User Exist
        $user_options = get_user_meta(User::get_user_id(), self::$opt_name, true);
        $user_options = (is_array($user_options) ? $user_options : array());

        // if the option isn't set yet, return the $default if it exists, otherwise FALSE.
        if (isset($user_options) and !array_key_exists($option, $user_options)) {
            if (isset($default)) {
                return $default;
            } else {
                return false;
            }
        }

        // Return the option.
        return (isset($user_options[$option]) ? $user_options[$option] : false);
    }

    /**
     * Mimics WordPress's update_user_meta() function
     * But uses the array instead of individual options.
     *
     * @param $option
     * @param $value
     *
     * @return bool
     */
    public static function update_user_option($option, $value)
    {
        // If the user id has not been set return FALSE.
        if (User::get_user_id() == 0) {
            return false;
        }

        // Get All User Options
        $user_options = get_user_meta(User::get_user_id(), self::$opt_name, true);
        $user_options = (is_array($user_options) ? $user_options : array());

        // Store the value in the array.
        $user_options[$option] = $value;

        // Write the array to the database.
        update_user_meta(User::get_user_id(), self::$opt_name, $user_options);
    }

    /**
     * Check WP-statistics Option Require
     *
     * @param array $item
     * @param string $condition_key
     * @return array|bool
     */
    public static function check_option_require($item = array(), $condition_key = 'require')
    {

        // Default is True
        $condition = true;

        // Check Require Params
        if (array_key_exists('require', $item)) {
            foreach ($item[$condition_key] as $if => $value) {

                // Check Type of Condition
                if (($value === true and !Option::get($if)) || ($value === false and Option::get($if))) {
                    $condition = false;
                    break;
                }
            }
        }

        return $condition;
    }

    /**
     * Get Email For Send notification
     *
     * @return string
     */
    public static function getEmailNotification()
    {
        if (Option::get('email_list') == '') {
            Option::update('email_list', get_bloginfo('admin_email'));
        }

        return Option::get('email_list');
    }

    public static function getAddonOptions($addon_name = '')
    {
        $setting_name = "wpstatistics_{$addon_name}_settings";

        $options = get_option($setting_name);
        if (!isset($options) || !is_array($options)) {
            return false;
        }

        return $options;
    }

    public static function getByAddon($option_name, $addon_name = '', $default = null)
    {
        $setting_name = "wpstatistics_{$addon_name}_settings";

        $options = get_option($setting_name);
        if (!isset($options) || !is_array($options)) {
            $options = array();
        }

        if (!array_key_exists($option_name, $options)) {
            return !is_null($default) ? $default : false;
        }

        return apply_filters("wp_statistics_option_{$setting_name}_{$option_name}", $options[$option_name]);
    }

    public static function saveByAddon($options, $addon_name = '')
    {
        $setting_name = "wpstatistics_{$addon_name}_settings";
        update_option($setting_name, $options);
    }

    public static function getOptionGroup($group, $key = null, $default = null)
    {
        $settingName = "wp_statistics_{$group}";
        $options     = get_option($settingName);

        if (!isset($options) || !is_array($options)) {
            $options = array();
        }

        if (is_null($key)) {
            $result = $options;
        } else {
            if (!array_key_exists($key, $options)) {
                $result = !is_null($default) ? $default : false;
            } else {
                $result = $options[$key];
            }
        }

        return apply_filters("wp_statistics_option_{$settingName}", $result);
    }

    public static function saveOptionGroup($key, $value, $group)
    {
        $settingName = "wp_statistics_{$group}";
        $options     = get_option($settingName, []);

        // Store the value in the array.
        $options[$key] = $value;

        // Write the array to the database.
        update_option($settingName, $options);
    }

    public static function addOptionGroup($key, $value, $group)
    {
        $settingName = "wp_statistics_{$group}";
        $options     = get_option($settingName, []);

        // Store the value in the array.
        $options[$key] = $value;

        // Write the array to the database.
        add_option($settingName, $options);
    }

    public static function deleteOptionGroup($key, $group)
    {
        $settingName = "wp_statistics_{$group}";
        $options     = get_option($settingName, []);

        // Check if the key exists in the array.
        if (array_key_exists($key, $options)) {
            // Remove the key from the array.
            unset($options[$key]);

            // Write the updated array back to the database.
            update_option($settingName, $options);
        }
    }
}
