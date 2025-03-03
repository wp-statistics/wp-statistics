<?php

namespace WP_STATISTICS;

use WP_Statistics\Components\DateRange;

class User
{
    /**
     * Default Manage User Capability
     *
     * @var string
     */
    public static $default_manage_cap = 'manage_options';

    public static $dateFilterMetaKey = 'wp_statistics_metabox_date_filter';

    /**
     * Check User is Logged in WordPress
     *
     * @return mixed
     */
    public static function is_login()
    {
        return is_user_logged_in();
    }

    /**
     * Get Current User ID
     *
     * @return int
     */
    public static function get_user_id()
    {
        $user_id = 0;
        if (self::is_login() === true) {
            $user_id = get_current_user_id();
        }

        return apply_filters('wp_statistics_user_id', $user_id);
    }

    /**
     * Get User Data
     *
     * @param bool $user_id
     * @return array
     */
    public static function get($user_id = false)
    {

        # Get User ID
        $user_id = $user_id ? $user_id : get_current_user_id();

        # Get User Data
        $user_data = get_userdata($user_id);
        $user_info = get_object_vars($user_data->data);

        # Get User roles
        $user_info['role'] = $user_data->roles;

        # Get User Caps
        $user_info['cap'] = $user_data->caps;

        # Get User Meta
        $user_info['meta'] = array_map(function ($a) {
            return $a[0];
        }, get_user_meta($user_id));

        return $user_info;
    }


    public static function getMeta($metaKey, $single = false, $userId = false)
    {
        $userId = !empty($userId) ? $userId : get_current_user_id();
        return get_user_meta($userId, $metaKey, $single);
    }

    public static function saveMeta($metaKey, $metaValue, $userId = false)
    {
        $userId = !empty($userId) ? $userId : get_current_user_id();
        return update_user_meta($userId, $metaKey, $metaValue);
    }

    /**
     * Get Full name of User
     *
     * @param $user_id
     * @return string
     */
    public static function get_name($user_id)
    {

        # Get User Info
        $user_info = self::get($user_id);

        # check display name
        if ($user_info['display_name'] != "") {
            return $user_info['display_name'];
        }

        # Check First and Last name
        if ($user_info['meta']['first_name'] != "") {
            return $user_info['meta']['first_name'] . " " . $user_info['meta']['last_name'];
        }

        # return Username
        return $user_info['user_login'];
    }

    /**
     * Check User Exist By id
     *
     * @param $user_id
     * @return bool
     * We Don`t Use get_userdata or get_user_by function, because We need only count nor UserData object.
     */
    public static function exists($user_id)
    {
        global $wpdb;

        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->users WHERE `ID` = %d", $user_id));
        return $count > 0;
    }

    /**
     * Returns WordPress' roles names + an extra "Anonymous Users" index.
     *
     * @return  array
     */
    public static function get_role_list()
    {
        global $wp_roles;

        $rolesNames   = $wp_roles->get_names();
        $rolesNames[] = 'Anonymous Users';

        return $rolesNames;
    }

    /**
     * Validation User Capability
     *
     * @default manage_options
     * @param string $capability Capability
     * @return string 'manage_options'
     */
    public static function ExistCapability($capability)
    {
        global $wp_roles;

        if (!is_object($wp_roles) || !is_array($wp_roles->roles)) {
            return self::$default_manage_cap;
        }

        foreach ($wp_roles->roles as $role) {
            $cap_list = $role['capabilities'];

            foreach ($cap_list as $key => $cap) {
                if ($capability == $key) {
                    return $capability;
                }
            }
        }

        return self::$default_manage_cap;
    }

    /**
     * Check User Access To WP Statistics Admin
     *
     * @param string $type [manage | read ]
     * @param string|boolean $export
     * @return bool
     */
    public static function Access($type = 'both', $export = false)
    {

        //List Of Default Cap
        $list = array(
            'manage' => array('manage_capability', 'manage_options'),
            'read'   => array('read_capability', 'manage_options')
        );

        //User User Cap
        $cap = 'both';
        if (!empty($type) and array_key_exists($type, $list)) {
            $cap = $type;
        }

        //Check Export Cap name or Validation current_can_user
        if ($export == "cap") {
            return self::ExistCapability(Option::get($list[$cap][0], $list[$cap][1]));
        }

        //Check Access
        switch ($type) {
            case "manage":
            case "read":
                return current_user_can(self::ExistCapability(Option::get($list[$cap][0], $list[$cap][1])));
                break;
            case "both":
                foreach (array('manage', 'read') as $c) {
                    if (self::Access($c) === true) {
                        return true;
                    }
                }
                break;
        }

        return false;
    }

    /**
     * Get Date Filter
     *
     * @param $metaKey
     * @return mixed
     */
    public static function getDefaultDateFilter($metaKey)
    {
        $dateFilters = self::getMeta(self::$dateFilterMetaKey, true);

        // Return default date filter
        if (empty($dateFilters) || empty($dateFilters[$metaKey])) {
            $range = DateRange::get(DateRange::$defaultPeriod);

            return [
                'type'   => 'filter',
                'filter' => DateRange::$defaultPeriod,
                'from'   => $range['from'],
                'to'     => $range['to']
            ];
        }

        $dateFilter = $dateFilters[$metaKey];
        [$filterType, $dateFilter] = explode('|', $dateFilter);

        if ($filterType === 'custom') {
            [$from, $to] = explode(':', $dateFilter);
        } elseif ($filterType === 'filter') {
            $range = DateRange::get($dateFilter);
            $from  = $range['from'];
            $to    = $range['to'];
        }

        return [
            'type'   => $filterType,
            'filter' => $dateFilter,
            'from'   => $from,
            'to'     => $to
        ];
    }

    /**
     * Save Date Filter
     *
     * @param $metaKey
     * @param $value
     * @return void
     */
    public static function saveDefaultDateFilter($metaKey, $args)
    {
        // Return early if necessary fields are not set
        if (!isset($args['filter'], $args['from'], $args['to'])) {
            return;
        }

        // Get metaboxes date filters
        $dateFilters = self::getMeta(self::$dateFilterMetaKey, true);

        // Check if date filters is empty, use default array
        if (empty($dateFilters)) {
            $dateFilters = [];
        }

        // Get period from range
        $period = DateRange::get($args['filter']);

        // Store date in the database depending on wether the period exists or not
        if (!empty($period)) {
            $value = "filter|{$args['filter']}";
        } else {
            $value = "custom|{$args['from']}:{$args['to']}";
        }

        // Update meta value
        $dateFilters[$metaKey] = sanitize_text_field($value);
        self::saveMeta(self::$dateFilterMetaKey, $dateFilters);
    }

    /**
     * Retrieves the last login time of a WordPress user.
     *
     * @param int|false $userId The ID of the user to retrieve the last login time for. Defaults to the current user.
     * @return string|false The last login time of the user, or false if no login time is found.
     */
    public static function getLastLogin($userId = false)
    {
        $userId    = empty($userId) ? get_current_user_id() : $userId;
        $lastLogin = get_user_meta($userId, 'session_tokens', true);

        if (!empty($lastLogin)) {
            $lastLogin = array_values($lastLogin);
            return $lastLogin[0]['login'];
        } else {
            return false;
        }
    }

    /**
     * Check if the current user is an administrator or super admin in multisite network.
     *
     * @return bool Whether the current user is an administrator.
     */
    public static function isAdmin()
    {
        if (!is_user_logged_in()) {
            return false;
        }

        return is_multisite() ? is_super_admin() : current_user_can('manage_options');
    }

    /**
     * Check if the current user has the specified capability.
     *
     * @param string $capability The user capability to check.
     * @param int $postId The post ID
     * @return bool|null Whether the current user has the specified capability.
     */
    public static function checkUserCapability($capability, $postId = null)
    {
        if (!self::is_login() || empty($capability)) {
            return;
        }

        if (self::isCapabilityNeedingPostId($capability) && empty($postId)) {
            return;
        }

        if (is_multisite()) {
            if (!empty(get_current_blog_id()) && current_user_can_for_site(get_current_blog_id(), $capability)) {
                return true;
            }

            return;
        }

        if (!empty($postId) && current_user_can($capability, $postId)) {
            return true;
        }

        if (current_user_can($capability)) {
            return true;
        }

        return;
    }

    /**
     * Checks if a capability requires a post ID.
     *
     * @param string $capability
     *
     * @return bool
     */
    public static function isCapabilityNeedingPostId($capability)
    {
        if (strpos($capability, 'edit') !== false || strpos($capability, 'delete') !== false || strpos($capability, 'read') !== false) {
            $postType = str_replace(['edit_', 'delete_', 'read_'], '', $capability);

            if (substr($postType, -1) === 's') {
                return false;
            }

            return true;
        }

        return false;
    }
}